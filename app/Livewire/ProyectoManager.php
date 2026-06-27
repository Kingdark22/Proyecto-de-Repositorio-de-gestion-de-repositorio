<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Models\LineaInvestigacion;
use App\Models\Involucrado;
use App\Models\RolInvolucrado;
use App\Services\GrupoProyectoService;
use App\Services\UnicidadNombreService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\ProyectoGestionService;
use App\Services\UserRoleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use App\Livewire\Concerns\WithSafeNotify;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
class ProyectoManager extends Component
{
    use WithFileUploads;
    use WithPagination;
    use WithSafeNotify;

    public ?string $titulo = '';

    public ?string $resumen = '';

    public ?string $linea_investigacion_id = '';

    public ?string $metodologia_id = '';

    public ?string $tipo_publicacion_id = '';
    public ?string $tipo_investigacion_id = '';
    public ?string $objetivo_investigacion_id = '';
    public ?string $comunidad_id = '';


    public ?string $equipo_seccion_clave = '';

    public ?string $filterLapsoEquipo = '';

    public ?string $filterProgramaEquipo = '';

    public ?string $filterSeccionEquipo = '';

    public ?string $filterEstadoList = '';
    public ?string $filterComunidadList = '';
    public ?string $filterGruposLapso = '';
    public ?string $filterGruposPrograma = '';
    public ?string $filterGruposTrayecto = '';

    public $archivosComponente = [];

    public array $archivos_actuales = [];

    public bool $showTeamFilters = false;

    public ?string $programa_id_derived = null;

    public ?string $trayecto_derived = '';

    public ?string $search = '';

    public ?string $motivo_rechazo = '';

    public ?int $editingId = null;

    public ?int $selectedProjectId = null;

    public ?Proyecto $selectedProject = null;

    public string $viewMode = 'list';

    /** True cuando el equipo seleccionado es un grupo de proyecto registrado (EQGRP:) */
    public bool $esGrupoRegistrado = false;

    /** Nombre de la comunidad vinculada al grupo (solo lectura) */
    public ?string $comunidadNombreGrupo = null;

    /** True si el usuario actual es lider del proyecto que esta editando */
    public bool $esLider = false;

    /** True cuando un lider esta actualizando documentos (modo solo subida) */
    public bool $modoActualizacion = false;

    /** Cédulas de los líderes seleccionados (max 2) */
    public array $selectedLeaders = [];

    /** Miembros del grupo seleccionado (para mostrar checkboxes) */
    public array $miembrosGrupo = [];

    /** Grupos del docente para registro por selección */
    public array $gruposDocente = [];

    /** True si el rol activo es profesor proyecto */
    public bool $esProfesor = false;

    /** True si el rol activo es gestionador */
    public bool $esGestionador = false;

    /** Búsqueda de estudiantes en la tabla de integrantes del equipo */
    public string $buscarEstudiante = '';

    // ─── Involucrados ────────────────────────────────────────

    /** Búsqueda de involucrados */
    public string $buscarInvolucrado = '';

    /** Resultados de búsqueda de involucrados */
    public Collection $resultadosInvolucrados;

    /** Involucrados actuales del proyecto que se está editando */
    public array $involucradosProyecto = [];

    /** Formulario nuevo involucrado */
    public bool $mostrarFormNuevoInvolucrado = false;

    /** Involucrado seleccionado de búsqueda pendiente de rol */
    public ?int $involucradoPendienteId = null;

    public string $involucradoPendienteNombre = '';

    public string $nuevoInvolucradoNombre = '';

    public string $nuevoInvolucradoApellido = '';

    public string $nuevoInvolucradoCedula = '';

    /** Búsqueda de roles del catálogo */
    public string $buscarRol = '';

    /** Resultados de búsqueda de roles */
    public Collection $resultadosRoles;

    /** Roles seleccionados para el involucrado pendiente (array de id => nombre) */
    public array $rolesSeleccionados = [];

    /** Mostrar formulario de nuevo rol */
    public bool $mostrarFormNuevoRol = false;

    /** Nombre del nuevo rol a crear */
    public string $nuevoRolNombre = '';

    /** Involucrado al que se le están editando roles adicionales (pivot_id) */
    public ?int $involucradoEditandoRoles = null;

    /** Involucrado al que se le están editando roles (id del involucrado) */
    public ?int $editandoRolesInvolucradoId = null;

    /** Modal crear línea de investigación */
    public bool $mostrarModalLinea = false;

    public string $modalLineaNombre = '';

    public ?string $modalLineaNombreStatus = null;

    public function updatedModalLineaNombre(): void
    {
        if (strlen(trim($this->modalLineaNombre)) < 3) {
            $this->modalLineaNombreStatus = null;
            $this->resetValidation('modalLineaNombre');
            return;
        }
        $this->modalLineaNombreStatus = app(UnicidadNombreService::class)->check(
            LineaInvestigacion::class,
            'nombre_investigacion',
            $this->modalLineaNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalLineaNombreStatus === 'disponible') {
            $this->resetValidation('modalLineaNombre');
        }
    }

    public string $modalLineaDescripcion = '';

    public string $modalLineaArea = '';

    /** Búsqueda de líneas */
    public string $buscarLinea = '';

    /** Resultados de búsqueda */
    public Collection $lineasEncontradas;

    /** Modal crear metodología */
    public bool $mostrarModalMetodologia = false;

    public string $modalMetodologiaNombre = '';

    public ?string $modalMetodologiaNombreStatus = null;

    public function updatedModalMetodologiaNombre(): void
    {
        if (strlen(trim($this->modalMetodologiaNombre)) < 3) {
            $this->modalMetodologiaNombreStatus = null;
            $this->resetValidation('modalMetodologiaNombre');
            return;
        }
        $this->modalMetodologiaNombreStatus = app(UnicidadNombreService::class)->check(
            \App\Models\MetodologiaInvestigacion::class,
            'nombre',
            $this->modalMetodologiaNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalMetodologiaNombreStatus === 'disponible') {
            $this->resetValidation('modalMetodologiaNombre');
        }
    }

    public string $modalMetodologiaDescripcion = '';

    /** Búsqueda de metodologías */
    public string $buscarMetodologia = '';

    /** Resultados de búsqueda */
    public Collection $metodologiasEncontradas;

    /** Modal crear tipo de investigación */
    public bool $mostrarModalTipoInvestigacion = false;

    public string $modalTipoInvNombre = '';

    public ?string $modalTipoInvNombreStatus = null;

    public function updatedModalTipoInvNombre(): void
    {
        if (strlen(trim($this->modalTipoInvNombre)) < 3) {
            $this->modalTipoInvNombreStatus = null;
            $this->resetValidation('modalTipoInvNombre');
            return;
        }
        $this->modalTipoInvNombreStatus = app(UnicidadNombreService::class)->check(
            \App\Models\TipoInvestigacion::class,
            'nombre',
            $this->modalTipoInvNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalTipoInvNombreStatus === 'disponible') {
            $this->resetValidation('modalTipoInvNombre');
        }
    }

    public string $modalTipoInvDescripcion = '';

    /** Búsqueda de tipos de investigación */
    public string $buscarTipoInvestigacion = '';

    /** Resultados de búsqueda */
    public Collection $tiposInvestigacionEncontradas;

    /** Modal crear tipo de publicación */
    public bool $mostrarModalTipoPublicacion = false;

    public string $modalTipoPubNombre = '';

    public ?string $modalTipoPubNombreStatus = null;

    public function updatedModalTipoPubNombre(): void
    {
        if (strlen(trim($this->modalTipoPubNombre)) < 3) {
            $this->modalTipoPubNombreStatus = null;
            $this->resetValidation('modalTipoPubNombre');
            return;
        }
        $this->modalTipoPubNombreStatus = app(UnicidadNombreService::class)->check(
            \App\Models\TipoPublicacion::class,
            'nombre',
            $this->modalTipoPubNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalTipoPubNombreStatus === 'disponible') {
            $this->resetValidation('modalTipoPubNombre');
        }
    }

    public bool $modalTipoPubMencionHonorifica = false;

    /** Búsqueda de tipos de publicación */
    public string $buscarTipoPublicacion = '';

    /** Resultados de búsqueda */
    public Collection $tiposPublicacionEncontradas;

    /** Modal crear objetivo de investigación */
    public bool $mostrarModalObjetivo = false;
    public string $modalObjetivoNombre = '';

    public ?string $modalObjetivoNombreStatus = null;

    public function updatedModalObjetivoNombre(): void
    {
        if (strlen(trim($this->modalObjetivoNombre)) < 3) {
            $this->modalObjetivoNombreStatus = null;
            $this->resetValidation('modalObjetivoNombre');
            return;
        }
        $this->modalObjetivoNombreStatus = app(UnicidadNombreService::class)->check(
            \App\Models\ObjetivoInvestigacion::class,
            'nombre',
            $this->modalObjetivoNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalObjetivoNombreStatus === 'disponible') {
            $this->resetValidation('modalObjetivoNombre');
        }
    }

    public string $modalObjetivoDescripcion = '';
    public string $buscarObjetivo = '';
    public Collection $objetivosEncontrados;

    public function placeholder()
    {
        return <<<'HTML'
        <div style="padding: 20px; margin: 10px 0;">
            <style>
                @keyframes pgmPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.85; } }
                @keyframes pgmShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
            </style>
            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Cargando m&oacute;dulo de gesti&oacute;n...</legend>
                <div style="animation: pgmPulse 1.5s ease-in-out infinite;">
                    <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%" style="padding: 6px;">
                                <div style="height: 14px; width: 80%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px;"></div>
                            </td>
                            <td width="30%" style="padding: 6px;">
                                <div style="height: 28px; width: 90%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px;"></div>
                            </td>
                            <td width="20%" style="padding: 6px;">
                                <div style="height: 14px; width: 80%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px;"></div>
                            </td>
                            <td width="30%" style="padding: 6px;">
                                <div style="height: 28px; width: 90%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px;"></div>
                            </td>
                        </tr>
                    </table>
                    <div style="height: 18px; width: 40%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px; margin: 12px 0;"></div>
                    <div style="height: 40px; width: 100%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px; margin: 6px 0;"></div>
                    <div style="height: 40px; width: 100%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px; margin: 6px 0;"></div>
                    <div style="height: 40px; width: 100%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: pgmShimmer 1.5s infinite; border-radius: 3px; margin: 6px 0;"></div>
                </div>
                <div style="text-align: center; margin-top: 15px; font-size: 11px; color: #888;">
                    Consultando datos del sistema...
                </div>
            </fieldset>
        </div>
        HTML;
    }

    public function mount(ProyectoGestionService $gestion): void
    {
        $user = auth()->user();
        if ($user) {
            $userRoleService = app(UserRoleService::class);
            $activeRole = $userRoleService->getActiveRole($user);
            $this->esProfesor = $userRoleService->roleMatches('profesor proyecto', $activeRole);
            $this->esGestionador = $userRoleService->roleMatches('gestionador', $activeRole);
        }

        if ($editId = request()->query('edit')) {
            $this->edit((int) $editId, $gestion, app(GrupoProyectoService::class));
        }

        if ($detailsId = request()->query('details')) {
            $this->openDetails((int) $detailsId, $gestion);
        }

        if ($desdeGrupo = request()->query('desde_grupo')) {
            $this->registrarProyectoGrupo((int) $desdeGrupo);
        }

        $this->cargarGruposDocente($gestion);

        $this->lineasEncontradas = collect();
        $this->metodologiasEncontradas = collect();
        $this->tiposInvestigacionEncontradas = collect();
        $this->tiposPublicacionEncontradas = collect();
        $this->objetivosEncontrados = collect();
        $this->resultadosInvolucrados = collect();
        $this->resultadosRoles = collect();
    }

    protected function cargarGruposDocente(ProyectoGestionService $gestion): void
    {
        $user = auth()->user();
        if (!$user) return;

        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);

        $rolesConAcceso = ['profesor proyecto', 'administrador', 'gestionador', 'coordinador'];
        $puedeVer = false;
        foreach ($rolesConAcceso as $rol) {
            if ($userRoleService->roleMatches($rol, $activeRole)) {
                $puedeVer = true;
                break;
            }
        }

        if (!$puedeVer) return;

        $filtros = [];
        if ($this->filterGruposLapso !== '') $filtros['lapso'] = $this->filterGruposLapso;
        if (!in_array($activeRole, ['profesor proyecto'], true)) {
            if ($this->filterGruposPrograma !== '') $filtros['programa'] = $this->filterGruposPrograma;
            if ($this->filterGruposTrayecto !== '') $filtros['trayecto'] = $this->filterGruposTrayecto;
        }

        $grupos = $gestion->gruposDelDocente($user, $filtros);
        $this->gruposDocente = $grupos->toArray();
    }

    public function registrarProyectoGrupo(int $grpCodigo): void
    {
        $gestion = app(ProyectoGestionService::class);
        $user = auth()->user();
        if (!$user) return;

        $proyecto = $gestion->registrarProyectoDesdeGrupo($grpCodigo, $user);
        if (!$proyecto) {
            $this->safeDispatch('error', 'No se pudo registrar el proyecto. Grupo no encontrado.');
            return;
        }

        $this->safeDispatch('success', 'Proyecto registrado exitosamente. Complete los datos del proyecto.');
        $this->edit($proyecto->id, $gestion, app(GrupoProyectoService::class));
    }

    public function irAListado(): void
    {
        $this->viewMode = 'list';
        $this->selectedProject = null;
        $this->selectedProjectId = null;
        $this->motivo_rechazo = '';
        $this->resetPage();
        $this->cargarGruposDocente(app(ProyectoGestionService::class));
    }

    public function toggleTeamFilters(): void
    {
        $this->showTeamFilters = ! $this->showTeamFilters;
    }

    public function updatingListTab(): void
    {
        $this->resetPage();
    }

    protected function messages(): array
    {
        return [
            'titulo.required' => 'El título del proyecto es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 5 caracteres.',
            'resumen.required' => 'El resumen es obligatorio.',
            'resumen.min' => 'El resumen debe tener al menos 10 caracteres.',

            'lapso_academico_id.required' => 'Debe seleccionar un lapso académico.',
            'equipo_seccion_clave.required' => 'Debe validar el equipo (sección intranet).',
            'comunidad_id.required' => 'La comunidad es obligatoria. El grupo seleccionado debe tener una comunidad asignada.',
            'trayecto.required' => 'El trayecto es obligatorio.',
            'motivo_rechazo.required' => 'Debe indicar el motivo de rechazo.',
            'motivo_rechazo.min' => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }

    public function updatedEquipoSeccionClave(GrupoProyectoService $grupos, IntranetEquipoSeccionService $equipos): void
    {
        $clave = $this->equipo_seccion_clave ?? '';

        $this->programa_id_derived = null;
        $this->trayecto_derived = '';

        if ($clave === '') {
            $this->esGrupoRegistrado = false;
            $this->comunidadNombreGrupo = null;
            $this->titulo = '';
            $this->comunidad_id = '';
            return;
        }

        // Si se selecciona un grupo de proyecto registrado (EQGRP:)
        if (str_starts_with($clave, GrupoProyectoService::PREFIJO . ':')) {
            $grupo = $grupos->obtenerPorClave($clave);
            if ($grupo) {
                $this->esGrupoRegistrado = true;
                $this->titulo = $grupo->nombre ?? '';
                if (!empty($grupo->com_codigo)) {
                    $this->comunidad_id = (string) $grupo->com_codigo;
                    $comunidad = \App\Models\Comunidad::find($grupo->com_codigo);
                    $this->comunidadNombreGrupo = $comunidad?->nombre;
                } else {
                    $this->comunidad_id = '';
                    $this->comunidadNombreGrupo = null;
                }
                if (!empty($grupo->lap_codigo)) {
                    $this->filterLapsoEquipo = (string) $grupo->lap_codigo;
                }
                $this->filterProgramaEquipo = $grupo->pro_codigo !== null ? (string) $grupo->pro_codigo : '';
                $this->filterSeccionEquipo = $grupo->sec_codigo !== null ? (string) $grupo->sec_codigo : '';
                $this->programa_id_derived = $grupo->pro_codigo ?? null;
                // Derive trayecto from grupo's seccion if available
                if (!empty($grupo->sec_codigo) && !empty($grupo->lap_codigo)) {
                    try {
                        $traRow = \Illuminate\Support\Facades\DB::connection($equipos->academicConnection())
                            ->table('seccion as sec')
                            ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                            ->leftJoin('semestre as sem', 'sem.sem_codigo', '=', 'sec.sec_cod_semestre')
                            ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'sem.sem_cod_trayecto')
                            ->where('sec.sec_codigo', $grupo->sec_codigo)
                            ->where('sec.sec_cod_lapso_academico', $grupo->lap_codigo)
                            ->value('tra.tra_nombre');
                        $this->trayecto_derived = trim((string) ($traRow ?? ''));
                    } catch (\Throwable) {
                        $this->trayecto_derived = '';
                    }
                }
                // Load group members for leader selection
                $this->cargarMiembrosGrupo($grupos, $clave);
                return;
            }
        }

        // Es una sección de intranet (EQSEC:)
        $this->esGrupoRegistrado = false;
        $this->comunidadNombreGrupo = null;
        $this->comunidad_id = '';

        $partes = $equipos->parsearClave($clave);
        if ($partes) {
            $this->filterLapsoEquipo = (string) $partes['lap_codigo'];
        }

        // Consultar datos del equipo para auto-rellenar título y derivar programa/trayecto
        try {
            $conn = $equipos->academicConnection();
            $row = \Illuminate\Support\Facades\DB::connection($conn)
                ->table('seccion as sec')
                ->join('lapso_academico as lap', 'lap.lap_codigo', '=', 'sec.sec_cod_lapso_academico')
                ->leftJoin('malla as mal', 'mal.mal_codigo', '=', 'sec.sec_cod_malla')
                ->leftJoin('programa as pro', 'pro.pro_codigo', '=', 'mal.mal_cod_programa')
                ->leftJoin('semestre as sem', 'sem.sem_codigo', '=', 'sec.sec_cod_semestre')
                ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'sem.sem_cod_trayecto')
                ->where('sec.sec_codigo', $partes['sec_codigo'])
                ->where('lap.lap_codigo', $partes['lap_codigo'])
                ->select(['sec.sec_nombre', 'lap.lap_nombre', 'pro.pro_codigo', 'pro.pro_siglas', 'tra.tra_nombre'])
                ->first();

            if ($row) {
                $this->titulo = trim('Sección ' . $row->sec_nombre . ' · ' . $row->lap_nombre);
                $this->programa_id_derived = $row->pro_codigo ?? null;
                $this->trayecto_derived = trim($row->tra_nombre ?? '');
                $this->filterProgramaEquipo = $row->pro_codigo !== null ? (string) $row->pro_codigo : '';
                $this->filterSeccionEquipo = (string) $partes['sec_codigo'];
            } else {
                $this->titulo = 'Sección #' . $partes['sec_codigo'];
                $this->filterSeccionEquipo = (string) $partes['sec_codigo'];
            }
        } catch (\Throwable) {
            $this->titulo = 'Sección #' . $partes['sec_codigo'];
        }
    }

    public function updatingFilterLapsoEquipo(): void
    {
        $this->filterProgramaEquipo = '';
        $this->filterSeccionEquipo = '';
        $this->equipo_seccion_clave = '';
    }

    public function updatingFilterProgramaEquipo(): void
    {
        $this->filterSeccionEquipo = '';
        $this->equipo_seccion_clave = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterGruposLapso(): void
    {
        $this->filterGruposPrograma = '';
        $this->filterGruposTrayecto = '';
        $this->cargarGruposDocente(app(ProyectoGestionService::class));
    }

    public function updatingFilterGruposPrograma(): void
    {
        $this->filterGruposTrayecto = '';
        $this->cargarGruposDocente(app(ProyectoGestionService::class));
    }

    public function updatingFilterGruposTrayecto(): void
    {
        $this->cargarGruposDocente(app(ProyectoGestionService::class));
    }

    protected function cargarMiembrosGrupo(GrupoProyectoService $grupos, string $clave): void
    {
        $integrantes = $grupos->integrantes($clave);
        $this->miembrosGrupo = $integrantes->map(fn($m) => [
            'cedula' => $m->cedula,
            'nombre' => $m->nombre,
            'apellido' => $m->apellido ?? '',
            'rol_id' => $m->rol_id ?? 0,
        ])->toArray();
        $this->selectedLeaders = [];
        foreach ($this->miembrosGrupo as $m) {
            if ((int) ($m['rol_id'] ?? 0) === IntranetEquipoSeccionService::ROL_LIDER) {
                $this->selectedLeaders[] = $m['cedula'];
            }
        }
    }

    public function abrirModalLinea(): void
    {
        $this->mostrarModalLinea = true;
        $this->modalLineaNombre = '';
        $this->modalLineaNombreStatus = null;
        $this->modalLineaDescripcion = '';
        $this->modalLineaArea = '';
        $this->buscarLinea = '';
        $this->lineasEncontradas = collect();
    }

    public function cerrarModalLinea(): void
    {
        $this->mostrarModalLinea = false;
    }

    public function abrirModalMetodologia(): void
    {
        $this->mostrarModalMetodologia = true;
        $this->modalMetodologiaNombre = '';
        $this->modalMetodologiaNombreStatus = null;
        $this->modalMetodologiaDescripcion = '';
        $this->buscarMetodologia = '';
        $this->metodologiasEncontradas = collect();
    }

    public function cerrarModalMetodologia(): void
    {
        $this->mostrarModalMetodologia = false;
    }

    public function updatedBuscarMetodologia(): void
    {
        $q = trim($this->buscarMetodologia);
        if ($q === '') {
            $this->metodologiasEncontradas = collect();
            return;
        }            $this->metodologiasEncontradas = \App\Models\MetodologiaInvestigacion::whereRaw('mei_nombre ILIKE ?', ["%{$q}%"])
            ->orWhereRaw('mei_descripcion ILIKE ?', ["%{$q}%"])
            ->orderByRaw('mei_nombre')
            ->get();
    }

    public function seleccionarMetodologia(int $id): void
    {
        $this->metodologia_id = (string) $id;
        $this->buscarMetodologia = '';
        $this->metodologiasEncontradas = collect();
    }

    public function guardarMetodologiaModal(): void
    {
        $this->validate([
            'modalMetodologiaNombre' => 'required|string|max:255',
        ], [
            'modalMetodologiaNombre.required' => 'El nombre de la metodología es obligatorio.',
        ]);

        if ($this->modalMetodologiaNombreStatus === 'no_disponible') {
            $this->addError('modalMetodologiaNombre', 'Este nombre ya está en uso.');
            return;
        }

        $metodologia = \App\Models\MetodologiaInvestigacion::create([
            'nombre' => $this->modalMetodologiaNombre,
            'descripcion' => $this->modalMetodologiaDescripcion ?: null,
            'estado_logico' => true,
        ]);

        $this->metodologia_id = (string) $metodologia->id;
        $this->cerrarModalMetodologia();
    }

    // ─── Tipo de Investigación ───────────────────────────────
    public function abrirModalTipoInvestigacion(): void
    {
        $this->mostrarModalTipoInvestigacion = true;
        $this->modalTipoInvNombre = '';
        $this->modalTipoInvNombreStatus = null;
        $this->modalTipoInvDescripcion = '';
        $this->buscarTipoInvestigacion = '';
        $this->tiposInvestigacionEncontradas = collect();
    }

    public function cerrarModalTipoInvestigacion(): void
    {
        $this->mostrarModalTipoInvestigacion = false;
    }

    public function updatedBuscarTipoInvestigacion(): void
    {
        $q = trim($this->buscarTipoInvestigacion);
        if ($q === '') {
            $this->tiposInvestigacionEncontradas = collect();
            return;
        }
        $this->tiposInvestigacionEncontradas = \App\Models\TipoInvestigacion::whereRaw('tin_nombre ILIKE ?', ["%{$q}%"])
            ->orWhereRaw('tin_descripcion ILIKE ?', ["%{$q}%"])
            ->orderByRaw('tin_nombre')
            ->get();
    }

    public function seleccionarTipoInvestigacion(int $id): void
    {
        $this->tipo_investigacion_id = (string) $id;
        $this->buscarTipoInvestigacion = '';
        $this->tiposInvestigacionEncontradas = collect();
    }

    public function guardarTipoInvestigacionModal(): void
    {
        $this->validate([
            'modalTipoInvNombre' => 'required|string|max:255',
        ], [
            'modalTipoInvNombre.required' => 'El nombre del tipo de investigación es obligatorio.',
        ]);

        if ($this->modalTipoInvNombreStatus === 'no_disponible') {
            $this->addError('modalTipoInvNombre', 'Este nombre ya está en uso.');
            return;
        }

        $tipo = \App\Models\TipoInvestigacion::create([
            'nombre' => $this->modalTipoInvNombre,
            'descripcion' => $this->modalTipoInvDescripcion ?: null,
            'estado_logico' => true,
        ]);

        $this->tipo_investigacion_id = (string) $tipo->id;
        $this->cerrarModalTipoInvestigacion();
    }

    // ─── Tipo de Publicación ────────────────────────────────
    public function abrirModalTipoPublicacion(): void
    {
        $this->mostrarModalTipoPublicacion = true;
        $this->modalTipoPubNombre = '';
        $this->modalTipoPubNombreStatus = null;
        $this->modalTipoPubMencionHonorifica = false;
        $this->buscarTipoPublicacion = '';
        $this->tiposPublicacionEncontradas = collect();
    }

    public function abrirModalObjetivo(): void
    {
        $this->mostrarModalObjetivo = true;
        $this->modalObjetivoNombre = '';
        $this->modalObjetivoNombreStatus = null;
        $this->modalObjetivoDescripcion = '';
        $this->buscarObjetivo = '';
        $this->objetivosEncontrados = collect();
    }

    public function cerrarModalObjetivo(): void
    {
        $this->mostrarModalObjetivo = false;
    }

    public function updatedBuscarObjetivo(): void
    {
        $q = trim($this->buscarObjetivo);
        if ($q === '') {
            $this->objetivosEncontrados = collect();
            return;
        }
        $this->objetivosEncontrados = \App\Models\ObjetivoInvestigacion::whereRaw('obi_nombre ILIKE ?', ["%{$q}%"])
            ->orWhereRaw('obi_descripcion ILIKE ?', ["%{$q}%"])
            ->orderByRaw('obi_nombre')
            ->get();
    }

    public function seleccionarObjetivo(int $id): void
    {
        $this->objetivo_investigacion_id = (string) $id;
        $this->buscarObjetivo = '';
        $this->objetivosEncontrados = collect();
    }

    public function guardarObjetivoModal(): void
    {
        $this->validate([
            'modalObjetivoNombre' => 'required|string|max:255',
        ], [
            'modalObjetivoNombre.required' => 'El nombre del objetivo es obligatorio.',
        ]);

        if ($this->modalObjetivoNombreStatus === 'no_disponible') {
            $this->addError('modalObjetivoNombre', 'Este nombre ya está en uso.');
            return;
        }

        $objetivo = \App\Models\ObjetivoInvestigacion::create([
            'nombre' => $this->modalObjetivoNombre,
            'descripcion' => $this->modalObjetivoDescripcion ?: null,
            'estado_logico' => true,
        ]);

        $this->objetivo_investigacion_id = (string) $objetivo->id;
        $this->cerrarModalObjetivo();
    }

    public function cerrarModalTipoPublicacion(): void
    {
        $this->mostrarModalTipoPublicacion = false;
    }

    public function updatedBuscarTipoPublicacion(): void
    {
        $q = trim($this->buscarTipoPublicacion);
        if ($q === '') {
            $this->tiposPublicacionEncontradas = collect();
            return;
        }
        $this->tiposPublicacionEncontradas = \App\Models\TipoPublicacion::whereRaw('tpu_nombre ILIKE ?', ["%{$q}%"])
            ->orderByRaw('tpu_nombre')
            ->get();
    }

    public function seleccionarTipoPublicacion(int $id): void
    {
        $this->tipo_publicacion_id = (string) $id;
        $this->buscarTipoPublicacion = '';
        $this->tiposPublicacionEncontradas = collect();
    }

    public function guardarTipoPublicacionModal(): void
    {
        $this->validate([
            'modalTipoPubNombre' => 'required|string|max:255',
        ], [
            'modalTipoPubNombre.required' => 'El nombre del tipo de publicación es obligatorio.',
        ]);

        if ($this->modalTipoPubNombreStatus === 'no_disponible') {
            $this->addError('modalTipoPubNombre', 'Este nombre ya está en uso.');
            return;
        }

        $tipo = \App\Models\TipoPublicacion::create([
            'nombre' => $this->modalTipoPubNombre,
            'mencion_honorifica' => $this->modalTipoPubMencionHonorifica,
            'estado_logico' => true,
        ]);

        $this->tipo_publicacion_id = (string) $tipo->id;
        $this->cerrarModalTipoPublicacion();
    }

    // ─────────────────────────────────────────────────────────

    public function updatedBuscarLinea(): void
    {
        $q = trim($this->buscarLinea);
        if ($q === '') {
            $this->lineasEncontradas = collect();
            return;
        }
        $this->lineasEncontradas = LineaInvestigacion::whereRaw('lin_nombre_investigacion ILIKE ?', ["%{$q}%"])
            ->orWhereRaw('lin_descripcion ILIKE ?', ["%{$q}%"])
            ->orderByRaw('lin_nombre_investigacion')
            ->get();
    }

    public function seleccionarLinea(int $id): void
    {
        $this->linea_investigacion_id = (string) $id;
        $this->buscarLinea = '';
        $this->lineasEncontradas = collect();
    }

    public function guardarLineaModal(): void
    {
        $this->validate([
            'modalLineaNombre' => 'required|string|max:255',
        ], [
            'modalLineaNombre.required' => 'El nombre de la línea es obligatorio.',
        ]);

        if ($this->modalLineaNombreStatus === 'no_disponible') {
            $this->addError('modalLineaNombre', 'Este nombre ya está en uso.');
            return;
        }

        $linea = LineaInvestigacion::create([
            'nombre_investigacion' => $this->modalLineaNombre,
            'descripcion' => $this->modalLineaDescripcion ?: null,
            'area_de_investigacion' => $this->modalLineaArea ?: null,
            'activo' => true,
        ]);

        $this->linea_investigacion_id = (string) $linea->id;
        $this->cerrarModalLinea();
    }

    public function updatingFilterEstadoList(): void
    {
        $this->resetPage();
    }

    public function updatingFilterComunidadList(): void
    {
        $this->resetPage();
    }

    public function edit(int $id, ProyectoGestionService $gestion, GrupoProyectoService $grupos): void
    {
        $this->resetFormulario();
        $this->showTeamFilters = true;
        $this->fill($gestion->cargarParaEdicion($id));
        $this->viewMode = 'form';

        // Detectar si el usuario actual es lider del proyecto
        $user = auth()->user();
        $proyecto = Proyecto::find($id);
        $this->esLider = $proyecto ? $gestion->usuarioEsLiderDelProyecto($user, $proyecto) : false;
        $this->modoActualizacion = $this->esLider && !$gestion->usuarioEsAdminEnSistema($user);

        // Cargar involucrados existentes del proyecto
        $this->cargarInvolucradosProyecto($gestion);

        // Reconstruir estado de grupo si el equipo seleccionado es un grupo de proyecto
        $clave = $this->equipo_seccion_clave ?? '';
        if (str_starts_with($clave, GrupoProyectoService::PREFIJO . ':')) {
            $grupo = $grupos->obtenerPorClave($clave);
            if ($grupo) {
                $this->esGrupoRegistrado = true;
                $this->titulo = $grupo->nombre ?? $this->titulo;
                if (!empty($grupo->com_codigo)) {
                    $comunidad = \App\Models\Comunidad::find($grupo->com_codigo);
                    $this->comunidadNombreGrupo = $comunidad?->nombre;
                    if (empty($this->comunidad_id)) {
                        $this->comunidad_id = (string) $grupo->com_codigo;
                    }
                }
                $this->cargarMiembrosGrupo($grupos, $clave);
            }
        }
    }

    // ─── Involucrados ─────────────────────────────────────────────

    public function updatedBuscarInvolucrado(): void
    {
        $q = trim($this->buscarInvolucrado);
        if ($q === '') {
            $this->resultadosInvolucrados = collect();
            return;
        }
        $this->resultadosInvolucrados = app(ProyectoGestionService::class)->buscarInvolucrados($q);
    }

    public function updatedNuevoInvolucradoCedula(): void
    {
        $cedula = trim($this->nuevoInvolucradoCedula);
        if ($cedula === '') return;

        $existente = Involucrado::where('cedula', $cedula)->first();
        if ($existente) {
            $this->nuevoInvolucradoNombre = $existente->nombre;
            $this->nuevoInvolucradoApellido = $existente->apellido;
        }
    }

    public function updatedBuscarRol(): void
    {
        $q = trim($this->buscarRol);
        if ($q === '') {
            $this->resultadosRoles = collect();
            return;
        }
        $this->resultadosRoles = app(ProyectoGestionService::class)->buscarRoles($q);
    }

    public function seleccionarRol(int $rolId): void
    {
        $rol = RolInvolucrado::find($rolId);
        if ($rol && !isset($this->rolesSeleccionados[$rolId])) {
            $this->rolesSeleccionados[$rolId] = $rol->nombre;
        }
        $this->buscarRol = '';
        $this->resultadosRoles = collect();
    }

    public function quitarRolSeleccionado(int $rolId): void
    {
        unset($this->rolesSeleccionados[$rolId]);
    }

    public function toggleFormNuevoRol(): void
    {
        $this->mostrarFormNuevoRol = !$this->mostrarFormNuevoRol;
        if (!$this->mostrarFormNuevoRol) {
            $this->nuevoRolNombre = '';
        }
    }

    public function crearNuevoRol(): void
    {
        $nombre = trim($this->nuevoRolNombre);
        if ($nombre === '') return;

        $rol = app(ProyectoGestionService::class)->crearRol($nombre);
        $this->rolesSeleccionados[$rol->id] = $rol->nombre;
        $this->mostrarFormNuevoRol = false;
        $this->nuevoRolNombre = '';
        $this->safeDispatch('success', 'Rol creado y seleccionado.');
    }

    public function seleccionarInvolucrado(int $involucradoId): void
    {
        if (!$this->editingId) return;

        $inv = Involucrado::find($involucradoId);
        $this->involucradoPendienteNombre = $inv ? trim($inv->nombre . ' ' . $inv->apellido) : '';
        $this->involucradoPendienteId = $involucradoId;
        $this->rolesSeleccionados = [];
        $this->buscarRol = '';
        $this->resultadosRoles = collect();
    }

    public function confirmarRolInvolucrado(): void
    {
        if (!$this->editingId || !$this->involucradoPendienteId) return;

        if (empty($this->rolesSeleccionados)) {
            $this->safeDispatch('warning', 'Debe seleccionar al menos un rol para el involucrado.');
            return;
        }

        app(ProyectoGestionService::class)->agregarInvolucradoAProyecto(
            $this->editingId,
            $this->involucradoPendienteId,
            array_keys($this->rolesSeleccionados)
        );

        $this->involucradoPendienteId = null;
        $this->rolesSeleccionados = [];
        $this->buscarInvolucrado = '';
        $this->resultadosInvolucrados = collect();
        $this->cargarInvolucradosProyecto();
        $this->safeDispatch('success', 'Involucrado agregado al proyecto.');
    }

    public function cancelarSeleccionInvolucrado(): void
    {
        $this->involucradoPendienteId = null;
        $this->involucradoPendienteNombre = '';
        $this->rolesSeleccionados = [];
        $this->buscarRol = '';
        $this->resultadosRoles = collect();
    }

    /**
     * Abre el panel para agregar más roles a un involucrado ya existente en el proyecto.
     */
    public function agregarRolesAInvolucrado(int $pivotId, int $involucradoId): void
    {
        $this->involucradoEditandoRoles = $pivotId;
        $this->editandoRolesInvolucradoId = $involucradoId;
        $this->rolesSeleccionados = [];
        $this->buscarRol = '';
        $this->resultadosRoles = collect();
        $this->mostrarFormNuevoRol = false;
        $this->nuevoRolNombre = '';
    }

    /**
     * Cierra el panel de edición de roles.
     */
    public function cancelarEdicionRoles(): void
    {
        $this->involucradoEditandoRoles = null;
        $this->editandoRolesInvolucradoId = null;
        $this->rolesSeleccionados = [];
        $this->buscarRol = '';
        $this->resultadosRoles = collect();
    }

    /**
     * Confirma los roles adicionales para un involucrado ya existente en el proyecto.
     */
    public function confirmarRolesAdicionales(): void
    {
        if (!$this->editingId || !$this->involucradoEditandoRoles || !$this->editandoRolesInvolucradoId) return;

        if (empty($this->rolesSeleccionados)) {
            $this->safeDispatch('warning', 'Debe seleccionar al menos un rol para agregar.');
            return;
        }

        app(ProyectoGestionService::class)->agregarInvolucradoAProyecto(
            $this->editingId,
            $this->editandoRolesInvolucradoId,
            array_keys($this->rolesSeleccionados)
        );

        $this->cancelarEdicionRoles();
        $this->cargarInvolucradosProyecto();
        $this->safeDispatch('success', 'Roles agregados al involucrado.');
    }

    /**
     * Quita un rol específico de un involucrado en el proyecto.
     */
    public function quitarRolDeInvolucrado(int $pivotId, int $rolId): void
    {
        if (!$this->editingId) return;

        app(ProyectoGestionService::class)->quitarRolDeInvolucrado($pivotId, $rolId);
        $this->cargarInvolucradosProyecto();
        $this->safeDispatch('success', 'Rol eliminado del involucrado.');
    }

    public function toggleFormNuevoInvolucrado(): void
    {
        $this->mostrarFormNuevoInvolucrado = !$this->mostrarFormNuevoInvolucrado;
        if ($this->mostrarFormNuevoInvolucrado) {
            // Al abrir, limpiar roles previos para evitar contaminación
            $this->rolesSeleccionados = [];
            $this->buscarRol = '';
            $this->resultadosRoles = collect();
            $this->mostrarFormNuevoRol = false;
            $this->nuevoRolNombre = '';
        } else {
            $this->nuevoInvolucradoNombre = '';
            $this->nuevoInvolucradoApellido = '';
            $this->nuevoInvolucradoCedula = '';
            $this->rolesSeleccionados = [];
            $this->buscarRol = '';
            $this->resultadosRoles = collect();
        }
    }

    public function agregarNuevoInvolucrado(): void
    {
        $this->validate([
            'nuevoInvolucradoNombre' => 'required|string|max:255',
            'nuevoInvolucradoApellido' => 'required|string|max:255',
            'nuevoInvolucradoCedula' => 'required|string|max:50',
        ], [
            'nuevoInvolucradoNombre.required' => 'El nombre del involucrado es obligatorio.',
            'nuevoInvolucradoApellido.required' => 'El apellido del involucrado es obligatorio.',
            'nuevoInvolucradoCedula.required' => 'La cédula del involucrado es obligatoria.',
        ]);

        if (empty($this->rolesSeleccionados)) {
            $this->safeDispatch('warning', 'Debe seleccionar al menos un rol para el involucrado.');
            return;
        }

        if (!$this->editingId) return;

        $gestion = app(ProyectoGestionService::class);
        $involucrado = $gestion->crearInvolucrado(
            $this->nuevoInvolucradoNombre,
            $this->nuevoInvolucradoApellido,
            $this->nuevoInvolucradoCedula
        );

        $roleIds = array_keys($this->rolesSeleccionados);
        $gestion->agregarInvolucradoAProyecto(
            $this->editingId,
            $involucrado->id,
            $roleIds
        );

        $this->toggleFormNuevoInvolucrado();
        $this->rolesSeleccionados = [];
        $this->cargarInvolucradosProyecto();
        $this->safeDispatch('success', 'Involucrado registrado y agregado al proyecto.');
    }

    public function quitarInvolucrado(int $involucradoId): void
    {
        if (!$this->editingId) return;

        app(ProyectoGestionService::class)->quitarInvolucradoDeProyecto(
            $this->editingId,
            $involucradoId
        );

        $this->cargarInvolucradosProyecto();
        $this->safeDispatch('success', 'Involucrado eliminado del proyecto.');
    }

    protected function cargarInvolucradosProyecto(?ProyectoGestionService $gestion = null): void
    {
        if (!$this->editingId) {
            $this->involucradosProyecto = [];
            return;
        }

        $gestion = $gestion ?? app(ProyectoGestionService::class);
        $this->involucradosProyecto = $gestion
            ->involucradosDelProyecto($this->editingId)
            ->toArray();
    }

    public function cancel(): void
    {
        $this->viewMode = 'list';
        $this->resetFormulario();
    }

    public function save(ProyectoGestionService $gestion): void
    {
        $user = auth()->user();
        $estado = $this->estadoFormulario();

        // Generar reglas de validación dinámicas por componente (tipo archivo y tamaño máximo)
        $docRules = [];
        if (!empty($this->archivosComponente)) {
            $componentes = \App\Models\Componente::whereIn('id', array_keys($this->archivosComponente))->get()->keyBy('id');
            foreach ($this->archivosComponente as $compId => $file) {
                if (!$file) continue;
                $comp = $componentes->get((int) $compId);
                $maxKb = $comp ? ($comp->tamano_maximo_mb ?? 10) * 1024 : 10240;
                $mimes = $comp ? $comp->tipo_archivo ?? 'pdf' : 'pdf';
                // Solo validar mimes si es un tipo conocido (pdf, zip, rar, doc, docx, xls, xlsx)
                $mimesList = array_intersect(explode(',', $mimes), ['pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx']);
                $rule = 'nullable|file|max:' . $maxKb;
                if (!empty($mimesList)) {
                    $rule .= '|mimes:' . implode(',', $mimesList);
                }
                $docRules['archivosComponente.' . $compId] = $rule;
            }
        }

        // Validar vigencia del estudiante líder (solo si está en modo actualización)
        if ($this->modoActualizacion && $this->editingId) {
            $userRoleService = app(\App\Services\UserRoleService::class);
            $activeRole = $userRoleService->getActiveRole($user);
            $esAdminOCoordinador = $userRoleService->roleMatches('administrador', $activeRole)
                || $userRoleService->roleMatches('coordinador', $activeRole);

            // Solo validar vigencia si NO es admin/coordinador
            if (!$esAdminOCoordinador) {
                $proyecto = \App\Models\Proyecto::find($this->editingId);
                if ($proyecto && !$gestion->estudianteLiderVigente($user, $proyecto)) {
                    $this->safeDispatch('error', 'No puedes subir documentos porque ya no estás inscrito vigentemente en la sección/lapso de este proyecto. Contacta al coordinador o profesor.');
                    return;
                }
            }
        }

        if ($this->modoActualizacion) {
            if (!empty($docRules)) {
                $this->validate($docRules);
            }
        } else {
            $rules = $gestion->reglasValidacion($estado, $user, $this->editingId !== null);
            // Si hay archivos subidos, validarlos también
            if (!empty(array_filter($this->archivosComponente))) {
                $rules = array_merge($rules, $docRules);
            }
            $this->validate($rules, $this->messages());
        }

        $proyecto = $gestion->guardar(
            $this->editingId,
            $estado,
            $user,
            $this->archivosComponente,
            $this->esGrupoRegistrado ? $this->selectedLeaders : [],
        );

        // Si lider actualizo, marcar como completado (listo para revision)
        if ($this->modoActualizacion && $proyecto) {
            $proyecto->update([
                'actualizado_por_estudiante' => true,
                'fecha_actualizacion_estudiante' => now(),
                'estado_validacion' => 'completado',
                'estado_logico' => true,
            ]);
        }

        $this->viewMode = 'list';
        $this->safeDispatch('success', $this->modoActualizacion ? 'Documentos subidos con éxito. El profesor será notificado.' : ($this->editingId ? 'Proyecto actualizado con éxito.' : 'Proyecto registrado con éxito.'));
        $this->resetFormulario();
        $this->cargarGruposDocente(app(ProyectoGestionService::class));
        $this->safeRefreshIcons();
    }

     /**
      * Cierra el formulario del profesor, guarda los datos y los documentos si se subieron,
      * y notifica a los líderes para que completen los documentos faltantes.
      */
     public function cerrarFormulario(ProyectoGestionService $gestion): void
     {
         if (!empty($this->archivosComponente)) {
             // Usar las mismas reglas dinámicas que save()
             $componentes = \App\Models\Componente::whereIn('id', array_keys($this->archivosComponente))->get()->keyBy('id');
             $docRules = [];
             foreach ($this->archivosComponente as $compId => $file) {
                 if (!$file) continue;
                 $comp = $componentes->get((int) $compId);
                 $maxKb = $comp ? ($comp->tamano_maximo_mb ?? 10) * 1024 : 10240;
                 $mimes = $comp ? $comp->tipo_archivo ?? 'pdf' : 'pdf';
                 $mimesList = array_intersect(explode(',', $mimes), ['pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx']);
                 $rule = 'nullable|file|max:' . $maxKb;
                 if (!empty($mimesList)) {
                     $rule .= '|mimes:' . implode(',', $mimesList);
                 }
                 $docRules['archivosComponente.' . $compId] = $rule;
             }
             $this->validate($docRules);
         }

         $gestion->guardar(
             $this->editingId,
             $this->estadoFormulario(),
             auth()->user(),
             $this->archivosComponente,
             $this->esGrupoRegistrado ? $this->selectedLeaders : [],
         );
 
         $this->safeDispatch('success', 'Formulario guardado con éxito. Se han guardado los datos y documentos del proyecto.');
         $this->irAListado();
     }

    public function toggleStatus(int $id, ProyectoGestionService $gestion): void
    {
        $gestion->alternarEstado($id);
        $this->safeDispatch('success', 'Estado del proyecto actualizado.');
        $this->safeRefreshIcons();
    }

    public function delete(int $id, ProyectoGestionService $gestion): void
    {
        $gestion->eliminar($id);
        $this->safeDispatch('success', 'Proyecto eliminado correctamente.');
        $this->safeRefreshIcons();
    }

    public function approve(int $id, ProyectoGestionService $gestion): void
    {
        try {
            $gestion->aprobar($id);
            $this->safeDispatch('success', 'Proyecto aprobado con éxito.');
        } catch (AuthorizationException $e) {
            $this->safeDispatch('error', $e->getMessage());
        }
        $this->safeRefreshIcons();
    }

    public function openReject(int $id): void
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function openDetails(int $id, ProyectoGestionService $gestion): void
    {
        $this->selectedProject = $gestion->proyectoParaFicha($id);
        $this->viewMode = 'details';
        $this->safeRefreshIcons();
    }

    public function confirmReject(ProyectoGestionService $gestion): void
    {
        $this->validate([
            'motivo_rechazo' => 'required|min:10',
        ], $this->messages());

        try {
            $gestion->rechazar((int) $this->selectedProjectId, $this->motivo_rechazo);
            $this->irAListado();
            $this->safeDispatch('success', 'Proyecto rechazado.');
        } catch (AuthorizationException $e) {
            $this->safeDispatch('error', $e->getMessage());
        }
        $this->safeRefreshIcons();
    }

    public function approveFromDetails(int $id, ProyectoGestionService $gestion): void
    {
        try {
            $gestion->aprobar($id);
            $this->irAListado();
            $this->safeDispatch('success', 'Proyecto aprobado con éxito.');
        } catch (AuthorizationException $e) {
            $this->safeDispatch('error', $e->getMessage());
        }
        $this->safeRefreshIcons();
    }

    public function rejectFromDetails(int $id): void
    {
        $this->openReject($id);
    }

    protected function usuarioEsLider(ProyectoGestionService $gestion): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        $userRoleService = app(\App\Services\UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);
        if ($userRoleService->roleMatches('administrador', $activeRole)
            || $userRoleService->roleMatches('coordinador', $activeRole)
            || $userRoleService->roleMatches('gestionador', $activeRole)) return false;
        if (!$this->editingId) return false;
        $proyecto = Proyecto::find($this->editingId);
        if (!$proyecto) return false;
        return $gestion->usuarioEsLiderDelProyecto($user, $proyecto);
    }

    public function render(ProyectoGestionService $gestion)
    {
        $estado = $this->estadoFormulario();
        $page = $this->getPage();
        $user = auth()->user();

        $esLiderGlobal = $this->usuarioEsLider($gestion);

        // Only compute expensive leader data when in list view for non-profesores
        $esEstudianteLider = false;
        $proyectosLiderIds = [];
        $proyectosLider = collect();
        if ($user && !$this->esProfesor && $this->viewMode === 'list') {
            $userRoleService = app(UserRoleService::class);
            $activeRole = $userRoleService->getActiveRole($user);
            if (!$userRoleService->roleMatches('administrador', $activeRole)
                && !$userRoleService->roleMatches('coordinador', $activeRole)
                && !$userRoleService->roleMatches('gestionador', $activeRole)) {
                $esEstudianteLider = true;
                $proyectosLiderIds = $gestion->proyectosDondeEsMiembro($user);
                $proyectosLider = $gestion->proyectosLider($user);
            }
        }

        $datos = match (true) {
            $this->viewMode === 'list' && !$this->esProfesor && !$esEstudianteLider => $gestion->datosVistaListado([
                'search' => $this->search,
                'estado' => $this->filterEstadoList,
                'comunidad' => $this->filterComunidadList,
                'lapso' => $this->filterGruposLapso,
            ], $page, $user),
            $this->viewMode === 'list' && $this->esProfesor => array_merge(
                ['comunidades' => $gestion->comunidadesOrdenadas()],
                $gestion->datosVistaListado([
                    'search' => $this->search,
                    'estado' => $this->filterEstadoList,
                    'comunidad' => $this->filterComunidadList,
                    'lapso' => $this->filterGruposLapso,
                ], $page, $user)
            ),
            $this->viewMode === 'list' && $esEstudianteLider => ['comunidades' => $gestion->comunidadesOrdenadas()],
            $this->viewMode === 'form' => $gestion->datosVistaFormulario($estado),
            default => ['comunidades' => $gestion->comunidadesOrdenadas()],
        };

        // Catalogs for group filters - only when form is visible or gruposDocente are loaded
        $equipoSeccion = app(IntranetEquipoSeccionService::class);
        $lapsoFiltro = $this->filterGruposLapso !== '' ? (int) $this->filterGruposLapso : null;
        $programaFiltro = $this->filterGruposPrograma !== '' ? (int) $this->filterGruposPrograma : null;

        $lapsosFiltro = \Illuminate\Support\Facades\Cache::remember(
            'proyecto_manager_lapsos',
            now()->addMinutes(10),
            fn () => \App\Models\LapsoAcademico::activos()->orderByDesc('lap_codigo')->get()
        );

        // Only query programas/trayectos from intranet when we have a lapso filter
        $programasFiltro = collect();
        $trayectosFiltro = collect();
        if ($lapsoFiltro && ($this->viewMode !== 'list' || $this->gruposDocente)) {
            $programasFiltro = $equipoSeccion->programasEnLapso($lapsoFiltro);
            $trayectosFiltro = $lapsoFiltro ? $equipoSeccion->trayectosEnLapso($lapsoFiltro, $programaFiltro) : collect();
        }

        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);
        $puedeFiltrarGrupos = true;

        // Team selection filter data (for showTeamFilters section)
        $equipoLapso = $this->filterLapsoEquipo !== '' ? (int) $this->filterLapsoEquipo : null;
        $equipoPrograma = $this->filterProgramaEquipo !== '' ? (int) $this->filterProgramaEquipo : null;
        $equipoSeccionCodigo = $this->filterSeccionEquipo !== '' ? (int) $this->filterSeccionEquipo : null;

        $lapsos = $lapsosFiltro;

        $programasEquipo = collect();
        if ($equipoLapso) {
            $programasEquipo = $equipoSeccion->programasEnLapso($equipoLapso);
        }

        $seccionesEquipo = collect();
        if ($equipoLapso && $equipoPrograma) {
            $seccionesEquipo = $equipoSeccion->seccionesEnLapso($equipoLapso, $equipoPrograma);
        }

        // Available groups for the selected filters
        $equipos_disp = collect();
        try {
            $gruposSvc = app(GrupoProyectoService::class);
            if ($gruposSvc->tablaDisponible()) {
                $filtrosEquipos = [];
                if ($equipoLapso) $filtrosEquipos['lapso'] = $equipoLapso;
                if ($equipoPrograma) $filtrosEquipos['programa'] = $equipoPrograma;
                if ($equipoSeccionCodigo) $filtrosEquipos['seccion'] = $equipoSeccionCodigo;
                if (!empty($filtrosEquipos)) {
                    $equipos_disp = $gruposSvc->listar($filtrosEquipos);
                }
            }
        } catch (\Throwable $e) {
            // Silently fail
        }

        // Validated team and its members
        $equipoValidado = null;
        $integrantesEquipo = collect();
        if ($this->equipo_seccion_clave) {
            try {
                $gruposSvc = app(GrupoProyectoService::class);
                $g = $gruposSvc->obtenerPorClave($this->equipo_seccion_clave);
                if ($g) {
                    $equipoValidado = $g;
                    $integrantesEquipo = collect($g->miembros ?? []);
                }
            } catch (\Throwable $e) {
                // Silently fail
            }
        }

        return view('livewire.proyecto-manager', array_merge($datos, [
            'viewMode' => $this->viewMode,
            'editingId' => $this->editingId,
            'archivos_actuales' => $this->archivos_actuales,
            'selectedProject' => $this->selectedProject,
            'esAdmin' => $gestion->usuarioEsAdminEnSistema($user),
            'esLider' => $esLiderGlobal,
            'proyectosLiderIds' => $proyectosLiderIds,
            'proyectosLider' => $proyectosLider,
            'esEstudianteLider' => $esEstudianteLider,
            'modoActualizacion' => $this->modoActualizacion,
            'gruposDocente' => $this->gruposDocente,
            'lapsosFiltro' => $lapsosFiltro,
            'programasFiltro' => $programasFiltro,
            'trayectosFiltro' => $trayectosFiltro,
            'puedeFiltrarGrupos' => $puedeFiltrarGrupos,
            'esGestionador' => $this->esGestionador,
            'esProfesor' => $this->esProfesor,
            // Team filter variables
            'lapsos' => $lapsos,
            'programasEquipo' => $programasEquipo,
            'seccionesEquipo' => $seccionesEquipo,
            'equipos_disp' => $equipos_disp,
            'equipoValidado' => $equipoValidado,
            'integrantesEquipo' => $integrantesEquipo,
        ]));
    }

    protected function resetFormulario(): void
    {
        $this->titulo = '';
        $this->resumen = '';
        $this->linea_investigacion_id = '';
        $this->metodologia_id = '';
        $this->tipo_publicacion_id = '';
        $this->tipo_investigacion_id = '';
        $this->objetivo_investigacion_id = '';
        $this->comunidad_id = '';
        $this->equipo_seccion_clave = '';
        $this->filterLapsoEquipo = '';
        $this->filterProgramaEquipo = '';
        $this->filterSeccionEquipo = '';
        $this->archivosComponente = [];
        $this->archivos_actuales = [];
        $this->editingId = null;
        $this->esGrupoRegistrado = false;
        $this->comunidadNombreGrupo = null;
        $this->showTeamFilters = false;
        $this->programa_id_derived = null;
        $this->trayecto_derived = '';
        $this->filterGruposLapso = '';
        $this->filterGruposPrograma = '';
        $this->filterGruposTrayecto = '';
        $this->buscarInvolucrado = '';
        $this->resultadosInvolucrados = collect();
        $this->involucradosProyecto = [];
        $this->mostrarFormNuevoInvolucrado = false;
        $this->involucradoPendienteId = null;
        $this->nuevoInvolucradoNombre = '';
        $this->nuevoInvolucradoApellido = '';
        $this->nuevoInvolucradoCedula = '';
        $this->buscarRol = '';
        $this->resultadosRoles = collect();
        $this->rolesSeleccionados = [];
        $this->mostrarFormNuevoRol = false;
        $this->nuevoRolNombre = '';
        $this->involucradoEditandoRoles = null;
        $this->editandoRolesInvolucradoId = null;
    }

    protected function estadoFormulario(): array
    {
        return [
            'search' => $this->search,
            'filterEstadoList' => $this->filterEstadoList,
            'filterComunidadList' => $this->filterComunidadList,
            'filterLapsoEquipo' => $this->filterLapsoEquipo,
            'filterProgramaEquipo' => $this->filterProgramaEquipo,
            'filterSeccionEquipo' => $this->filterSeccionEquipo,
            'equipo_seccion_clave' => $this->equipo_seccion_clave,
            'programa_id' => $this->programa_id_derived,
            'trayecto' => $this->trayecto_derived,
            'titulo' => $this->titulo,
            'resumen' => $this->resumen,
            'linea_investigacion_id' => $this->linea_investigacion_id,
            'metodologia_id' => $this->metodologia_id,
        'tipo_publicacion_id' => $this->tipo_publicacion_id,
        'tipo_investigacion_id' => $this->tipo_investigacion_id,
        'objetivo_investigacion_id' => $this->objetivo_investigacion_id,
        'comunidad_id' => $this->comunidad_id,

        ];
    }
}
