<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Services\GrupoProyectoService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\IntranetProfessorService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

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

    public string $comunidadId = '';

    public string $selectedCedula = '';

    public string $selectedRolId = '2';

    /** @var list<array{cedula: string, nombre: string, apellido: string, rol_id: int, rol_name: string}> */
    public array $miembrosSeleccionados = [];

    public function mount(): void
    {
        $profesores = app(IntranetProfessorService::class);
        $this->lapsos = $profesores->lapsosActivos();
        $this->comunidades = Comunidad::query()->orderBy('nombre')->get();

        $this->loadProgramas();
        $this->loadSecciones();
    }

    public function crearGrupo(): void
    {
        $this->resetFormulario();
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
        $this->filterPrograma = $g->pro_codigo ? (string) $g->pro_codigo : '';
        $this->filterSeccion = (string) $g->sec_codigo;
        $this->comunidadId = $g->com_codigo ? (string) $g->com_codigo : '';
        $this->miembrosSeleccionados = array_map(
            fn($m) => [
                'cedula' => $m['cedula'],
                'nombre' => $m['nombre'] ?? '',
                'apellido' => $m['apellido'] ?? '',
                'rol_id' => (int) ($m['rol_id'] ?? 2),
                'rol_name' => match ((int) ($m['rol_id'] ?? 2)) {
                    1 => 'Líder',
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
            session()->flash('message_error', 'El estudiante no está inscrito en la sección elegida.');
            return;
        }

        $rolId = (int) $this->selectedRolId;
        if ($rolId === 1) {
            foreach ($this->miembrosSeleccionados as $m) {
                if ((int) $m['rol_id'] === 1) {
                    session()->flash('message_error', 'Solo puede haber un líder en el grupo.');
                    return;
                }
            }
        }

        $this->miembrosSeleccionados[] = [
            'cedula' => $this->selectedCedula,
            'nombre' => $est->nombre,
            'apellido' => $est->apellido,
            'rol_id' => $rolId,
            'rol_name' => $rolId === 1 ? 'Líder' : 'Autor',
        ];
        $this->selectedCedula = '';
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
                'filterLapso' => 'required',
                'filterSeccion' => 'required',
            ],
            [
                'nombreGrupo.required' => 'Indique un nombre para el equipo/grupo.',
                'filterLapso.required' => 'Seleccione el lapso.',
                'filterSeccion.required' => 'Seleccione la sección del PNF.',
            ],
        );

        if (!$grupos->tablaDisponible()) {
            session()->flash('message_error', 'Ejecute la migración grupo_proyecto_modulo en repositorio (solo módulo).');
            return;
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

        session()->flash('message', 'Grupo registrado. Clave: ' . $clave);
        $this->viewMode = 'list';
        $this->resetFormulario();
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
    }

    protected function resetFormulario(): void
    {
        $this->editingGrpCodigo = null;
        $this->nombreGrupo = '';
        $this->comunidadId = '';
        $this->miembrosSeleccionados = [];
        $this->selectedCedula = '';
    }

    public function updatedFilterLapso(): void
    {
        $this->filterPrograma = '';
        $this->filterSeccion = '';
        $this->loadProgramas();
        $this->loadSecciones();
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
        if ($this->filterLapso === '' || $this->filterSeccion === '') {
            return collect();
        }

        return app(GrupoProyectoService::class)->candidatosSeccion((int) $this->filterLapso, (int) $this->filterSeccion);
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

        $tablaOk = $grupos->tablaDisponible();
        $lista = collect();
        if ($tablaOk) {
            try {
                $lista = $grupos->listar([
                    'lapso' => $lapCodigo,
                    'programa' => $programaCodigo,
                    'seccion' => $seccionCodigo,
                    'busqueda' => $this->search,
                ]);
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
        ];
    }

    public function render()
    {
        return view('livewire.grupo-proyecto-manager', $this->with());
    }
}
