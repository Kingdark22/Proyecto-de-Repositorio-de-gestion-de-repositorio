<?php

namespace App\Livewire;

use App\Models\Organizacion;
use App\Services\ProyectoBusquedaService;
use Illuminate\Support\Facades\Mail;
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

    public bool $showEnvioModal = false;

    public array $orgSeleccionadas = [];

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

    public function abrirEnvio(): void
    {
        if (empty($this->selectedProjects)) {
            $this->mensaje = 'Seleccione al menos un proyecto.';
            $this->tipoMensaje = 'error';
            return;
        }
        $this->orgSeleccionadas = [];
        $this->showEnvioModal = true;
    }

    public function cerrarEnvio(): void
    {
        $this->showEnvioModal = false;
        $this->orgSeleccionadas = [];
    }

    public function enviarCorreo(): void
    {
        if (empty($this->selectedProjects)) {
            $this->mensaje = 'Seleccione al menos un proyecto.';
            $this->tipoMensaje = 'error';
            $this->showEnvioModal = false;
            return;
        }

        $orgs = Organizacion::whereIn('org_dep_codigo', $this->orgSeleccionadas)->get()->unique('id');

        if ($orgs->isEmpty()) {
            $this->mensaje = 'Seleccione al menos una organización.';
            $this->tipoMensaje = 'error';
            return;
        }

        $proyectos = \App\Models\Proyecto::whereIn('pry_codigo', $this->selectedProjects)->get();

        foreach ($orgs as $org) {
            $correo = $org->correo;
            if (empty($correo)) continue;

            try {
                Mail::raw($this->cuerpoCorreo($proyectos, $org), function ($msg) use ($correo, $org) {
                    $msg->to($correo, $org->nombre)
                        ->subject('Proyectos de interés - Repositorio UPTP');
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando correo a {$correo}: " . $e->getMessage());
            }
        }

        $this->selectedProjects = [];
        $this->showEnvioModal = false;
        $this->orgSeleccionadas = [];
        $this->mensaje = 'Correo(s) enviado(s) correctamente.';
        $this->tipoMensaje = 'success';
    }

    protected function cuerpoCorreo($proyectos, $org): string
    {
        $lineas = [];
        $lineas[] = "Estimado(a) {$org->nombre},";
        $lineas[] = "";
        $lineas[] = "A continuación se listan los proyectos que podrían ser de su interés:";
        $lineas[] = "";

        foreach ($proyectos as $i => $p) {
            $num = $i + 1;
            $lineas[] = "{$num}. {$p->titulo}";
            $lineas[] = "   Resumen: " . strip_tags($p->resumen ?? 'Sin resumen');
            $lineas[] = "";
        }

        $lineas[] = "Atentamente,";
        $lineas[] = "Repositorio de Proyectos - UPTP";

        return implode("\n", $lineas);
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
        $organizaciones = collect();
        try {
            $organizaciones = Organizacion::query()
                ->where('correo', '!=', '')
                ->orderBy('nombre')
                ->get();
        } catch (\Throwable) {}

        return view('livewire.project-search', array_merge(
            $busqueda->datosVista($this->filtrosBusqueda(), $this->getPage()),
            [
                'organizaciones' => $organizaciones,
            ]
        ));
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
