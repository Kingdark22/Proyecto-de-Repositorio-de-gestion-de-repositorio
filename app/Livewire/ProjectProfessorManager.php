<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Services\IntranetProfessorService;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
class ProjectProfessorManager extends Component
{
    use WithPagination;
    use WithSafeNotify;

    public string $search = '';

    public string $lapsoFilter = '';

    public string $programaFilter = '';

    public string $trayectoFilter = '';

    public string $seccionFilter = '';

    public array $selectedYear = [];

    public array $selectedSection = [];

    public array $selectedPrograma = [];

    public function mount(IntranetProfessorService $professorService): void
    {
        try {
            $lapsos = $professorService->lapsosActivos();
            if ($lapsos->isNotEmpty() && $this->lapsoFilter === '') {
                $this->lapsoFilter = (string) $lapsos->first()->lap_codigo;
            }

            $this->fill($professorService->cargarSeleccionesFormulario());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en ProjectProfessorManager::mount: ' . $e->getMessage());
        }
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
        $this->search = '';
        $this->resetPage();
    }

    public function updatingProgramaFilter(): void
    {
        $this->trayectoFilter = '';
        $this->seccionFilter = '';
        $this->search = '';
        $this->resetPage();
    }

    public function updatingTrayectoFilter(): void
    {
        $this->seccionFilter = '';
        $this->search = '';
        $this->resetPage();
    }

    public function updatingSeccionFilter(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function toggleProjectProfessor(string $cedula, IntranetProfessorService $professorService): void
    {
        $cedula = trim($cedula);

        $filtros = $this->filtrosIntranet();
        // Per-professor PNF overrides global filter
        $prog = $this->selectedPrograma[$cedula] ?? null;
        if ($prog) {
            $filtros['programa'] = (int) $prog;
        }

        $result = $professorService->alternarHabilitacionModulo(
            $cedula,
            (int) $this->lapsoFilter,
            $filtros,
            [
                'anio' => $this->selectedYear[$cedula] ?? null,
                'seccion' => $this->selectedSection[$cedula] ?? null,
            ],
        );

        session()->flash($result['flash'], $result['message']);

        if ($result['ok'] && ($result['deshabilitado'] ?? false)) {
            unset($this->selectedYear[$cedula], $this->selectedSection[$cedula], $this->selectedPrograma[$cedula]);
        }

        $this->safeRefreshIcons();
    }

    public function placeholder(): string
    {
        return '<div style="text-align:center;padding:40px;color:#666;">Cargando gesti&oacute;n de profesores...</div>';
    }

    public function render(IntranetProfessorService $professorService)
    {
        try {
            return view('livewire.project-professor-manager', array_merge(
                $professorService->datosVistaGestion([
                    'search' => $this->search,
                    'lapso' => $this->lapsoFilter ? (int) $this->lapsoFilter : null,
                    'programa' => $this->programaFilter ? (int) $this->programaFilter : null,
                    'trayecto' => $this->trayectoFilter ? (int) $this->trayectoFilter : null,
                    'seccion' => $this->seccionFilter ? (int) $this->seccionFilter : null,
                    'page' => $this->getPage(),
                ]),
                ['professorService' => $professorService]
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en ProjectProfessorManager::render: ' . $e->getMessage());
            return view('livewire.project-professor-manager', [
                'docentes' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1),
                'lapsos' => collect(),
                'programas' => collect(),
                'trayectosCatalogo' => collect(),
                'secciones' => collect(),
                'trayectosHabilitar' => collect(),
                'intranetDisponible' => false,
                'professorService' => $professorService,
            ]);
        }
    }

    /**
     * @return array{programa?: int|null, trayecto?: int|null, seccion?: int|null}
     */
    protected function filtrosIntranet(): array
    {
        return array_filter([
            'programa' => $this->programaFilter !== '' ? (int) $this->programaFilter : null,
            'trayecto' => $this->trayectoFilter !== '' ? (int) $this->trayectoFilter : null,
            'seccion' => $this->seccionFilter !== '' ? (int) $this->seccionFilter : null,
        ]);
    }
}
