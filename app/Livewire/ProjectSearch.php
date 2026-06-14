<?php

namespace App\Livewire;

use App\Services\ProyectoBusquedaService;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectSearch extends Component
{
    use WithPagination;

    public string $search = '';

    public string $lapsoFilter = '';

    public string $programaFilter = '';

    public string $trayectoFilter = '';

    public string $seccionFilter = '';

    public string $comunidadFilter = '';

    public string $lineaFilter = '';

    public string $tipoPublicacionFilter = '';

    public string $tipoInvestigacionFilter = '';

    public string $metodologiaFilter = '';

    public ?\App\Models\Proyecto $selectedProject = null;

    public bool $isDetailsModalOpen = false;

    public array $selectedProjects = [];

    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public function mount(ProyectoBusquedaService $busqueda): void
    {
        $lapsos = $busqueda->datosVista([], 1)['lapsos'];
        if ($lapsos->isNotEmpty() && $this->lapsoFilter === '') {
            $this->lapsoFilter = (string) $lapsos->first()->lap_codigo;
        }
    }

    public function toggleProject(int $id): void
    {
        if (in_array($id, $this->selectedProjects)) {
            $this->selectedProjects = array_values(array_diff($this->selectedProjects, [$id]));
        } else {
            $this->selectedProjects[] = $id;
        }
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLapsoFilter(): void
    {
        $this->programaFilter = '';
        $this->trayectoFilter = '';
        $this->seccionFilter = '';
        $this->resetPage();
    }

    public function updatingProgramaFilter(): void
    {
        $this->trayectoFilter = '';
        $this->seccionFilter = '';
        $this->resetPage();
    }

    public function updatingTrayectoFilter(): void
    {
        $this->seccionFilter = '';
        $this->resetPage();
    }

    public function updatingSeccionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingComunidadFilter(): void
    {
        $this->resetPage();
    }

    public function updatingLineaFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTipoPublicacionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTipoInvestigacionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMetodologiaFilter(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(ProyectoBusquedaService $busqueda): void
    {
        $this->search = '';
        $this->programaFilter = '';
        $this->trayectoFilter = '';
        $this->seccionFilter = '';
        $this->comunidadFilter = '';
        $this->lineaFilter = '';
        $this->tipoPublicacionFilter = '';
        $this->tipoInvestigacionFilter = '';
        $this->metodologiaFilter = '';

        $lapsos = $busqueda->datosVista([], 1)['lapsos'];
        $this->lapsoFilter = $lapsos->isNotEmpty() ? (string) $lapsos->first()->lap_codigo : '';
        $this->resetPage();
    }

    public function openDetails(int $id, ProyectoBusquedaService $busqueda): void
    {
        $this->selectedProject = $busqueda->proyectoDetalle($id);
        $this->isDetailsModalOpen = $this->selectedProject !== null;
        $this->dispatch('refresh-icons');
    }

    public function closeDetails(): void
    {
        $this->isDetailsModalOpen = false;
        $this->selectedProject = null;
    }

    public function render(ProyectoBusquedaService $busqueda)
    {
        return view('livewire.project-search', $busqueda->datosVista($this->filtrosBusqueda(), $this->getPage()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function filtrosBusqueda(): array
    {
        return array_filter([
            'search' => $this->search,
            'lapso' => $this->lapsoFilter !== '' ? (int) $this->lapsoFilter : null,
            'programa' => $this->programaFilter !== '' ? (int) $this->programaFilter : null,
            'trayecto' => $this->trayectoFilter !== '' ? (int) $this->trayectoFilter : null,
            'seccion' => $this->seccionFilter !== '' ? (int) $this->seccionFilter : null,
            'comunidad' => $this->comunidadFilter !== '' ? (int) $this->comunidadFilter : null,
            'linea' => $this->lineaFilter !== '' ? (int) $this->lineaFilter : null,
            'tipo_publicacion' => $this->tipoPublicacionFilter !== '' ? (int) $this->tipoPublicacionFilter : null,
            'tipo_investigacion' => $this->tipoInvestigacionFilter !== '' ? (int) $this->tipoInvestigacionFilter : null,
            'metodologia' => $this->metodologiaFilter !== '' ? (int) $this->metodologiaFilter : null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
