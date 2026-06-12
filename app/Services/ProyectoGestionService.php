<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\Proyecto;
use App\Models\TipoInvestigacion;
use App\Models\TipoPublicacion;
use App\Models\User;
use App\Models\Componente;
use App\Models\LapsoAcademico;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorInstance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProyectoGestionService
{
    protected static array $roleCache = [];

    protected static array $contextoEquipoCache = [];

    public function __construct(
        protected IntranetEquipoSeccionService $equipoSeccion,
        protected IntranetProfessorService $profesorIntranet,
    ) {}

    protected function conexionRepositorio(): string
    {
        return (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function usaComponentesDocumentales(): bool
    {
        return Cache::remember('pry_usa_componentes', 3600, function () {
            $schema = Schema::connection($this->conexionRepositorio());

            return $schema->hasTable('componentes') && $schema->hasColumn('proyectos', 'pry_documentos');
        });
    }

    /**
     * @return list<string>
     */
    protected function relacionesProyecto(): array
    {
        return ['tipo_publicacion', 'linea_investigacion', 'comunidad', 'metodologia', 'tipo_investigacion'];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosVistaListado(array $filtros, int $page, ?User $user = null, string $listTab = 'gestion'): array
    {
        $canValidate = $user ? $this->usuarioPuedeValidar($user) : false;

        return [
            'comunidades' => $this->comunidadesOrdenadas(),
            'proyectos' => $listTab === 'validar'
                ? $this->paginarPendientes($filtros['search'] ?? '', $page, $user)
                : $this->paginarProyectos($filtros, $page),
            'canRegister' => $user ? $this->usuarioPuedeRegistrar($user) : false,
            'canValidate' => $canValidate,
            'listTab' => $listTab,
        ];
    }

    public function paginarPendientes(string $search, int $page, ?User $user = null): LengthAwarePaginator
    {
        $clavesDocente = $this->clavesEquipoFiltroValidacion($user);
        if ($clavesDocente !== null && $clavesDocente === []) {
            return new PaginatorInstance([], 0, 10, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        $query = Proyecto::with($this->relacionesProyecto())
            ->where('estado_validacion', 'pendiente')
            ->where(function ($q) use ($search) {
                if ($search === '') {
                    return;
                }
                try {
                    $q->whereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$search]);
                } catch (\Throwable) {
                    $q->whereRaw('pry_resumen ILIKE ?', ['%' . $search . '%']);
                }
            });

        if ($clavesDocente !== null) {
            $query->whereIn('pry_direccion_logica', $clavesDocente);
        }

        return $query->latest()->paginate(10, page: $page);
    }

    public function proyectoParaFicha(int $id): ?Proyecto
    {
        return Proyecto::with($this->relacionesProyecto())->find($id);
    }

    public function aprobar(int $id, ?User $user = null): void
    {
        $user = $user ?? auth()->user();
        $proyecto = Proyecto::findOrFail($id);
        $this->autorizarValidacionProyecto($user, $proyecto);

        $proyecto->update([
            'estado_validacion' => 'aprobado',
            'estado_logico' => true,
        ]);
        $this->registrarAuditoria($proyecto, 'aprobar');
    }

    public function rechazar(int $id, string $motivo, ?User $user = null): void
    {
        $user = $user ?? auth()->user();
        $proyecto = Proyecto::findOrFail($id);
        $this->autorizarValidacionProyecto($user, $proyecto);

        $proyecto->update([
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
        $lapCodigoFiltro = ($estado['filterLapsoEquipo'] ?? '') !== ''
            ? (int) $estado['filterLapsoEquipo']
            : null;

        $datos = array_merge($this->catalogos(), $equipoCtx, [
            'canRegister' => $user ? $this->usuarioPuedeRegistrar($user) : false,
            'esAdmin' => $esAdmin,
            'usaComponentes' => $this->usaComponentesDocumentales(),
            'programasEquipo' => $lapCodigoFiltro
                ? $this->equipoSeccion->programasEnLapso($lapCodigoFiltro)
                : collect(),
            'seccionesEquipo' => $lapCodigoFiltro
                ? $this->equipoSeccion->seccionesEnLapso(
                    $lapCodigoFiltro,
                    ($estado['filterProgramaEquipo'] ?? '') !== '' ? (int) $estado['filterProgramaEquipo'] : null
                )
                : collect(),
            'comunidades' => $this->comunidadesOrdenadas(),
        ]);

        if ($this->usaComponentesDocumentales()) {
            $datos['componentes_requeridos'] = $this->componentesRequeridos(
                $estado['programa_id'] ?? null
            );
        } else {
            $datos['componentes_requeridos'] = collect();
        }

        $datos['catalogosVacios'] = $this->catalogosVacios($datos);

        return $datos;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return list<string>
     */
    public function catalogosVacios(array $datos): array
    {
        $faltantes = [];

        if (($datos['comunidades'] ?? collect())->isEmpty()) {
            $faltantes[] = 'comunidades';
        }
        if (($datos['lineas'] ?? collect())->isEmpty()) {
            $faltantes[] = 'líneas de investigación';
        }
        if (($datos['metodologias'] ?? collect())->isEmpty()) {
            $faltantes[] = 'metodologías';
        }
        if (($datos['tipos_publicacion'] ?? collect())->isEmpty()) {
            $faltantes[] = 'tipos de publicación';
        }
        if (($datos['tipos_investigacion'] ?? collect())->isEmpty()) {
            $faltantes[] = 'tipos de investigación';
        }

        return $faltantes;
    }

    /**
     * @return array<string, string|null>
     */
    public function sincronizarEquipoEstudiante(string $cedula, ?int $lapCodigo): array
    {
        $equipos = $this->equipoSeccion->equiposDelEstudiante($cedula, $lapCodigo);

        if ($equipos->count() !== 1) {
            return ['equipo_seccion_clave' => null];
        }

        return ['equipo_seccion_clave' => $equipos->first()->clave];
    }

    /**
     * @return array<string, mixed>
     */
    public function cargarParaEdicion(int $id): array
    {
        $item = Proyecto::findOrFail($id);

        $archivos = [];
        if ($this->usaComponentesDocumentales()) {
            foreach ($item->documentos ?? [] as $doc) {
                $archivos[$doc['componente_id']] = $doc['archivo_path'];
            }
        }

        $partes = $this->equipoSeccion->parsearClave($item->equipo_ref);

        $programaDerived = null;
        $trayectoDerived = '';
        if ($partes) {
            try {
                $row = \Illuminate\Support\Facades\DB::connection($this->equipoSeccion->academicConnection())
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

        return [
            'editingId' => $id,
            'titulo' => $item->titulo,
            'resumen' => $item->resumen,
            'fecha_subida' => $item->fecha_subida?->format('Y-m-d') ?? '',
            'asignacion_ct' => (bool) $item->asignacion_ct,
            'calificacion' => $item->calificacion !== null ? (string) $item->calificacion : '',
            'fecha_aprobacion' => $item->fecha_aprobacion?->format('Y-m-d') ?? '',
            'linea_investigacion_id' => (string) ($item->linea_investigacion_id ?? ''),
            'metodologia_id' => (string) ($item->metodologia_id ?? ''),
            'tipo_publicacion_id' => (string) ($item->tipo_publicacion_id ?? ''),
            'tipo_investigacion_id' => (string) ($item->tipo_investigacion_id ?? ''),
            'comunidad_id' => (string) $item->comunidad_id,
            'equipo_seccion_clave' => $item->equipo_ref ?? '',
            'filterLapsoEquipo' => $partes ? (string) $partes['lap_codigo'] : '',
            'programa_id_derived' => $programaDerived,
            'trayecto_derived' => $trayectoDerived,
            'archivos_actuales' => $archivos,
            'archivo_actual' => $item->archivo_path ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $archivosComponentes
     * @param  array<int, string>  $archivosActuales
     */
    public function guardar(
        ?int $editingId,
        array $datos,
        array $archivosComponentes,
        array $archivosActuales,
        User $user,
        mixed $archivoProyecto = null,
    ): Proyecto {
        $payload = [
            'titulo' => $datos['titulo'],
            'resumen' => $datos['resumen'],
            'fecha_subida' => $datos['fecha_subida'],
            'asignacion_ct' => (bool) ($datos['asignacion_ct'] ?? false),
            'calificacion' => ($datos['calificacion'] ?? '') !== '' ? (int) $datos['calificacion'] : null,
            'fecha_aprobacion' => ($datos['fecha_aprobacion'] ?? '') !== '' ? $datos['fecha_aprobacion'] : now()->format('Y-m-d'),
            'linea_investigacion_id' => $datos['linea_investigacion_id'] ?? null,
            'metodologia_id' => $datos['metodologia_id'] ?? null,
            'tipo_publicacion_id' => $datos['tipo_publicacion_id'] ?? null,
            'tipo_investigacion_id' => $datos['tipo_investigacion_id'] ?? null,
            'comunidad_id' => $datos['comunidad_id'],
            'equipo_ref' => $datos['equipo_seccion_clave'],
            'estado_validacion' => 'aprobado',
            'estado_logico' => true,
        ];

        if ($editingId) {
            $proyecto = Proyecto::findOrFail($editingId);
            if (! $archivoProyecto && ! empty($datos['archivo_actual'])) {
                $payload['archivo_path'] = $datos['archivo_actual'];
            }
            $proyecto->update($payload);
        } else {
            $payload['archivo_path'] = null;
            $proyecto = Proyecto::create($payload);
        }

        if ($archivoProyecto) {
            $path = $archivoProyecto->store('proyectos', 'public');
            $proyecto->update(['archivo_path' => $path]);
        }

        if ($this->usaComponentesDocumentales()) {
            $componentes = $this->componentesRequeridos($datos['programa_id'] ?? null);
            $documentos = [];

            if ($editingId && ! empty($archivosActuales)) {
                foreach ($archivosActuales as $componenteId => $archivoPath) {
                    $documentos[$componenteId] = [
                        'componente_id' => $componenteId,
                        'archivo_path' => $archivoPath,
                    ];
                }
            }

            foreach ($componentes as $c) {
                if (isset($archivosComponentes[$c->id])) {
                    $path = $archivosComponentes[$c->id]->store('proyectos/componentes', 'public');
                    $documentos[$c->id] = [
                        'componente_id' => $c->id,
                        'componente_nombre' => $c->nombre,
                        'archivo_path' => $path,
                    ];
                } elseif (! isset($documentos[$c->id]) && isset($archivosActuales[$c->id])) {
                    $documentos[$c->id] = [
                        'componente_id' => $c->id,
                        'archivo_path' => $archivosActuales[$c->id],
                    ];
                }
            }

            if (! empty($documentos)) {
                $proyecto->update(['documentos' => array_values($documentos)]);
            }
        }

        $this->registrarAuditoria($proyecto, $editingId ? 'actualizar' : 'registrar');

        return $proyecto->fresh();
    }

    protected function registrarAuditoria(Proyecto $proyecto, string $accion): void
    {
        if (! Schema::connection($this->conexionRepositorio())->hasTable('auditorias')) {
            return;
        }

        try {
            $audId = DB::connection($this->conexionRepositorio())->table('auditorias')->insertGetId([
                'pry_codigo' => $proyecto->id,
                'aud_accion' => $accion,
                'aud_modulo' => 'proyectos',
                'ip' => request()->ip(),
                'aud_user_agent' => substr((string) request()->userAgent(), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::connection($this->conexionRepositorio())
                ->table('proyectos')
                ->where('pry_codigo', $proyecto->id)
                ->update(['aud_codigo' => $audId]);
        } catch (\Throwable) {
            // No bloquear el registro si falla la auditoría
        }
    }

    public function alternarEstado(int $id): void
    {
        $item = Proyecto::findOrFail($id);
        $item->update(['estado_logico' => ! $item->estado_logico]);
    }

    public function eliminar(int $id): void
    {
        Proyecto::findOrFail($id)->delete();
    }

    /**
     * @param  array<string, mixed>  $estado
     */
    public function reglasValidacion(array $estado, array $archivosActuales, User $user, bool $esEdicion = false): array
    {
        $rules = [
            'titulo' => 'required|min:5|max:255',
            'resumen' => 'required|min:10',
            'fecha_subida' => 'required|date',
            'asignacion_ct' => 'boolean',
            'linea_investigacion_id' => ['nullable', Rule::exists(LineaInvestigacion::class, (new LineaInvestigacion())->getKeyName())],
            'metodologia_id' => ['nullable', Rule::exists(MetodologiaInvestigacion::class, (new MetodologiaInvestigacion())->getKeyName())],
            'tipo_publicacion_id' => ['nullable', Rule::exists(TipoPublicacion::class, (new TipoPublicacion())->getKeyName())],
            'tipo_investigacion_id' => ['nullable', Rule::exists(TipoInvestigacion::class, (new TipoInvestigacion())->getKeyName())],
            'comunidad_id' => ['required', Rule::exists(Comunidad::class, (new Comunidad())->getKeyName())],
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
            'archivo_proyecto' => 'nullable|file|max:20480|mimes:pdf',
        ];

        if ($esEdicion) {
            $rules['calificacion'] = 'nullable|integer|min:1|max:20';
            $rules['fecha_aprobacion'] = 'nullable|date';
        } else {
            $rules['calificacion'] = 'nullable|integer|min:1|max:20';
            $rules['fecha_aprobacion'] = 'nullable|date';
        }

        if ($this->usaComponentesDocumentales()) {
            $componentes = $this->componentesRequeridos($estado['programa_id'] ?? null);
            foreach ($componentes as $c) {
                if ($c->es_obligatorio && ! isset($archivosActuales[$c->id])) {
                    $rules['archivos_componentes.'.$c->id] = 'required|file|max:20480';
                } else {
                    $rules['archivos_componentes.'.$c->id] = 'nullable|file|max:20480';
                }
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function paginarProyectos(array $filtros, int $page): LengthAwarePaginator
    {
        return Proyecto::with($this->relacionesProyecto())
            ->when(($filtros['search'] ?? '') !== '', function ($q) use ($filtros) {
                $s = $filtros['search'];
                try {
                    $q->whereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$s]);
                } catch (\Throwable) {
                    $q->whereRaw('pry_resumen ILIKE ?', ['%' . $s . '%']);
                }
            })
            ->when(($filtros['estado'] ?? '') !== '', fn ($q) => $q->where('estado_validacion', $filtros['estado']))
            ->when(($filtros['comunidad'] ?? '') !== '', fn ($q) => $q->where('comunidad_id', $filtros['comunidad']))
            ->latest()
            ->paginate(10, page: $page);
    }

    public function componentesRequeridos(mixed $programaId): Collection
    {
        if (! $this->usaComponentesDocumentales() || ! $programaId) {
            return collect();
        }

        $cacheKey = 'componentes_req_' . $programaId;

        return Cache::remember($cacheKey, 3600, fn() =>
            Componente::where('programa_id', $programaId)
                ->where('estado_logico', true)
                ->get()
        );
    }

    public function comunidadesOrdenadas(): Collection
    {
        return Cache::remember('gestion_comunidades_ordenadas', now()->addMinutes(10), fn() =>
            Comunidad::orderBy('nombre')->get()
        );
    }

    /**
     * @return array<string, Collection>
     */
    protected function catalogos(): array
    {
        $ttl = now()->addMinutes(10);
        return [
            'lineas' => Cache::remember('gestion_cat_lineas', $ttl, fn() => app(ModuloRepositorioService::class)->lineasInvestigacionActivas()),
            'metodologias' => Cache::remember('gestion_cat_metodologias', $ttl, fn() => MetodologiaInvestigacion::where('estado_logico', true)->get()),
            'tipos_publicacion' => Cache::remember('gestion_cat_tipos_publicacion', $ttl, fn() => TipoPublicacion::where('estado_logico', true)->get()),
            'tipos_investigacion' => Cache::remember('gestion_cat_tipos_investigacion', $ttl, fn() => TipoInvestigacion::where('estado_logico', true)->get()),
            'lapsos' => Cache::remember('gestion_cat_lapsos', $ttl, fn() => LapsoAcademico::activos()->orderByDesc('lap_codigo')->get()),
        ];
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

        $equiposDisp = $gruposSvc->tablaDisponible()
            ? $gruposSvc->listar(['lapso' => $lapFiltro])
            : collect();

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
            if ($userRoleService->roleMatches('administrador', $activeRole)) {
                return static::$roleCache[$key] = true;
            }
            if ($userRoleService->roleMatches('estudiante', $activeRole)) {
                if ($userRoleService->allowsFreeSessionRoles()) {
                    return static::$roleCache[$key] = true;
                }
                return static::$roleCache[$key] = $this->equipoSeccion->estudiantePuedeRegistrar(trim((string) $user->usu_cedula));
            }
            return static::$roleCache[$key] = false;
        }

        $availableDetectedRoles = array_keys($userRoleService->detectAvailableRoles($user));

        if (in_array('administrador', $availableDetectedRoles, true)) {
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

        $partes = app(GrupoProyectoService::class)->parsearClave($clave);
        if (!$partes || ($partes['tipo'] ?? '') !== GrupoProyectoService::PREFIJO) {
            return false;
        }

        $grupo = \App\Models\GrupoProyectoModulo::find($partes['grp_codigo'] ?? 0);
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

    public function usuarioPuedeValidar(?User $user): bool
    {
        return false;
    }

    public function usuarioPuedeValidarProyecto(?User $user, Proyecto $proyecto): bool
    {
        return false;
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
    protected function clavesEquipoFiltroValidacion(?User $user): ?array
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
