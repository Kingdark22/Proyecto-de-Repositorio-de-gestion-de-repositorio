<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Models\Proyecto;
use App\Models\Vinculacion;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class VinculacionManager extends Component
{
    use WithPagination;


    public string $search = '';

    public $selectedProyecto = null;
    public $integrantesProyecto;
    public $vinculacionTitulo = '';
    public $vinculacionExistente = null;

    public string $vinculacionComunidadId = '';

    public bool $mostrarModalComunidad = false;
    public string $modalComunidadNombre = '';
    public string $modalComunidadRif = '';
    public string $buscarComunidad = '';
    public Collection $comunidadesEncontradas;

    public function mount(): void
    {
        $this->comunidadesEncontradas = collect();
    }

    public function updatedBuscarComunidad(): void
    {
        $q = trim($this->buscarComunidad);
        if ($q === '') {
            $this->comunidadesEncontradas = collect();
            return;
        }
        $this->comunidadesEncontradas = Comunidad::whereRaw('com_nombre ILIKE ?', ["%{$q}%"])
            ->orWhereRaw('com_rif ILIKE ?', ["%{$q}%"])
            ->orderByRaw('com_nombre')
            ->get();
    }

    public function abrirModalComunidad(): void
    {
        $this->mostrarModalComunidad = true;
        $this->modalComunidadNombre = '';
        $this->modalComunidadRif = '';
        $this->buscarComunidad = '';
        $this->comunidadesEncontradas = collect();
    }

    public function cerrarModalComunidad(): void
    {
        $this->mostrarModalComunidad = false;
    }

    public function seleccionarComunidadModal(string $id): void
    {
        $this->vinculacionComunidadId = $id;
        $this->cerrarModalComunidad();
    }

    public function guardarComunidadModal(): void
    {
        $this->validate([
            'modalComunidadNombre' => 'required|string|max:255',
        ], [
            'modalComunidadNombre.required' => 'El nombre de la comunidad es obligatorio.',
        ]);

        $comunidad = Comunidad::create([
            'nombre' => $this->modalComunidadNombre,
            'rif' => $this->modalComunidadRif ?: null,
        ]);

        $this->vinculacionComunidadId = (string) $comunidad->id;
        $this->cerrarModalComunidad();
    }

    public function quitarComunidad(): void
    {
        $this->vinculacionComunidadId = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function vincular($proyectoId): void
    {
        $proyecto = Proyecto::with(['comunidad', 'documentos.componente',
            'linea_investigacion', 'metodologia', 'tipo_publicacion', 'tipo_investigacion'])
            ->find($proyectoId);
        if (!$proyecto) {
            $this->dispatch('notify', type: 'error', message: 'Proyecto no encontrado.');
            return;
        }

        Proyecto::precargarTitulos(collect([$proyecto]));
        $this->selectedProyecto = $proyecto;
        $this->integrantesProyecto = app(\App\Services\IntranetEquipoSeccionService::class)
            ->integrantes($proyecto->equipo_ref ?? '');
        $this->vinculacionExistente = Vinculacion::with('comunidad')->where('proyecto_id', $proyectoId)->first();

        if ($this->vinculacionExistente) {
            $this->vinculacionTitulo = $this->vinculacionExistente->titulo;
            $this->vinculacionComunidadId = (string) ($this->vinculacionExistente->comunidad_id ?? '');
        } else {
            $this->vinculacionTitulo = '';
            $this->vinculacionComunidadId = '';
        }
    }

    public function cerrar(): void
    {
        $this->selectedProyecto = null;
        $this->integrantesProyecto = null;
        $this->vinculacionExistente = null;
        $this->vinculacionTitulo = '';
        $this->vinculacionComunidadId = '';
    }

    public function guardarVinculacion(): void
    {
        if (!$this->selectedProyecto) {
            return;
        }

        $titulo = trim($this->vinculacionTitulo);
        if ($titulo === '') {
            $this->dispatch('notify', type: 'error', message: 'Debe escribir un título para la vinculación.');
            return;
        }

        $data = [
            'proyecto_id' => $this->selectedProyecto->id,
            'vin_titulo' => $titulo,
            'comunidad_id' => $this->vinculacionComunidadId !== '' ? (int) $this->vinculacionComunidadId : null,
            'tipo' => 'Vinculación',
        ];

        if ($this->vinculacionExistente) {
            $this->vinculacionExistente->update($data);
            $this->dispatch('notify', type: 'success', message: "Vinculación «{$titulo}» actualizada.");
        } else {
            $this->vinculacionExistente = Vinculacion::create($data);
            $this->dispatch('notify', type: 'success', message: "Vinculación «{$titulo}» creada.");
        }
    }

    public function render()
    {
        $query = Proyecto::with('comunidad')
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);

        if ($this->search !== '') {
            $search = trim($this->search);
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($search, $term) {
                $q->whereRaw('pry_resumen ILIKE ?', [$term])
                  ->orWhereRaw('pry_direccion_logica ILIKE ?', [$term])
                  ->orWhereRaw('pry_motivo_rechazo ILIKE ?', [$term])
                  ->orWhereRaw('pry_creador_cedula ILIKE ?', [$term])
                  ->orWhereRaw('cast(pry_calificacion as text) ILIKE ?', [$term])
                  ->orWhereHas('comunidad', fn($cq) => $cq->whereRaw('com_nombre ILIKE ?', [$term]))
                  ->orWhereHas('linea_investigacion', fn($cq) => $cq->whereRaw('lin_nombre_investigacion ILIKE ?', [$term]))
                  ->orWhereHas('metodologia', fn($cq) => $cq->whereRaw('mei_nombre ILIKE ?', [$term]))
                  ->orWhereHas('tipo_publicacion', fn($cq) => $cq->whereRaw('tpu_nombre ILIKE ?', [$term]))
                  ->orWhereHas('tipo_investigacion', fn($cq) => $cq->whereRaw('tin_nombre ILIKE ?', [$term]))
                  ->orWhereHas('objetivo_investigacion', fn($cq) => $cq->whereRaw('obi_nombre ILIKE ?', [$term]));
            });
        }

        $proyectos = $query->orderBy('id', 'desc')->paginate(10);

        Proyecto::precargarTitulos(collect($proyectos->items()));

        $proyectosCollection = collect($proyectos->items());
        $ids = $proyectosCollection->pluck('id');

        $vinculaciones = Vinculacion::with('comunidad')
            ->whereIn('proyecto_id', $ids)
            ->get()
            ->keyBy('proyecto_id');

        $comunidadSeleccionada = null;
        if ($this->vinculacionComunidadId !== '') {
            $comunidadSeleccionada = Comunidad::with('direccion.municipio.estado')
                ->find((int) $this->vinculacionComunidadId);
        }

        $comunidades = Comunidad::orderBy('com_nombre')->get();

        return view('livewire.vinculacion-manager', [
            'proyectos' => $proyectos,
            'vinculaciones' => $vinculaciones,
            'comunidadSeleccionada' => $comunidadSeleccionada,
            'comunidades' => $comunidades,
        ]);
    }
}
