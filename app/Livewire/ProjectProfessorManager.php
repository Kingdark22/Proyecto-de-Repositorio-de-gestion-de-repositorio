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

    public function mount(IntranetProfessorService $professorService): void
    {
        try {
            $lapsoVigente = \App\Models\LapsoAcademico::vigente();
            if ($lapsoVigente) {
                $this->lapsoFilter = (string) $lapsoVigente->lap_codigo;
            } else {
                $lapsos = $professorService->lapsosActivos();
                if ($lapsos->isNotEmpty()) {
                    $this->lapsoFilter = (string) $lapsos->first()->lap_codigo;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en ProjectProfessorManager::mount: ' . $e->getMessage());
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function placeholder(): string
    {
        return '<div style="text-align:center;padding:40px;color:#666;">Cargando listado de profesores...</div>';
    }

    public function render(IntranetProfessorService $professorService)
    {
        try {
            return view('livewire.project-professor-manager', array_merge(
                $professorService->datosVistaGestion([
                    'search' => $this->search,
                    'lapso' => $this->lapsoFilter ? (int) $this->lapsoFilter : null,
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
}
