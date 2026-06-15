<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Services\GrupoProyectoService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\ProyectoGestionService;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;

#[Lazy]
class ProyectoManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?string $listTab = 'gestion';

    public ?string $titulo = '';

    public ?string $resumen = '';

    public ?string $fecha_subida = '';

    public ?string $calificacion = '';

    public ?string $fecha_aprobacion = '';

    public ?string $linea_investigacion_id = '';

    public ?string $metodologia_id = '';

    public ?string $tipo_publicacion_id = '';

    public ?string $tipo_investigacion_id = '';

    public ?string $comunidad_id = '';

    public ?string $equipo_seccion_clave = '';

    public ?string $filterLapsoEquipo = '';

    public ?string $filterProgramaEquipo = '';

    public ?string $filterSeccionEquipo = '';

    public ?string $filterEstadoList = '';

    public ?string $filterComunidadList = '';

    public $archivosComponente = [];

    public array $archivos_actuales = [];

    public bool $showTeamFilters = false;

    public bool $showAdvanced = false;

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

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-6 w-full bg-white rounded-lg shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800">
            <div class="animate-pulse space-y-6">
                <div class="flex justify-between items-center font-semibold">
                    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/3"></div>
                    <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded w-40"></div>
                </div>
                <div class="grid grid-cols-4 gap-4">
                    <div class="h-8 bg-slate-100 dark:bg-slate-800 rounded col-span-1"></div>
                    <div class="h-8 bg-slate-100 dark:bg-slate-800 rounded col-span-1"></div>
                    <div class="h-8 bg-slate-100 dark:bg-slate-800 rounded col-span-2"></div>
                </div>
                <div class="space-y-3">
                    <div class="h-20 bg-slate-50 dark:bg-slate-800/50 rounded w-full"></div>
                    <div class="h-20 bg-slate-50 dark:bg-slate-800/50 rounded w-full"></div>
                    <div class="h-20 bg-slate-50 dark:bg-slate-800/50 rounded w-full"></div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function mount(ProyectoGestionService $gestion): void
    {
        $tab = request()->query('tab', 'gestion');
        if (in_array($tab, ['gestion', 'validar'], true)) {
            $this->listTab = $tab;
        }

        if (request()->boolean('registrar')) {
            $this->listTab = 'gestion';
            $this->iniciarRegistro();
        }

        if ($editId = request()->query('edit')) {
            $this->listTab = 'gestion';
            $this->edit((int) $editId, $gestion, app(GrupoProyectoService::class));
        }

        if ($detailsId = request()->query('details')) {
            $this->listTab = 'validar';
            $this->openDetails((int) $detailsId, $gestion);
        }
    }

    public function iniciarRegistro(): void
    {
        $gestion = app(ProyectoGestionService::class);
        $user = auth()->user();

        // Eliminamos la validación inicial para que siempre abra el formulario al hacer clic.
        // Las validaciones de registro se realizarán al intentar guardar el formulario.

        $this->listTab = 'gestion';
        $this->resetFormulario();
        $this->fecha_subida = now()->format('Y-m-d');

        $this->viewMode = 'form';
        $this->aplicarSyncEquipo($gestion);
    }

    public function irAListado(string $tab = 'gestion'): void
    {
        $this->listTab = in_array($tab, ['gestion', 'validar'], true) ? $tab : 'gestion';
        $this->viewMode = 'list';
        $this->selectedProject = null;
        $this->selectedProjectId = null;
        $this->motivo_rechazo = '';
        $this->resetPage();
    }

    public function toggleTeamFilters(): void
    {
        $this->showTeamFilters = ! $this->showTeamFilters;
    }

    public function toggleAdvanced(): void
    {
        $this->showAdvanced = ! $this->showAdvanced;
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

    /** @deprecated use iniciarRegistro() desde la vista */
    public function create(): void
    {
        $this->iniciarRegistro();
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
            } else {
                $this->titulo = 'Sección #' . $partes['sec_codigo'];
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

    public function toggleLider(string $cedula): void
    {
        $idx = array_search($cedula, $this->selectedLeaders, true);
        if ($idx !== false) {
            unset($this->selectedLeaders[$idx]);
            $this->selectedLeaders = array_values($this->selectedLeaders);
        } else {
            if (count($this->selectedLeaders) >= 2) {
                $this->dispatch('notify', type: 'error', message: 'Solo puede seleccionar hasta 2 líderes por grupo.');
                return;
            }
            $this->selectedLeaders[] = $cedula;
        }
    }

    public function updatingFilterEstadoList(): void
    {
        $this->resetPage();
    }

    public function updatingFilterComunidadList(): void
    {
        $this->resetPage();
    }

    protected function aplicarSyncEquipo(ProyectoGestionService $gestion): void
    {
        if (! auth()->check() || app(ProyectoGestionService::class)->usuarioEsAdminEnSistema(auth()->user())) {
            return;
        }

        $lap = $this->filterLapsoEquipo !== '' ? (int) $this->filterLapsoEquipo : null;
        $sync = $gestion->sincronizarEquipoEstudiante(trim((string) auth()->user()->usu_cedula), $lap);

        if ($sync['equipo_seccion_clave']) {
            $this->equipo_seccion_clave = $sync['equipo_seccion_clave'];
        }
    }

    public function edit(int $id, ProyectoGestionService $gestion, GrupoProyectoService $grupos): void
    {
        $this->resetFormulario();
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
                    // Si no tiene comunidad_id asignado, auto-rellenar
                    if (empty($this->comunidad_id)) {
                        $this->comunidad_id = (string) $grupo->com_codigo;
                    }
                }
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

        if ($this->modoActualizacion) {
            $this->validate([
                'archivosComponente.*' => 'nullable|file|max:20480|mimes:pdf',
            ]);
        } else {
            $this->validate(
                $gestion->reglasValidacion($estado, $user, $this->editingId !== null),
                $this->messages()
            );

            // Validar que se seleccione al menos 1 líder para grupos registrados
            if ($this->esGrupoRegistrado && empty($this->selectedLeaders)) {
                $this->dispatch('notify', type: 'error', message: 'Debe seleccionar al menos un líder para el grupo.');
                return;
            }
        }

        $proyecto = $gestion->guardar(
            $this->editingId,
            $estado,
            $user,
            $this->archivosComponente,
            $this->esGrupoRegistrado ? $this->selectedLeaders : [],
        );

        // Si lider actualizo, marcar y devolver a pendiente de validacion
        if ($this->modoActualizacion && $proyecto) {
            $proyecto->update([
                'actualizado_por_estudiante' => true,
                'fecha_actualizacion_estudiante' => now(),
                'estado_validacion' => 'pendiente',
                'estado_logico' => false,
            ]);
        }

        $this->viewMode = 'list';
        $this->dispatch('notify', type: 'success', message: $this->modoActualizacion ? 'Documentos subidos con éxito. El profesor será notificado.' : ($this->editingId ? 'Proyecto actualizado con éxito.' : 'Proyecto registrado con éxito.'));
        $this->resetFormulario();
        $this->dispatch('refresh-icons');
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
            $this->irAListado($this->listTab);
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
            $this->irAListado($this->listTab);
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

    protected function usuarioEsLider(ProyectoGestionService $gestion): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        $userRoleService = app(\App\Services\UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);
        if ($userRoleService->roleMatches('administrador', $activeRole)
            || $userRoleService->roleMatches('coordinador', $activeRole)) return false;
        return $gestion->usuarioPuedeRegistrar($user);
    }

    public function render(ProyectoGestionService $gestion)
    {
        $estado = $this->estadoFormulario();
        $page = $this->getPage();
        $user = auth()->user();

        $esLiderGlobal = $this->usuarioEsLider($gestion);

        $datos = match ($this->viewMode) {
            'list' => $gestion->datosVistaListado([
                'search' => $this->search,
                'estado' => $this->filterEstadoList,
                'comunidad' => $this->filterComunidadList,
            ], $page, $user, $this->listTab),
            'form' => $gestion->datosVistaFormulario($estado),
            default => ['comunidades' => $gestion->comunidadesOrdenadas()],
        };

        return view('livewire.proyecto-manager', array_merge($datos, [
            'viewMode' => $this->viewMode,
            'listTab' => $this->listTab,
            'editingId' => $this->editingId,
            'filterLapsoEquipo' => $this->filterLapsoEquipo,
            'archivos_actuales' => $this->archivos_actuales,
            'selectedProject' => $this->selectedProject,
            'canRegister' => $gestion->usuarioPuedeRegistrar($user),
            'esAdmin' => $gestion->usuarioEsAdminEnSistema($user),
            'esLider' => $esLiderGlobal,
            'modoActualizacion' => $this->modoActualizacion,
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
        $this->showAdvanced = false;
        $this->programa_id_derived = null;
        $this->trayecto_derived = '';
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
            'comunidad_id' => $this->comunidad_id,
        ];
    }
}
