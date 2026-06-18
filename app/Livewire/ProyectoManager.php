<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Models\LineaInvestigacion;
use App\Services\GrupoProyectoService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\ProyectoGestionService;
use App\Services\UserRoleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;

#[Lazy]
class ProyectoManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?string $titulo = '';

    public ?string $resumen = '';

    public ?string $fecha_subida = '';

    public ?string $calificacion = '';

    public ?string $fecha_aprobacion = '';

    public ?string $linea_investigacion_id = '';

    public ?string $metodologia_id = '';

    public ?string $tipo_publicacion_id = '';
    public ?string $tipo_investigacion_id = '';
    public ?string $objetivo_investigacion_id = '';
    public ?string $objetivo_id = '';
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

    /** Modal crear línea de investigación */
    public bool $mostrarModalLinea = false;

    public string $modalLineaNombre = '';

    public string $modalLineaDescripcion = '';

    public string $modalLineaArea = '';

    /** Búsqueda de líneas */
    public string $buscarLinea = '';

    /** Resultados de búsqueda */
    public Collection $lineasEncontradas;

    /** Modal crear metodología */
    public bool $mostrarModalMetodologia = false;

    public string $modalMetodologiaNombre = '';

    public string $modalMetodologiaDescripcion = '';

    /** Búsqueda de metodologías */
    public string $buscarMetodologia = '';

    /** Resultados de búsqueda */
    public Collection $metodologiasEncontradas;

    /** Modal crear tipo de investigación */
    public bool $mostrarModalTipoInvestigacion = false;

    public string $modalTipoInvNombre = '';

    public string $modalTipoInvDescripcion = '';

    /** Búsqueda de tipos de investigación */
    public string $buscarTipoInvestigacion = '';

    /** Resultados de búsqueda */
    public Collection $tiposInvestigacionEncontradas;

    /** Mapeo de MIME types a extensiones para la regla 'mimes' de Laravel */
    private const MIME_TO_EXT = [
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/vnd.rar' => 'rar',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    /** Modal crear tipo de publicación */
    public bool $mostrarModalTipoPublicacion = false;

    public string $modalTipoPubNombre = '';

    public bool $modalTipoPubMencionHonorifica = false;

    /** Búsqueda de tipos de publicación */
    public string $buscarTipoPublicacion = '';

    /** Resultados de búsqueda */
    public Collection $tiposPublicacionEncontradas;

    /** Modal crear objetivo de investigación */
    public bool $mostrarModalObjetivo = false;
    public string $modalObjetivoNombre = '';
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

        $this->cargarGruposDocente($gestion);

        $this->lineasEncontradas = collect();
        $this->metodologiasEncontradas = collect();
        $this->tiposInvestigacionEncontradas = collect();
        $this->tiposPublicacionEncontradas = collect();
        $this->objetivosEncontrados = collect();
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
            $this->dispatch('notify', type: 'error', message: 'No se pudo registrar el proyecto. Grupo no encontrado.');
            return;
        }

        $this->dispatch('notify', type: 'success', message: 'Proyecto registrado exitosamente. Complete los datos del proyecto.');
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
            'titulo.required' => 'El titulo del proyecto es obligatorio.',
            'titulo.min' => 'El titulo debe tener al menos 5 caracteres.',
            'resumen.required' => 'El resumen es obligatorio.',
            'resumen.min' => 'El resumen debe tener al menos 10 caracteres.',
            'fecha_subida.required' => 'La fecha de subida es obligatoria.',
            'calificacion.required' => 'La calificacion es obligatoria.',
            'calificacion.integer' => 'La calificacion debe ser un numero entero.',
            'calificacion.min' => 'La calificacion minima es 1.',
            'calificacion.max' => 'La calificacion maxima es 20.',
            'fecha_aprobacion.required' => 'La fecha de aprobacion es obligatoria.',

            'lapso_academico_id.required' => 'Debe seleccionar un lapso academico.',
            'equipo_seccion_clave.required' => 'Debe validar el equipo (seccion intranet).',
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
                            ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
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
                ->leftJoin('trayecto as tra', 'tra.tra_codigo', '=', 'mal.mal_cod_trayecto')
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
        $this->modalTipoPubMencionHonorifica = false;
        $this->buscarTipoPublicacion = '';
        $this->tiposPublicacionEncontradas = collect();
    }

    public function abrirModalObjetivo(): void
    {
        $this->mostrarModalObjetivo = true;
        $this->modalObjetivoNombre = '';
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
        $docMessages = [];
        if (!empty($this->archivosComponente)) {
            $componentes = \App\Models\Componente::whereIn('id', array_keys($this->archivosComponente))->get()->keyBy('id');
            foreach ($this->archivosComponente as $compId => $file) {
                if (!$file) continue;
                $comp = $componentes->get((int) $compId);
                $compNombre = $comp ? $comp->nombre : "Componente #{$compId}";
                $maxKb = $comp ? ($comp->tamano_maximo_mb ?? 10) * 1024 : 10240;
                $maxMb = $comp ? ($comp->tamano_maximo_mb ?? 10) : 10;

                // Usar el método getMimeTypesAttribute() del modelo Componente
                // que ya mapea correctamente todos los tipos (pdf, zip, rar, doc, docx, xls, xlsx, img)
                $rule = 'nullable|file|max:' . $maxKb;

                if ($comp && $comp->tipo_archivo) {
                    $mimesFromAttr = $comp->mime_types; // usa getMimeTypesAttribute()
                    if (!empty($mimesFromAttr)) {
                        $extList = [];
                        foreach (explode(',', $mimesFromAttr) as $mime) {
                            $mime = trim($mime);
                            if (isset(self::MIME_TO_EXT[$mime])) {
                                $extList[] = self::MIME_TO_EXT[$mime];
                            }
                        }
                        if (!empty($extList)) {
                            $rule .= '|mimes:' . implode(',', array_unique($extList));
                        }
                    }
                }

                $docRules['archivosComponente.' . $compId] = $rule;
                $docMessages['archivosComponente.' . $compId . '.mimes'] = "El archivo para «{$compNombre}» debe ser un formato válido ({$comp->tipo_archivo}).";
                $docMessages['archivosComponente.' . $compId . '.max'] = "El archivo para «{$compNombre}» no debe superar los {$maxMb} MB.";
                $docMessages['archivosComponente.' . $compId . '.file'] = "El archivo para «{$compNombre}» debe ser un archivo válido.";
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
                    $this->dispatch('notify', type: 'error', message: 'No puedes subir documentos porque ya no estás inscrito vigentemente en la sección/lapso de este proyecto. Contacta al coordinador o profesor.');
                    return;
                }
            }
        }

        // Siempre validar campos del formulario (título, resumen, fecha, clasificación)
        // para que SUBIR = GUARDAR sin perder datos
        $rules = $gestion->reglasValidacion($estado, $user, $this->editingId !== null);
        // Si hay archivos subidos, validarlos también
        // Combinar mensajes personalizados de documentos con los del formulario
        $allMessages = array_merge($this->messages(), $docMessages);
        if (!empty(array_filter($this->archivosComponente))) {
            $rules = array_merge($rules, $docRules);
        }
        $this->validate($rules, $allMessages);

        $proyecto = $gestion->guardar(
            $this->editingId,
            $estado,
            $user,
            $this->archivosComponente,
            $this->esGrupoRegistrado ? $this->selectedLeaders : [],
        );

        // Si el líder actualizó, marcar como completado (listo para revisión)
        if ($this->modoActualizacion && $proyecto) {
            $proyecto->update([
                'actualizado_por_estudiante' => true,
                'fecha_actualizacion_estudiante' => now(),
                'estado_validacion' => 'completado',
                'estado_logico' => true,
            ]);
        }

        $this->viewMode = 'list';
        $this->dispatch('notify', type: 'success', message: $this->modoActualizacion ? 'Documentos subidos con éxito. El profesor será notificado.' : ($this->editingId ? 'Proyecto actualizado con éxito.' : 'Proyecto registrado con éxito.'));
        $this->resetFormulario();
        $this->cargarGruposDocente(app(ProyectoGestionService::class));
        $this->dispatch('refresh-icons');
    }

     /**
      * Cierra el formulario del profesor, guarda los datos y los documentos si se subieron,
      * y notifica a los líderes para que completen los documentos faltantes.
      */
     public function cerrarFormulario(ProyectoGestionService $gestion): void
     {
         $docRules = [];
         $docMessages = [];
         if (!empty($this->archivosComponente)) {
             $componentes = \App\Models\Componente::whereIn('id', array_keys($this->archivosComponente))->get()->keyBy('id');
             foreach ($this->archivosComponente as $compId => $file) {
                 if (!$file) continue;
                 $comp = $componentes->get((int) $compId);
                 $compNombre = $comp ? $comp->nombre : "Componente #{$compId}";
                 $maxKb = $comp ? ($comp->tamano_maximo_mb ?? 10) * 1024 : 10240;
                 $maxMb = $comp ? ($comp->tamano_maximo_mb ?? 10) : 10;
                 $rule = 'nullable|file|max:' . $maxKb;
                 if ($comp && $comp->tipo_archivo) {
                     $mimesFromAttr = $comp->mime_types;
                     if (!empty($mimesFromAttr)) {
                         $extList = [];
                         foreach (explode(',', $mimesFromAttr) as $mime) {
                             $mime = trim($mime);
                             if (isset(self::MIME_TO_EXT[$mime])) {
                                 $extList[] = self::MIME_TO_EXT[$mime];
                             }
                         }
                         if (!empty($extList)) {
                             $rule .= '|mimes:' . implode(',', array_unique($extList));
                         }
                     }
                 }
                 $docRules['archivosComponente.' . $compId] = $rule;
                 $docMessages['archivosComponente.' . $compId . '.mimes'] = "El archivo para «{$compNombre}» debe ser un formato válido ({$comp->tipo_archivo}).";
                 $docMessages['archivosComponente.' . $compId . '.max'] = "El archivo para «{$compNombre}» no debe superar los {$maxMb} MB.";
                 $docMessages['archivosComponente.' . $compId . '.file'] = "El archivo para «{$compNombre}» debe ser un archivo válido.";
             }
             $this->validate($docRules, $docMessages);
         }

         $gestion->guardar(
             $this->editingId,
             $this->estadoFormulario(),
             auth()->user(),
             $this->archivosComponente,
             $this->esGrupoRegistrado ? $this->selectedLeaders : [],
         );
 
         $this->dispatch('notify', type: 'success', message: 'Formulario guardado con éxito. Se han guardado los datos y documentos del proyecto.');
         $this->irAListado();
     }

    public function toggleStatus(int $id, ProyectoGestionService $gestion): void
    {
        $gestion->alternarEstado($id);
        $this->dispatch('notify', type: 'success', message: 'Estado del proyecto actualizado.');
        $this->dispatch('refresh-icons');
    }

    public function delete(int $id, ProyectoGestionService $gestion): void
    {
        $gestion->eliminar($id);
        $this->dispatch('notify', type: 'success', message: 'Proyecto eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function approve(int $id, ProyectoGestionService $gestion): void
    {
        try {
            $gestion->aprobar($id);
            $this->dispatch('notify', type: 'success', message: 'Proyecto aprobado con éxito.');
        } catch (AuthorizationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
        $this->dispatch('refresh-icons');
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
        $this->dispatch('refresh-icons');
    }

    public function confirmReject(ProyectoGestionService $gestion): void
    {
        $this->validate([
            'motivo_rechazo' => 'required|min:10',
        ], $this->messages());

        try {
            $gestion->rechazar((int) $this->selectedProjectId, $this->motivo_rechazo);
            $this->irAListado();
            $this->dispatch('notify', type: 'success', message: 'Proyecto rechazado.');
        } catch (AuthorizationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
        $this->dispatch('refresh-icons');
    }

    public function approveFromDetails(int $id, ProyectoGestionService $gestion): void
    {
        try {
            $gestion->aprobar($id);
            $this->irAListado();
            $this->dispatch('notify', type: 'success', message: 'Proyecto aprobado con éxito.');
        } catch (AuthorizationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
        $this->dispatch('refresh-icons');
    }

    public function rejectFromDetails(int $id): void
    {
        $this->openReject($id);
    }

    /**
     * Emite la solvencia para un proyecto aprobado.
     */
    public function emitirSolvencia(int $id)
    {
        $user = auth()->user();
        if (!$user) return;

        $gestion = app(ProyectoGestionService::class);
        if (!$gestion->usuarioPuedeValidar($user)) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permisos para emitir solvencias.');
            return;
        }

        $proyecto = \App\Models\Proyecto::find($id);
        if (!$proyecto) {
            $this->dispatch('notify', type: 'error', message: 'Proyecto no encontrado.');
            return;
        }
        if ($proyecto->estado_validacion === 'solventado') {
            $this->dispatch('notify', type: 'warning', message: 'La solvencia ya fue emitida anteriormente para este proyecto.');
            // Si ya existe, redirigir a la descarga
            $solvenciaExistente = \App\Models\Solvencia::where('pry_codigo', $id)->first();
            if ($solvenciaExistente) {
                $this->redirect(route('solvencias.download', $solvenciaExistente->id));
            }
            return;
        }
        if ($proyecto->estado_validacion !== 'aprobado') {
            $this->dispatch('notify', type: 'error', message: 'El proyecto debe estar aprobado para emitir la solvencia.');
            return;
        }

        try {
            $service = app(\App\Services\SolvenciaService::class);
            $result = $service->emitirSolvencia($proyecto, $user);
            $this->dispatch('notify', type: 'success', message: 'Solvencia emitida con éxito: ' . $result['solvencia']->sol_numero);
            // Redirigir a la descarga del PDF
            $this->redirect(route('solvencias.download', $result['solvencia']->id));
            return;
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al emitir solvencia: ' . $e->getMessage());
        }
        $this->dispatch('refresh-icons');
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

        // Detectar si es estudiante líder (no admin, no coord, no prof)
        $esEstudianteLider = false;
        $proyectosLiderIds = [];
        $proyectosLider = collect();
        $proyectosMiembro = collect();
        if ($user && !$this->esProfesor) {
            $userRoleService = app(UserRoleService::class);
            $activeRole = $userRoleService->getActiveRole($user);
            $esAdminOCoordOGestionador = $userRoleService->roleMatches('administrador', $activeRole)
                || $userRoleService->roleMatches('coordinador', $activeRole)
                || $userRoleService->roleMatches('gestionador', $activeRole);
            if (!$esAdminOCoordOGestionador) {
                $esEstudianteLider = true;
                $proyectosLiderIds = $gestion->proyectosDondeEsLider($user);
                $proyectosLider = $gestion->proyectosLider($user);
                // También buscar proyectos donde es miembro (no líder)
                $proyectosMiembro = $gestion->proyectosDondeEsMiembro($user, $proyectosLiderIds);
            }
        }

        $datos = match (true) {
            $this->viewMode === 'list' && !$this->esProfesor && !$esEstudianteLider => $gestion->datosVistaListado([
                'search' => $this->search,
                'estado' => $this->filterEstadoList,
                'comunidad' => $this->filterComunidadList,
                'lapso' => $this->filterGruposLapso,
            ], $page, $user),
            $this->viewMode === 'list' && ($this->esProfesor || $esEstudianteLider) => ['comunidades' => $gestion->comunidadesOrdenadas()],
            $this->viewMode === 'form' => $gestion->datosVistaFormulario($estado),
            default => ['comunidades' => $gestion->comunidadesOrdenadas()],
        };

        // Catalogs for group filters
        $equipoSeccion = app(IntranetEquipoSeccionService::class);
        $lapsoFiltro = $this->filterGruposLapso !== '' ? (int) $this->filterGruposLapso : null;
        $programaFiltro = $this->filterGruposPrograma !== '' ? (int) $this->filterGruposPrograma : null;
        $lapsosFiltro = \App\Models\LapsoAcademico::activos()->orderByDesc('lap_codigo')->get();
        $programasFiltro = $lapsoFiltro ? $equipoSeccion->programasEnLapso($lapsoFiltro) : collect();
        $trayectosFiltro = $lapsoFiltro ? $equipoSeccion->trayectosEnLapso($lapsoFiltro, $programaFiltro) : collect();

        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);
        $puedeFiltrarGrupos = true;

        // Cargar solvencias de una vez para evitar N+1
        $todosProyectosIds = collect();
        if ($proyectosLider->isNotEmpty()) {
            $todosProyectosIds = $todosProyectosIds->merge($proyectosLider->pluck('id'));
        }
        if ($proyectosMiembro->isNotEmpty()) {
            $todosProyectosIds = $todosProyectosIds->merge($proyectosMiembro->pluck('id'));
        }
        $solvenciasMap = collect();
        if ($todosProyectosIds->isNotEmpty()) {
            $solvenciasMap = \App\Models\Solvencia::whereIn('pry_codigo', $todosProyectosIds->unique()->values()->all())
                ->get()
                ->keyBy('pry_codigo');
        }

        return view('livewire.proyecto-manager', array_merge($datos, [
            'viewMode' => $this->viewMode,
            'editingId' => $this->editingId,
            'filterLapsoEquipo' => $this->filterLapsoEquipo,
            'archivos_actuales' => $this->archivos_actuales,
            'selectedProject' => $this->selectedProject,
            'esAdmin' => $gestion->usuarioEsAdminEnSistema($user),
            'esLider' => $esLiderGlobal,
            'proyectosLiderIds' => $proyectosLiderIds,
            'proyectosLider' => $proyectosLider,
            'esEstudianteLider' => $esEstudianteLider,
            'proyectosMiembro' => $proyectosMiembro,
            'modoActualizacion' => $this->modoActualizacion,
            'gruposDocente' => $this->gruposDocente,
            'lapsosFiltro' => $lapsosFiltro,
            'programasFiltro' => $programasFiltro,
            'trayectosFiltro' => $trayectosFiltro,
            'puedeFiltrarGrupos' => $puedeFiltrarGrupos,
            'esGestionador' => $this->esGestionador,
            'esProfesor' => $this->esProfesor,
            'solvenciasMap' => $solvenciasMap,
        ]));
    }

    protected function resetFormulario(): void
    {
        $this->titulo = '';
        $this->resumen = '';
        $this->fecha_subida = '';
        $this->calificacion = '';
        $this->fecha_aprobacion = '';
        $this->linea_investigacion_id = '';
        $this->metodologia_id = '';
        $this->tipo_publicacion_id = '';
        $this->tipo_investigacion_id = '';
        $this->objetivo_investigacion_id = '';
        $this->objetivo_id = '';
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
            'fecha_subida' => $this->fecha_subida,
            'calificacion' => $this->calificacion,
            'fecha_aprobacion' => $this->fecha_aprobacion,
            'linea_investigacion_id' => $this->linea_investigacion_id,
            'metodologia_id' => $this->metodologia_id,
            'tipo_publicacion_id' => $this->tipo_publicacion_id,
            'tipo_investigacion_id' => $this->tipo_investigacion_id,
            'objetivo_investigacion_id' => $this->objetivo_investigacion_id,
            'objetivo_id' => $this->objetivo_id,
            'comunidad_id' => $this->comunidad_id,
        ];
    }
}
