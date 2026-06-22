<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\User;
use App\Repositories\AuditoriaRepository;
use App\Repositories\CatalogoRepository;
use App\Repositories\ComunidadRepository;
use App\Repositories\GrupoProyectoRepository;
use App\Repositories\ProyectoRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProyectoGestionService
{
    protected static array $roleCache = [];

    protected static array $contextoEquipoCache = [];

    public function __construct(
        protected IntranetEquipoSeccionService $equipoSeccion,
        protected IntranetProfessorService $profesorIntranet,
        protected ProyectoRepository $proyectoRepo,
        protected CatalogoRepository $catalogoRepo,
        protected ComunidadRepository $comunidadRepo,
        protected AuditoriaRepository $auditoriaRepo,
        protected GrupoProyectoRepository $grupoRepo,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function datosVistaListado(array $filtros, int $page, ?User $user = null): array
    {
        $canValidate = $user ? $this->usuarioPuedeValidar($user) : false;

        // For profesor proyecto: auto-filter by their sections + creador
        if ($user) {
            $cedula = trim((string) $user->usu_cedula);
            $userRoleService = app(UserRoleService::class);
            $activeRole = $userRoleService->getActiveRole($user);
            if ($userRoleService->roleMatches('profesor proyecto', $activeRole)) {
                $filtros['creador_cedula'] = $cedula;
                $lapFiltro = !empty($filtros['lapso']) ? (int) $filtros['lapso'] : null;
                $clavesDocente = $this->profesorIntranet->clavesEquipoSeccionDocente($cedula, $lapFiltro);
                if ($clavesDocente !== null && $clavesDocente !== []) {
                    $pares = [];
                    foreach ($clavesDocente as $claveSec) {
                        $partes = $this->equipoSeccion->parsearClave($claveSec);
                        if ($partes) {
                            $pares[] = ['lap' => $partes['lap_codigo'], 'sec' => $partes['sec_codigo']];
                        }
                    }
                    $grupos = $this->grupoRepo->tablaDisponible()
                        ? $this->grupoRepo->listar()
                        : collect();
                    $grupoSvc = app(GrupoProyectoService::class);
                    $clavesFiltradas = $grupos->filter(fn ($g) => collect($pares)->contains(
                        fn ($p) => $g->lap_codigo === $p['lap'] && $g->sec_codigo === $p['sec']
                    ))->pluck('clave')->toArray();
                    if ($clavesFiltradas !== []) {
                        $filtros['equipo_ref'] = $clavesFiltradas;
                    }
                }
            }
        }

        return [
            'comunidades' => $this->comunidadRepo->allOrdered(),
            'proyectos' => $this->proyectoRepo->paginate($filtros, $page),
            'canRegister' => $user ? $this->usuarioPuedeRegistrar($user) : false,
            'canValidate' => $canValidate,
        ];
    }

    public function proyectoParaFicha(int $id): ?Proyecto
    {
        return $this->proyectoRepo->findWithRelations($id);
    }

    public function comunidadesOrdenadas(): \Illuminate\Support\Collection
    {
        return $this->comunidadRepo->allOrdered();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Proyecto>
     */
    public function proyectosLider(User $user): \Illuminate\Support\Collection
    {
        $ids = $this->proyectosDondeEsLider($user);
        if (empty($ids)) {
            return collect();
        }

        return $this->proyectoRepo->findWhereIn('pry_codigo', $ids);
    }

    public function aprobar(int $id, ?User $user = null): void
    {
        $user = $user ?? auth()->user();
        $proyecto = $this->proyectoRepo->findOrFail($id);
        $this->autorizarValidacionProyecto($user, $proyecto);

        $this->proyectoRepo->update($id, [
            'estado_validacion' => 'aprobado',
            'estado_logico' => true,
        ]);
        $this->registrarAuditoria($proyecto, 'aprobar');
    }

    public function rechazar(int $id, string $motivo, ?User $user = null): void
    {
        $user = $user ?? auth()->user();
        $proyecto = $this->proyectoRepo->findOrFail($id);
        $this->autorizarValidacionProyecto($user, $proyecto);

        $this->proyectoRepo->update($id, [
            'estado_validacion' => 'rechazado',
            'motivo_rechazo' => $motivo,
            'estado_logico' => false,
        ]);
        $this->registrarAuditoria($proyecto, 'rechazar');
    }

    /**
     * @return array<string, mixed>
     */
    public function datosVistaFormulario(array $estado): array
    {
        $user = auth()->user();
        $cedula = $user ? trim((string) $user->usu_cedula) : '';
        $esAdmin = $this->usuarioEsAdminEnSistema($user);

        $equipoCtx = $this->contextoEquipo($estado, $cedula, $esAdmin);

        $programaId = !empty($estado['programa_id'])
            ? (int) $estado['programa_id']
            : $this->resolverProgramaDesdeClave($estado['equipo_seccion_clave'] ?? '');

        $trayectoCodigo = $this->resolverTrayectoDesdeEstado($estado);

        $datos = array_merge($this->catalogoRepo->catalogos($programaId, $trayectoCodigo), $equipoCtx, [
            'canRegister' => $user ? $this->usuarioPuedeRegistrar($user) : false,
            'esAdmin' => $esAdmin,
            'comunidades' => $this->comunidadRepo->allOrdered(),
        ]);

        $datos['catalogosVacios'] = $this->catalogoRepo->catalogoVacios($datos);

        return $datos;
    }

    /**
     * @return array<string, mixed>
     */
    public function cargarParaEdicion(int $id): array
    {
        $item = $this->proyectoRepo->findWithDocuments($id);

        $partes = $this->equipoSeccion->parsearClave($item->equipo_ref);

        $programaDerived = null;
        $trayectoDerived = '';
        if ($partes) {
            try {
                $row = DB::connection($this->equipoSeccion->academicConnection())
                    ->table('seccion as sec')
                    ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                    ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                    ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                    ->where('sec.sec_codigo', $partes['sec_codigo'])
                    ->select(['pro.pro_codigo', 'tra.tra_nombre'])
                    ->first();
                if ($row) {
                    $programaDerived = $row->pro_codigo ?? null;
                    $trayectoDerived = trim($row->tra_nombre ?? '');
                }
            } catch (\Throwable) {
            }
        }

        $docsExistentes = [];
        foreach ($item->documentos as $doc) {
            $docsExistentes[$doc->comp_codigo] = [
                'id' => $doc->id,
                'path' => $doc->pd_archivo_path,
            ];
        }

        return [
            'editingId' => $id,
            'titulo' => $item->titulo,
            'resumen' => $item->resumen,
            'fecha_subida' => $item->fecha_subida?->format('Y-m-d') ?? '',
            'calificacion' => $item->calificacion !== null ? (string) $item->calificacion : '',
            'fecha_aprobacion' => $item->fecha_aprobacion?->format('Y-m-d') ?? '',
            'linea_investigacion_id' => (string) ($item->linea_investigacion_id ?? ''),
            'metodologia_id' => (string) ($item->metodologia_id ?? ''),
            'tipo_publicacion_id' => (string) ($item->tipo_publicacion_id ?? ''),
            'tipo_investigacion_id' => (string) ($item->tipo_investigacion_id ?? ''),
            'objetivo_investigacion_id' => (string) ($item->objetivo_investigacion_id ?? ''),
            'comunidad_id' => (string) $item->comunidad_id,
            'equipo_seccion_clave' => $item->equipo_ref ?? '',
            'filterLapsoEquipo' => $partes ? (string) $partes['lap_codigo'] : '',
            'filterProgramaEquipo' => $programaDerived !== null ? (string) $programaDerived : '',
            'filterSeccionEquipo' => $partes ? (string) $partes['sec_codigo'] : '',
            'programa_id_derived' => $programaDerived,
            'trayecto_derived' => $trayectoDerived,
            'archivos_actuales' => $docsExistentes,
        ];
    }

    public function guardar(
        ?int $editingId,
        array $datos,
        User $user,
        array $documentos = [],
        array $leaders = [],
    ): Proyecto {
        $esAdmin = $this->usuarioEsAdminEnSistema($user);
        $existing = $editingId ? $this->proyectoRepo->findWithDocuments($editingId) : null;

        $payload = [
            'resumen' => $datos['resumen'],
            'fecha_subida' => $datos['fecha_subida'],
            'calificacion' => ($datos['calificacion'] ?? '') !== '' ? (int) $datos['calificacion'] : null,
            'fecha_aprobacion' => ($datos['fecha_aprobacion'] ?? '') !== '' ? $datos['fecha_aprobacion'] : now()->format('Y-m-d'),
            'linea_investigacion_id' => $datos['linea_investigacion_id'] ?? null,
            'metodologia_id' => $datos['metodologia_id'] ?? null,
            'tipo_publicacion_id' => $datos['tipo_publicacion_id'] ?? null,
            'tipo_investigacion_id' => $datos['tipo_investigacion_id'] ?? null,
            'objetivo_investigacion_id' => $datos['objetivo_investigacion_id'] ?? null,
            'comunidad_id' => $datos['comunidad_id'],
            'equipo_ref' => $datos['equipo_seccion_clave'],
            'estado_validacion' => $editingId 
                ? ($existing->estado_validacion ?? 'pendiente') 
                : ($esAdmin ? 'aprobado' : 'pendiente'),
            'estado_logico' => $editingId
                ? (bool) ($existing->estado_logico ?? false)
                : ($esAdmin ? true : false),
        ];

        if (!$editingId) {
            $payload['creador_cedula'] = trim((string) $user->usu_cedula);
        }

        if ($editingId) {
            $this->proyectoRepo->update($editingId, $payload);
            $proyecto = $this->proyectoRepo->find($editingId);
        } else {
            $proyecto = $this->proyectoRepo->create($payload);
        }

        $this->guardarDocumentos($proyecto, $documentos, $existing);

        if (!empty($datos['equipo_seccion_clave']) && !$esAdmin && !empty($leaders)) {
            $this->asignarLideresGrupo($datos['equipo_seccion_clave'], $leaders);
        }

        $this->registrarAuditoria($proyecto, $editingId ? 'actualizar' : 'registrar');

        return $proyecto->fresh();
    }

    protected function asignarLideresGrupo(string $clave, array $leaders): void
    {
        if (!str_starts_with($clave, GrupoProyectoService::PREFIJO . ':')) {
            return;
        }
        try {
            app(GrupoProyectoService::class)->asignarLideres($clave, $leaders);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error asignando lideres de grupo: ' . $e->getMessage());
        }
    }

    protected function guardarDocumentos(Proyecto $proyecto, array $documentos, ?Proyecto $existing): void
    {
        $docsActuales = $existing?->documentos?->keyBy('comp_codigo') ?? collect();

        foreach ($documentos as $compCodigo => $file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
                continue;
            }

            $compCodigo = (int) $compCodigo;
            $path = $file->store('proyectos/' . $proyecto->id, 'public');

            if ($docsActuales->has($compCodigo)) {
                $docViejo = $docsActuales->get($compCodigo);
                $this->proyectoRepo->eliminarDocumentoViejo($docViejo->pd_archivo_path);
                $this->proyectoRepo->actualizarDocumento($docViejo->id, [
                    'pd_archivo_path' => $path,
                    'pd_orden' => 0,
                ]);
            } else {
                $this->proyectoRepo->crearDocumento($proyecto->id, $compCodigo, $path);
            }
        }
    }

    protected function registrarAuditoria(Proyecto $proyecto, string $accion): void
    {
        $audId = $this->auditoriaRepo->registrar(
            $proyecto->id,
            $accion,
            request()->ip(),
            (string) request()->userAgent(),
        );

        if ($audId !== null) {
            $this->auditoriaRepo->actualizarProyecto($proyecto->id, $audId);
        }
    }

    public function alternarEstado(int $id): void
    {
        $this->proyectoRepo->alternarEstado($id);
    }

    public function eliminar(int $id): void
    {
        $this->proyectoRepo->delete($id);
    }

    /**
     * @param  array{lapso?: int|null, programa?: int|null, trayecto?: string|null}  $filtros
     * @return Collection<int, object>
     */
    public function gruposDelDocente(User $user, array $filtros = []): Collection
    {
        $gruposSvc = app(GrupoProyectoService::class);
        if (!$gruposSvc->tablaDisponible()) {
            return collect();
        }

        $cedula = trim((string) $user->usu_cedula);
        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);

        $esProfesor = $userRoleService->roleMatches('profesor proyecto', $activeRole);

        if ($esProfesor) {
            $lapFiltro = !empty($filtros['lapso']) ? (int) $filtros['lapso'] : null;
            $clavesDocente = $this->profesorIntranet->clavesEquipoSeccionDocente($cedula, $lapFiltro);
            if ($clavesDocente === null || $clavesDocente === []) {
                return collect();
            }

            $grupos = $gruposSvc->listar();
            $gruposFiltrados = $grupos->filter(fn ($g) => collect($clavesDocente)->contains(
                fn ($clave) => str_contains($clave, ':' . $g->lap_codigo . ':' . $g->sec_codigo)
            ));
        } else {
            $lapso = !empty($filtros['lapso']) ? (int) $filtros['lapso'] : null;
            $programa = !empty($filtros['programa']) ? (int) $filtros['programa'] : null;
            $trayecto = !empty($filtros['trayecto']) ? $filtros['trayecto'] : null;

            $gruposFiltrados = $gruposSvc->listar([
                'lapso' => $lapso,
                'programa' => $programa,
                'trayecto' => $trayecto,
            ]);
        }

        $claves = $gruposFiltrados->pluck('clave')->toArray();
        $proyectoPorClave = $this->proyectoRepo->findByEquipos($claves)->keyBy('equipo_ref');

        return $gruposFiltrados->map(fn ($g) => (object) [
            'grp_codigo' => $g->grp_codigo,
            'nombre' => $g->nombre,
            'clave' => $g->clave,
            'lap_nombre' => $g->lap_nombre,
            'sec_nombre' => $g->sec_nombre,
            'pro_siglas' => $g->pro_siglas,
            'integrantes' => $g->integrantes ?? 0,
            'com_codigo' => $g->com_codigo,
            'tiene_proyecto' => $proyectoPorClave->has($g->clave),
            'proyecto_id' => $proyectoPorClave->get($g->clave)?->id,
            'proyecto_estado_validacion' => $proyectoPorClave->get($g->clave)?->estado_validacion,
            'proyecto_estado_logico' => $proyectoPorClave->get($g->clave)?->estado_logico,
        ])->values();
    }

    public function registrarProyectoDesdeGrupo(int $grpCodigo, User $user): ?Proyecto
    {
        $gruposSvc = app(GrupoProyectoService::class);
        $grupo = $gruposSvc->obtener($grpCodigo);
        if (!$grupo) {
            return null;
        }

        $clave = $gruposSvc->construirClave($grpCodigo);

        $existing = $this->proyectoRepo->findFirstByEquipoRef($clave);
        if ($existing) {
            return $existing;
        }

        $proyecto = $this->proyectoRepo->create([
            'resumen' => 'Proyecto del grupo ' . $grupo->nombre,
            'comunidad_id' => $grupo->com_codigo,
            'equipo_ref' => $clave,
            'estado_validacion' => 'pendiente',
            'estado_logico' => false,
            'creador_cedula' => trim((string) $user->usu_cedula),
            'fecha_subida' => now()->format('Y-m-d'),
        ]);

        $this->registrarAuditoria($proyecto, 'registrar');

        return $proyecto->fresh();
    }

    public function estudianteLiderVigente(User $user, Proyecto $proyecto): bool
    {
        $cedula = trim((string) $user->usu_cedula);
        if ($cedula === '') {
            return false;
        }

        $clave = $proyecto->equipo_ref ?? '';
        if ($clave === '') {
            return false;
        }

        $gruposSvc = app(GrupoProyectoService::class);
        if (!$gruposSvc->estudianteEnGrupo($cedula, $clave, IntranetEquipoSeccionService::ROL_LIDER)) {
            return false;
        }

        $partes = $gruposSvc->parsearClave($clave);
        if (!$partes || ($partes['tipo'] ?? '') !== GrupoProyectoService::PREFIJO) {
            $partesEq = $this->equipoSeccion->parsearClave($clave);
            if (!$partesEq) {
                return false;
            }
            return $this->equipoSeccion->estudiantePerteneceEquipo($cedula, $clave);
        }

        $grupo = $this->grupoRepo->find($partes['grp_codigo'] ?? 0);
        if (!$grupo) {
            return false;
        }

        $ctx = $grupo->grp_contexto;
        if (!$ctx instanceof \ArrayObject) {
            return false;
        }

        $lapCodigo = (int) ($ctx['lap_codigo'] ?? 0);
        $secCodigo = (int) ($ctx['sec_codigo'] ?? 0);
        if ($lapCodigo <= 0 || $secCodigo <= 0) {
            return false;
        }

        $claveSec = $this->equipoSeccion->construirClave($lapCodigo, $secCodigo);
        return $this->equipoSeccion->estudiantePerteneceEquipo($cedula, $claveSec);
    }

    public function reglasValidacion(array $estado, User $user, bool $esEdicion = false): array
    {
        $rules = [
            'titulo' => 'required|min:5|max:255',
            'resumen' => 'required|min:10',
            'fecha_subida' => 'required|date',

            'linea_investigacion_id' => ['nullable', Rule::exists('\App\Models\LineaInvestigacion', (new \App\Models\LineaInvestigacion())->getKeyName())],
            'metodologia_id' => ['nullable', Rule::exists('\App\Models\MetodologiaInvestigacion', (new \App\Models\MetodologiaInvestigacion())->getKeyName())],
            'tipo_publicacion_id' => ['nullable', Rule::exists('\App\Models\TipoPublicacion', (new \App\Models\TipoPublicacion())->getKeyName())],
            'tipo_investigacion_id' => ['nullable', Rule::exists('\App\Models\TipoInvestigacion', (new \App\Models\TipoInvestigacion())->getKeyName())],
            'objetivo_investigacion_id' => ['nullable', Rule::exists('\App\Models\ObjetivoInvestigacion', (new \App\Models\ObjetivoInvestigacion())->getKeyName())],
            'comunidad_id' => ['required', Rule::exists('\App\Models\Comunidad', (new \App\Models\Comunidad())->getKeyName())],
            'equipo_seccion_clave' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (! $this->equipoSeccion->parsearClave($value)) {
                        $fail('Debe seleccionar el equipo (sección y lapso en intranet).');

                        return;
                    }
                    if (! $this->usuarioEsAdminEnSistema($user)) {
                        $cedula = trim((string) $user->usu_cedula);
                        if (! $this->equipoSeccion->estudiantePerteneceEquipo($cedula, $value)) {
                            $fail('No pertenece al equipo/grupo seleccionado.');
                        }
                    }
                },
            ],
        ];

        if ($esEdicion) {
            $rules['calificacion'] = 'nullable|integer|min:1|max:20';
            $rules['fecha_aprobacion'] = 'nullable|date';
        } else {
            $rules['calificacion'] = 'nullable|integer|min:1|max:20';
            $rules['fecha_aprobacion'] = 'nullable|date';
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $estado
     * @return array<string, mixed>
     */
    protected function contextoEquipo(array $estado, string $cedula, bool $esAdmin): array
    {
        $cacheKey = 'ctx_' . md5(serialize([$estado['filterLapsoEquipo'] ?? '', $estado['equipo_seccion_clave'] ?? '', $cedula]));
        if (array_key_exists($cacheKey, static::$contextoEquipoCache)) {
            return static::$contextoEquipoCache[$cacheKey];
        }

        $gruposSvc = app(GrupoProyectoService::class);
        $lapFiltro = ($estado['filterLapsoEquipo'] ?? '') !== ''
            ? (int) $estado['filterLapsoEquipo']
            : null;

        $equiposDisp = $this->grupoRepo->tablaDisponible()
            ? $this->grupoRepo->listar(['lapso' => $lapFiltro])
            : collect();

        if (!$esAdmin && $cedula !== '') {
            $userRoleService = app(UserRoleService::class);
            $user = auth()->user();
            $activeRole = $userRoleService->getActiveRole($user);
            if ($userRoleService->roleMatches('profesor proyecto', $activeRole)) {
                $clavesDocente = $this->profesorIntranet->clavesEquipoSeccionDocente($cedula, $lapFiltro);
                if ($clavesDocente !== null && $clavesDocente !== []) {
                    $pares = [];
                    foreach ($clavesDocente as $claveSec) {
                        $partes = $this->equipoSeccion->parsearClave($claveSec);
                        if ($partes) {
                            $pares[] = ['lap' => $partes['lap_codigo'], 'sec' => $partes['sec_codigo']];
                        }
                    }
                    $equiposDisp = $equiposDisp->filter(fn ($g) => collect($pares)->contains(
                        fn ($p) => $g->lap_codigo === $p['lap'] && $g->sec_codigo === $p['sec']
                    ));
                } else {
                    $equiposDisp = collect();
                }
            }
        }

        $clave = $estado['equipo_seccion_clave'] ?? '';
        $equipoValidado = null;
        $integrantes = collect();

        if ($clave !== '') {
            $equipoValidado = $equiposDisp->firstWhere('clave', $clave);
            if (! $equipoValidado && $cedula !== '') {
                $equipoValidado = $this->equipoSeccion->equiposDelEstudiante($cedula, $lapFiltro)
                    ->firstWhere('clave', $clave);
            }
            if (! $equipoValidado && $gruposSvc->tablaDisponible()) {
                $equipoValidado = $gruposSvc->obtenerPorClave($clave);
            }
            $integrantes = $this->equipoSeccion->integrantes($clave);
        }

        return static::$contextoEquipoCache[$cacheKey] = [
            'equipos_disp' => $equiposDisp,
            'equipoValidado' => $equipoValidado,
            'integrantesEquipo' => $integrantes,
        ];
    }

    protected function resolverProgramaDesdeClave(string $clave): ?int
    {
        if ($clave === '') {
            return null;
        }

        $partes = $this->equipoSeccion->parsearClave($clave);

        if (!$partes || empty($partes['sec_codigo'])) {
            return null;
        }

        try {
            $row = DB::connection($this->equipoSeccion->academicConnection())
                ->table('seccion as sec')
                ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                ->where('sec.sec_codigo', $partes['sec_codigo'])
                ->select('pro.pro_codigo')
                ->first();

            return $row ? (int) $row->pro_codigo : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolverTrayectoDesdeEstado(array $estado): ?string
    {
        $trayectoNombre = $estado['trayecto'] ?? '';
        if ($trayectoNombre === '' || $trayectoNombre === null) {
            return null;
        }

        $clave = $estado['equipo_seccion_clave'] ?? '';
        $partes = $clave !== '' ? $this->equipoSeccion->parsearClave($clave) : null;
        if (!$partes || empty($partes['sec_codigo'])) {
            return null;
        }

        try {
            $row = DB::connection($this->equipoSeccion->academicConnection())
                ->table('seccion as sec')
                ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
                ->where('sec.sec_codigo', $partes['sec_codigo'])
                ->where('tra.tra_nombre', $trayectoNombre)
                ->select('tra.tra_codigo')
                ->first();

            return $row ? (string) $row->tra_codigo : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function usuarioEsAdminEnSistema(?User $user): bool
    {
        $key = 'admin_' . ($user?->getKey() ?? 'null');
        if (array_key_exists($key, static::$roleCache)) {
            return static::$roleCache[$key];
        }

        if ($user === null) {
            return static::$roleCache[$key] = false;
        }

        $roleService = app(UserRoleService::class);
        $activeRole = $roleService->getActiveRole($user);

        if ($activeRole !== null) {
            return static::$roleCache[$key] = $roleService->roleMatches('administrador', $activeRole);
        }

        return static::$roleCache[$key] = in_array(
            'administrador',
            array_keys($roleService->detectAvailableRoles($user)),
            true
        );
    }

    public function usuarioPuedeRegistrar(?User $user): bool
    {
        $key = 'registrar_' . ($user?->getKey() ?? 'null');
        if (array_key_exists($key, static::$roleCache)) {
            return static::$roleCache[$key];
        }

        if ($user === null) {
            return static::$roleCache[$key] = false;
        }

        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);

        if ($activeRole !== null) {
            if ($userRoleService->roleMatches('administrador', $activeRole) ||
                $userRoleService->roleMatches('coordinador', $activeRole) ||
                $userRoleService->roleMatches('profesor proyecto', $activeRole) ||
                $userRoleService->roleMatches('gestionador', $activeRole)) {
                return static::$roleCache[$key] = true;
            }
            if ($userRoleService->roleMatches('estudiante', $activeRole)) {
                return static::$roleCache[$key] = $this->equipoSeccion->estudiantePuedeRegistrar(trim((string) $user->usu_cedula));
            }
            return static::$roleCache[$key] = false;
        }

        $availableDetectedRoles = array_keys($userRoleService->detectAvailableRoles($user));

        if (in_array('administrador', $availableDetectedRoles, true) ||
            in_array('coordinador', $availableDetectedRoles, true) ||
            in_array('profesor proyecto', $availableDetectedRoles, true) ||
            in_array('gestionador', $availableDetectedRoles, true)) {
            return static::$roleCache[$key] = true;
        }

        if (in_array('estudiante', $availableDetectedRoles, true)) {
            return static::$roleCache[$key] = $this->equipoSeccion->estudiantePuedeRegistrar(trim((string) $user->usu_cedula));
        }

        return static::$roleCache[$key] = false;
    }

    public function usuarioEsLiderDelProyecto(?User $user, Proyecto $proyecto): bool
    {
        if ($user === null) {
            return false;
        }
        $cedula = trim((string) $user->usu_cedula);
        $clave = $proyecto->equipo_ref ?? '';

        if ($clave === '') {
            return false;
        }

        $gruposSvc = app(GrupoProyectoService::class);
        $partes = $gruposSvc->parsearClave($clave);
        if (!$partes || ($partes['tipo'] ?? '') !== GrupoProyectoService::PREFIJO) {
            return false;
        }

        $grupo = $this->grupoRepo->find($partes['grp_codigo'] ?? 0);
        if (!$grupo) {
            return false;
        }

        $miembros = $grupo->grp_miembros ?? [];
        foreach ($miembros as $m) {
            $mCedula = trim((string) ($m['cedula'] ?? ''));
            $rolId = (int) ($m['rol_id'] ?? 0);
            if ($mCedula === $cedula && $rolId === IntranetEquipoSeccionService::ROL_LIDER) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int> IDs de proyectos donde el usuario es líder
     */
    public function proyectosDondeEsLider(User $user): array
    {
        $cedula = trim((string) $user->usu_cedula);
        if ($cedula === '') {
            return [];
        }

        try {
            $grupos = $this->grupoRepo->findLiderByCedula($cedula);

            $claves = $grupos->map(fn ($g) => 'EQGRP:' . $g->grp_codigo)->toArray();
            if (empty($claves)) {
                return [];
            }

            return $this->proyectoRepo->findLiderIds($claves)
                ->map(fn ($v) => (int) $v)
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    public function usuarioPuedeValidar(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->usuarioEsAdminEnSistema($user)
            || $user->hasRole('coordinador', 'profesor proyecto');
    }

    public function usuarioPuedeValidarProyecto(?User $user, Proyecto $proyecto): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->usuarioEsAdminEnSistema($user)) {
            return true;
        }

        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);

        if ($userRoleService->roleMatches('coordinador', $activeRole)) {
            return true;
        }

        if ($userRoleService->roleMatches('profesor proyecto', $activeRole)) {
            $clavesDocente = $this->clavesEquipoFiltroValidacion($user);
            if ($clavesDocente !== null) {
                $claveSeccion = $this->resolverClaveSeccionDesdeProyecto($proyecto);
                return $claveSeccion !== null && in_array($claveSeccion, $clavesDocente, true);
            }
        }

        return false;
    }

    protected function resolverClaveSeccionDesdeProyecto(Proyecto $proyecto): ?string
    {
        $clave = $proyecto->equipo_ref;
        if (!$clave) return null;

        $gruposSvc = app(GrupoProyectoService::class);
        $partes = $gruposSvc->parsearClave($clave);
        if (($partes['tipo'] ?? '') !== GrupoProyectoService::PREFIJO || empty($partes['grp_codigo'])) {
            return null;
        }

        $grupo = $this->grupoRepo->find($partes['grp_codigo']);
        if (!$grupo) return null;

        $contexto = $grupo->grp_contexto;
        if (!$contexto instanceof \ArrayObject) return null;

        $lap = $contexto['lap_codigo'] ?? null;
        $sec = $contexto['sec_codigo'] ?? null;
        if (!$lap || !$sec) return null;

        return $this->equipoSeccion->construirClave((int) $lap, (int) $sec);
    }

    protected function autorizarValidacionProyecto(?User $user, Proyecto $proyecto): void
    {
        if (! $this->usuarioPuedeValidarProyecto($user, $proyecto)) {
            throw new AuthorizationException(
                'No puede validar este expediente: debe ser docente de la UC Proyecto en la misma sección y lapso del equipo (intranet).'
            );
        }
    }

    /**
     * null = sin filtro (admin/coordinador); [] = docente sin secciones asignadas.
     *
     * @return list<string>|null
     */
    public function clavesEquipoFiltroValidacion(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        if ($this->usuarioEsAdminEnSistema($user)) {
            return null;
        }

        $disponibles = array_keys(app(UserRoleService::class)->detectAvailableRoles($user));
        if (in_array('coordinador', $disponibles, true) && $user->hasRole('coordinador')) {
            return null;
        }

        if (! $user->hasRole('profesor proyecto')) {
            return null;
        }

        return $this->profesorIntranet->clavesEquipoSeccionDocente(trim((string) $user->usu_cedula));
    }

    /**
     * @param  array<string, mixed>  $estado
     */
    protected function puedeRegistrar(User $user, array $estado): bool
    {
        return $this->usuarioPuedeRegistrar($user);
    }
}
