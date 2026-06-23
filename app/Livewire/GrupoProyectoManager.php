<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Models\Estado;
use App\Services\ComunidadGestionService;
use App\Services\GrupoProyectoService;
use App\Services\UnicidadNombreService;
use App\Services\ValidacionCorreoService;
use App\Services\ValidacionRifService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\IntranetProfessorService;
use App\Services\UserRoleService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;

#[Lazy]

class GrupoProyectoManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $viewMode = 'list';

    public string $filterLapso = '';

    public string $filterPrograma = '';

    public string $filterSeccion = '';

    public string $filterEquipo = '';

    public Collection $lapsos;

    public Collection $programas;

    public Collection $secciones;

    public Collection $comunidades;
    public string $searchComunidad = '';
    public bool $mostrarDropdownComunidad = false;

    public bool $mostrarModalComunidad = false;

    public string $modalNombre = '';

    public ?string $modalNombreStatus = null;

    public function updatedModalNombre(): void
    {
        if (strlen(trim($this->modalNombre)) < 3) {
            $this->modalNombreStatus = null;
            $this->resetValidation('modalNombre');
            return;
        }
        $this->modalNombreStatus = app(UnicidadNombreService::class)->check(
            Comunidad::class,
            'nombre',
            $this->modalNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalNombreStatus === 'disponible') {
            $this->resetValidation('modalNombre');
        }
    }

    public string $modalRifLetra = 'J';

    public string $modalRifNumero = '';

    public ?string $modalRifDigito = null;

    public ?string $modalRifStatus = null;

    public ?string $modalRifError = null;

    public function updatedModalRifNumero(ValidacionRifService $rifService): void
    {
        $num = preg_replace('/\D/', '', $this->modalRifNumero);
        $this->modalRifNumero = $num;
        if ($num === '') {
            $this->modalRifDigito = null;
            $this->modalRifStatus = null;
            $this->modalRifError = null;
            $this->resetValidation('modalRifNumero');
            return;
        }
        if (strlen($num) < 9) {
            $this->modalRifDigito = null;
            $this->modalRifStatus = 'invalido';
            $this->modalRifError = 'Debe tener 9 dígitos';
            $this->resetValidation('modalRifNumero');
            return;
        }
        $this->modalRifDigito = $rifService->calcularDigito($this->modalRifLetra, $num);
        $this->modalRifStatus = $this->modalRifDigito !== null ? 'valido' : 'invalido';
        $this->modalRifError = $this->modalRifStatus === 'valido' ? null : 'RIF inválido';
        if ($this->modalRifStatus === 'valido') {
            $this->resetValidation('modalRifNumero');
        }
    }

    public function updatedModalRifLetra(ValidacionRifService $rifService): void
    {
        if (strlen($this->modalRifNumero) >= 9) {
            $this->updatedModalRifNumero($rifService);
        }
    }

    public string $modalCorreo = '';

    public ?string $modalCorreoStatus = null;

    public ?string $modalCorreoError = null;

    public function updatedModalCorreo(ValidacionCorreoService $correoService): void
    {
        $correo = trim($this->modalCorreo);
        if ($correo === '' || strlen($correo) < 5) {
            $this->modalCorreoStatus = null;
            $this->modalCorreoError = null;
            $this->resetValidation('modalCorreo');
            return;
        }
        $resultado = $correoService->validarCompleto($correo);
        $this->modalCorreoStatus = $resultado['valido'] ? 'valido' : 'invalido';
        $this->modalCorreoError = $resultado['error'];
        if ($this->modalCorreoStatus === 'valido') {
            $this->resetValidation('modalCorreo');
        }
    }

    public string $modalPrefijoTelefono = '0424';

    public string $modalNumeroTelefono = '';

    public string $modalEstadoId = '';

    public string $modalMunicipioId = '';

    public string $modalDirNombre = '';

    public Collection $modalEstados;

    public Collection $modalMunicipios;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterLapso(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPrograma(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSeccion(): void
    {
        $this->resetPage();
    }

    public ?int $editingGrpCodigo = null;

    public string $nombreGrupo = '';

    public ?string $nombreGrupoStatus = null;

    public function updatedNombreGrupo(): void
    {
        if (strlen(trim($this->nombreGrupo)) < 2) {
            $this->nombreGrupoStatus = null;
            $this->resetValidation('nombreGrupo');
            return;
        }
        $this->nombreGrupoStatus = app(UnicidadNombreService::class)->check(
            \App\Models\GrupoProyectoModulo::class,
            'grp_nombre',
            $this->nombreGrupo,
            $this->editingGrpCodigo,
        ) ? 'disponible' : 'no_disponible';
        if ($this->nombreGrupoStatus === 'disponible') {
            $this->resetValidation('nombreGrupo');
        }
    }

    public string $comunidadId = '';

    public string $selectedCedula = '';

    public string $selectedRolId = '2';

    /** @var list<array{cedula: string, nombre: string, apellido: string, rol_id: int, rol_name: string}> */
    public array $miembrosSeleccionados = [];

    public function mount(): void
    {
        $profesores = app(IntranetProfessorService::class);
        $this->lapsos = $profesores->lapsosActivos();
        $this->comunidades = Cache::remember('grupos_comunidades', 3600, fn () =>
            Comunidad::query()->orderBy('nombre')->get(['com_codigo', 'com_nombre', 'com_rif'])
        );

        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        if ($activeRole === 'profesor proyecto') {
            $this->filterLapso = (string) ($profesores->lapsoVigenteCodigo() ?? '');
        }

        $this->loadProgramas();
        $this->loadSecciones();
    }

    public function updatedSearchComunidad(): void
    {
        $this->mostrarDropdownComunidad = true;
    }

    public function selectComunidad(string $id): void
    {
        $this->comunidadId = $id;
        $this->searchComunidad = $this->comunidades->firstWhere('com_codigo', $id)?->com_nombre ?? '';
        $this->mostrarDropdownComunidad = false;
    }

    #[Computed]
    public function comunidadesFiltradas()
    {
        if ($this->searchComunidad === '') {
            return $this->comunidades;
        }

        $search = strtolower($this->searchComunidad);
        return $this->comunidades->filter(fn($c) => 
            str_contains(strtolower($c->com_nombre), $search) || 
            str_contains(strtolower($c->com_rif ?? ''), $search)
        )->values();
    }


    public function crearGrupo(): void
    {
        $this->resetFormulario();

        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);
        if ($activeRole === 'profesor proyecto') {
            $profesores = app(IntranetProfessorService::class);
            $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : $profesores->lapsoVigenteCodigo();
            if ($lapCodigo) {
                $this->filterLapso = (string) $lapCodigo;
                $this->loadProgramas();

                $proCodigos = $profesores->programasDelDocente(
                    trim((string) $user->usu_cedula),
                    $lapCodigo,
                );
                if (count($proCodigos) === 1) {
                    $this->filterPrograma = (string) $proCodigos[0];
                    $this->loadSecciones();

                    $secCodigos = $profesores->seccionesDelDocente(
                        trim((string) $user->usu_cedula),
                        $lapCodigo,
                    );
                    if ($secCodigos !== []) {
                        $this->secciones = $this->secciones->whereIn('sec_codigo', $secCodigos)->values();
                        if ($this->secciones->count() === 1) {
                            $this->filterSeccion = (string) $this->secciones->first()->sec_codigo;
                        }
                    }
                }
            }
        }

        $this->viewMode = 'form';
    }

    public function editarGrupo(int $grpCodigo): void
    {
        $grupos = app(GrupoProyectoService::class);
        $g = $grupos->obtener($grpCodigo);
        if (!$g) {
            session()->flash('message_error', 'Grupo no encontrado.');
            return;
        }

        $this->editingGrpCodigo = $grpCodigo;
        $this->nombreGrupo = $g->nombre;
        $this->filterLapso = (string) $g->lap_codigo;
        $this->loadProgramas();
        $this->filterPrograma = $g->pro_codigo ? (string) $g->pro_codigo : '';
        $this->loadSecciones();
        $this->filterSeccion = (string) $g->sec_codigo;
        $this->comunidadId = $g->com_codigo ? (string) $g->com_codigo : '';
        
        // Set searchComunidad to the name of the selected community
        $com = $this->comunidades->firstWhere('com_codigo', $this->comunidadId);
        $this->searchComunidad = $com ? $com->com_nombre : '';

        $this->miembrosSeleccionados = array_map(
            fn($m) => [
                'cedula' => $m['cedula'],
                'nombre' => $m['nombre'] ?? '',
                'apellido' => $m['apellido'] ?? '',
                'rol_id' => (int) ($m['rol_id'] ?? 2),
                'rol_name' => match ((int) ($m['rol_id'] ?? 2)) {
                    1 => 'Autor-Líder',
                    2 => 'Autor',
                    default => 'Integrante',
                },
            ],
            $g->miembros,
        );
        $this->viewMode = 'form';
    }

    public function agregarIntegrante(): void
    {
        if ($this->selectedCedula === '') {
            session()->flash('message_error', 'Seleccione un estudiante de la sección.');
            return;
        }

        foreach ($this->miembrosSeleccionados as $m) {
            if ($m['cedula'] === $this->selectedCedula) {
                session()->flash('message_error', 'Ese estudiante ya está en el grupo.');
                return;
            }
        }

        $candidatos = $this->candidatosActuales();
        $est = $candidatos->firstWhere('cedula', $this->selectedCedula);
        if (!$est) {
            session()->flash('message_error', 'El estudiante no está inscrito en ninguna sección del PNF en este lapso.');
            return;
        }

        // Validar que el estudiante no pertenezca ya a otro grupo en el mismo lapso
        if ($this->estudianteEnOtroGrupo($this->selectedCedula)) {
            session()->flash('message_error', 'El estudiante ya pertenece a un grupo en este lapso académico.');
            return;
        }

        $rolId = (int) $this->selectedRolId;
        if ($rolId === 1) {
            $lideresActuales = 0;
            foreach ($this->miembrosSeleccionados as $m) {
                if ((int) $m['rol_id'] === 1) {
                    $lideresActuales++;
                }
            }

        }

        $this->miembrosSeleccionados[] = [
            'cedula' => $this->selectedCedula,
            'nombre' => $est->nombre,
            'apellido' => $est->apellido,
            'rol_id' => $rolId,
            'rol_name' => $rolId === 1 ? 'Autor-Líder' : 'Autor',
        ];
        $this->selectedCedula = '';
    }

    protected function estudianteEnOtroGrupo(string $cedula): bool
    {
        $lapso = $this->filterLapso;
        if (!$lapso) {
            return false;
        }

        try {
            $query = \App\Models\GrupoProyectoModulo::whereRaw(
                "CAST(grp_contexto AS jsonb)->>'lap_codigo' = ?",
                [(string) $lapso]
            )->whereRaw(
                "CAST(grp_miembros AS jsonb) @> ?",
                ['[{"cedula":"' . $cedula . '"}]']
            );

            // Excluir el grupo actual si estamos editando
            if ($this->editingGrpCodigo) {
                $query->where('grp_codigo', '!=', $this->editingGrpCodigo);
            }

            return $query->exists();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error verificando grupo existente: ' . $e->getMessage());
            return false;
        }
    }

    public function quitarIntegrante(string $cedula): void
    {
        $this->miembrosSeleccionados = array_values(array_filter($this->miembrosSeleccionados, fn($m) => $m['cedula'] !== $cedula));
    }

    public function registrarGrupo(): void
    {
        $grupos = app(GrupoProyectoService::class);

        $this->validate(
            [
                'nombreGrupo' => 'required|min:2|max:120',
                'comunidadId' => 'required',
                'filterLapso' => 'required',
                'filterSeccion' => 'required',
            ],
            [
                'nombreGrupo.required' => 'Indique un nombre para el equipo/grupo.',
                'comunidadId.required' => 'Seleccione la comunidad.',
                'filterLapso.required' => 'Seleccione el lapso.',
                'filterSeccion.required' => 'Seleccione la sección del PNF.',
            ],
        );

        if ($this->nombreGrupoStatus === 'no_disponible') {
            session()->flash('message_error', 'Este nombre de grupo ya está en uso.');
            return;
        }

        if (!$grupos->tablaDisponible()) {
            session()->flash('message_error', 'Ejecute la migración grupo_proyecto_modulo en repositorio (solo módulo).');
            return;
        }

        // Validar que todos los miembros seleccionados estén inscritos en alguna sección del PNF
        $candidatos = $this->candidatosActuales();
        foreach ($this->miembrosSeleccionados as $m) {
            $cedula = trim($m['cedula']);
            if (!$candidatos->contains('cedula', $cedula)) {
                session()->flash('message_error', 'El integrante ' . ($m['apellido'] ?: '') . ', ' . ($m['nombre'] ?: '') . ' (' . $cedula . ') no está inscrito en el PNF en este lapso.');
                return;
            }
        }

        $user = auth()->user();
        $clave = $grupos->registrar(
            $this->nombreGrupo,
            (int) $this->filterLapso,
            (int) $this->filterSeccion,
            $this->filterPrograma !== '' ? (int) $this->filterPrograma : null,
            $this->comunidadId !== '' ? (int) $this->comunidadId : null,
            $this->miembrosSeleccionados,
            trim((string) $user->usu_cedula),
            $this->editingGrpCodigo,
            $this->etiquetasContextoFormulario(app(IntranetEquipoSeccionService::class))
        );

        if (!$clave) {
            session()->flash('message_error', 'Debe incluir al menos un integrante y un líder.');
            return;
        }

        $nombresMiembros = collect($this->miembrosSeleccionados)
            ->map(fn($m) => trim($m['nombre'] . ' ' . $m['apellido']))
            ->filter()
            ->implode(', ');

        session()->flash('message', 'Grupo registrado. Clave: ' . $clave . '. Los integrantes (' . $nombresMiembros . ') recibirán notificación al recargar su pantalla.');
        $this->viewMode = 'list';
        $this->resetFormulario();
        $this->restablecerFiltros();
    }

    public function eliminarGrupo(int $grpCodigo): void
    {
        app(GrupoProyectoService::class)->eliminar($grpCodigo);
        session()->flash('message', 'Grupo eliminado.');
    }

    public function volver(): void
    {
        $this->viewMode = 'list';
        $this->resetFormulario();
        $this->restablecerFiltros();
    }

    protected function resetFormulario(): void
    {
        $this->editingGrpCodigo = null;
        $this->nombreGrupo = '';
        $this->nombreGrupoStatus = null;
        $this->comunidadId = '';
        $this->searchComunidad = '';
        $this->mostrarDropdownComunidad = false;
        $this->miembrosSeleccionados = [];
        $this->selectedCedula = '';
        $this->filterLapso = '';
        $this->filterPrograma = '';
        $this->filterSeccion = '';
        $this->loadProgramas();
        $this->loadSecciones();
    }

    public function abrirModalComunidad(): void
    {
        $this->mostrarModalComunidad = true;
        $this->modalNombre = '';
        $this->modalNombreStatus = null;
        $this->modalRifLetra = 'J';
        $this->modalRifNumero = '';
        $this->modalRifDigito = null;
        $this->modalRifStatus = null;
        $this->modalRifError = null;
        $this->modalCorreo = '';
        $this->modalCorreoStatus = null;
        $this->modalCorreoError = null;
        $this->modalPrefijoTelefono = '0424';
        $this->modalNumeroTelefono = '';
        $this->modalEstadoId = '';
        $this->modalMunicipioId = '';
        $this->modalDirNombre = '';
        $this->modalEstados = Estado::orderBy('est_nombre')->get();
        $this->modalMunicipios = collect();
    }

    public function cerrarModalComunidad(): void
    {
        $this->mostrarModalComunidad = false;
    }

    public function updatedModalEstadoId(): void
    {
        $this->modalMunicipioId = '';
        if ($this->modalEstadoId !== '') {
            $this->modalMunicipios = \App\Models\Municipio::where('est_codigo', $this->modalEstadoId)->orderBy('mun_nombre')->get();
        } else {
            $this->modalMunicipios = collect();
        }
    }

    public function guardarComunidadDesdeModal(ComunidadGestionService $gestion): void
    {
        $this->validate([
            'modalNombre' => 'required|string|max:255',
            'modalCorreo' => 'nullable|email|max:150',
            'modalEstadoId' => 'required|integer|exists:estados,est_codigo',
            'modalMunicipioId' => 'required|integer|exists:municipios,mun_codigo',
            'modalDirNombre' => 'required|string|max:500',
        ], [
            'modalNombre.required' => 'El nombre de la comunidad es obligatorio.',
            'modalEstadoId.required' => 'Seleccione un estado.',
            'modalMunicipioId.required' => 'Seleccione un municipio.',
            'modalDirNombre.required' => 'La dirección exacta es obligatoria.',
        ]);

        if ($this->modalNombreStatus === 'no_disponible') {
            $this->addError('modalNombre', 'Este nombre de comunidad ya está en uso.');
            return;
        }

        if ($this->modalRifNumero !== '' && strlen($this->modalRifNumero) < 9) {
            $this->addError('modalRifNumero', 'El RIF debe tener exactamente 9 dígitos.');
            return;
        }

        if ($this->modalRifNumero !== '' && $this->modalRifStatus !== 'valido') {
            $this->addError('modalRifNumero', 'El RIF ingresado no es válido.');
            return;
        }

        if ($this->modalCorreo !== '' && $this->modalCorreoStatus !== 'valido') {
            $this->addError('modalCorreo', $this->modalCorreoError ?? 'El correo ingresado no es válido.');
            return;
        }

        if ($this->modalCorreo !== '') {
            $resultado = app(ValidacionCorreoService::class)->validarCompleto($this->modalCorreo, true);
            if (!$resultado['valido']) {
                $this->addError('modalCorreo', $resultado['error'] ?? 'El dominio del correo no existe.');
                return;
            }
        }

        $payload = [
            'nombre' => $this->modalNombre,
            'correo' => $this->modalCorreo,
            'prefijo_telefono' => $this->modalPrefijoTelefono,
            'numero_telefono' => $this->modalNumeroTelefono,
            'estado_id' => $this->modalEstadoId,
            'municipio_id' => $this->modalMunicipioId,
            'dir_nombre' => $this->modalDirNombre,
        ];

        if ($this->modalRifNumero !== '') {
            $payload['rif'] = "{$this->modalRifLetra}-{$this->modalRifNumero}-{$this->modalRifDigito}";
        }

        $id = $gestion->guardar(null, $payload);

        Cache::forget('grupos_comunidades');
        $this->comunidades = Comunidad::query()->orderBy('nombre')->get(['com_codigo', 'com_nombre']);
        $this->comunidadId = (string) $id;
        $this->cerrarModalComunidad();

        session()->flash('message', 'Comunidad creada correctamente.');
    }

    protected function restablecerFiltros(): void
    {
        $this->reset('filterPrograma', 'filterSeccion');
        $this->loadSecciones();

        $activeRole = app(UserRoleService::class)->getActiveRole(auth()->user());
        if ($activeRole !== 'profesor proyecto') {
            $this->filterLapso = '';
            $this->loadProgramas();
        }
    }

    public function updatedFilterLapso(): void
    {
        $this->filterPrograma = '';
        $this->filterSeccion = '';
        $this->loadProgramas();
    }

    public function updatedFilterPrograma(): void
    {
        $this->filterSeccion = '';
        $this->loadSecciones();
    }

    protected function loadProgramas(): void
    {
        $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : null;
        $this->programas = app(IntranetEquipoSeccionService::class)->programasEnLapso($lapCodigo);
    }

    protected function loadSecciones(): void
    {
        $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : null;
        $programaCodigo = $this->filterPrograma !== '' ? (int) $this->filterPrograma : null;
        $this->secciones = app(IntranetEquipoSeccionService::class)->seccionesEnLapso($lapCodigo, $programaCodigo);
    }

    protected function candidatosActuales()
    {
        if ($this->filterSeccion === '' || $this->filterLapso === '' || $this->secciones->isEmpty()) {
            return collect();
        }

        $grupos = app(GrupoProyectoService::class);
        $lapCodigo = (int) $this->filterLapso;

        return $grupos->candidatosSeccion($lapCodigo, (int) $this->filterSeccion);
    }

    /**
     * @return array{lap_nombre: string, sec_nombre: string, pro_siglas: string, pro_nombre: string}
     */
    protected function etiquetasContextoFormulario(IntranetEquipoSeccionService $equipos): array
    {
        if ($this->filterLapso === '' || $this->filterSeccion === '') {
            return ['lap_nombre' => '', 'sec_nombre' => '', 'pro_siglas' => '', 'pro_nombre' => ''];
        }

        return $equipos->etiquetasContexto((int) $this->filterLapso, (int) $this->filterSeccion, $this->filterPrograma !== '' ? (int) $this->filterPrograma : null);
    }

    public function with()
    {
        $grupos = app(GrupoProyectoService::class);
        $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : null;
        $programaCodigo = $this->filterPrograma !== '' ? (int) $this->filterPrograma : null;
        $seccionCodigo = $this->filterSeccion !== '' ? (int) $this->filterSeccion : null;

        $user = auth()->user();
        $activeRole = app(UserRoleService::class)->getActiveRole($user);

        $filters = [
            'lapso' => $lapCodigo,
            'programa' => $programaCodigo,
            'busqueda' => $this->search,
        ];

        if ($activeRole === 'profesor proyecto' && $this->viewMode === 'list') {
            $profesorService = app(IntranetProfessorService::class);
            $secCodigos = $profesorService->seccionesDelDocente(
                trim((string) $user->usu_cedula),
                $lapCodigo,
            );
            if ($secCodigos === []) {
                $filters['seccion'] = [-1];
            } else {
                $filters['seccion'] = $secCodigos;
                $this->secciones = $this->secciones->whereIn('sec_codigo', $secCodigos)->values();
            }
        } elseif ($seccionCodigo) {
            $filters['seccion'] = $seccionCodigo;
        }

        $tablaOk = $grupos->tablaDisponible();
        $lista = collect();
        if ($tablaOk) {
            try {
                $lista = $grupos->listar($filters);
            } catch (\Throwable $e) {
                session()->flash('message_error', 'Error: ' . $e->getMessage());
                $lista = collect();
            }
        }

        $perPage = 10;
        $page = $this->getPage();
        $items = $lista->slice(($page - 1) * $perPage, $perPage)->values();
        $paginados = new \Illuminate\Pagination\LengthAwarePaginator($items, $lista->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);

        return [
            'gruposList' => $paginados,
            'lapsos' => $this->lapsos,
            'programas' => $this->programas,
            'secciones' => $this->secciones,
            'candidatos' => $this->viewMode === 'form' ? $this->candidatosActuales() : collect(),
            'comunidades' => $this->comunidades,
            'tablaLista' => $tablaOk,
            'isProfessor' => $activeRole === 'profesor proyecto',
        ];
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div style="padding: 20px; margin: 10px 0;">
            <style>
                @keyframes grpPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.85; } }
                @keyframes grpShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
            </style>
            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Cargando equipos de proyecto...</legend>
                <div style="animation: grpPulse 1.5s ease-in-out infinite;">
                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div style="height: 32px; width: 160px; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: grpShimmer 1.5s infinite; border-radius: 3px;"></div>
                        <div style="height: 32px; width: 160px; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: grpShimmer 1.5s infinite; border-radius: 3px;"></div>
                        <div style="height: 32px; width: 160px; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: grpShimmer 1.5s infinite; border-radius: 3px;"></div>
                    </div>
                    <div style="height: 18px; width: 100%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: grpShimmer 1.5s infinite; border-radius: 3px; margin-bottom: 8px;"></div>
                    <div style="height: 18px; width: 100%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: grpShimmer 1.5s infinite; border-radius: 3px; margin-bottom: 8px;"></div>
                    <div style="height: 18px; width: 90%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: grpShimmer 1.5s infinite; border-radius: 3px; margin-bottom: 8px;"></div>
                    <div style="height: 18px; width: 75%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: grpShimmer 1.5s infinite; border-radius: 3px;"></div>
                </div>
                <div style="text-align: center; margin-top: 15px; font-size: 11px; color: #888;">
                    Consultando secciones y grupos de proyecto...
                </div>
            </fieldset>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.grupo-proyecto-manager', $this->with());
    }
}