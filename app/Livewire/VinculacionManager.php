<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Models\Proyecto;
use App\Models\Vinculacion;
use Livewire\Component;
use Livewire\WithPagination;

class VinculacionManager extends Component
{
    use WithPagination;

    public string $mensaje = '';
    public string $tipoMensaje = 'success';
    public string $search = '';

    public $selectedProyecto = null;
    public $vinculacionTitulo = '';
    public $vinculacionDescripcion = '';
    public $vinculacionTipo = '';
    public $vinculacionObservaciones = '';
    public $vinculacionExistente = null;

    public string $vinculacionComunidadId = '';
    public string $searchComunidad = '';

    public function updatedSearchComunidad(): void
    {
        $this->vinculacionComunidadId = '';
    }

    public function seleccionarComunidad($id): void
    {
        $this->vinculacionComunidadId = (string) $id;
        $this->searchComunidad = '';
    }

    public function quitarComunidad(): void
    {
        $this->vinculacionComunidadId = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function vincular($proyectoId): void
    {
        $proyecto = Proyecto::with(['comunidad', 'documentos.componente',
            'linea_investigacion', 'metodologia', 'tipo_publicacion', 'tipo_investigacion'])
            ->find($proyectoId);
        if (!$proyecto) {
            $this->tipoMensaje = 'error';
            $this->mensaje = 'Proyecto no encontrado.';
            return;
        }

        Proyecto::precargarTitulos(collect([$proyecto]));
        $this->selectedProyecto = $proyecto;
        $this->vinculacionExistente = Vinculacion::with('comunidad')->where('proyecto_id', $proyectoId)->first();

        if ($this->vinculacionExistente) {
            $this->vinculacionTitulo = $this->vinculacionExistente->titulo;
            $this->vinculacionDescripcion = $this->vinculacionExistente->descripcion;
            $this->vinculacionTipo = $this->vinculacionExistente->tipo ?? '';
            $this->vinculacionObservaciones = $this->vinculacionExistente->observaciones ?? '';
            $this->vinculacionComunidadId = (string) ($this->vinculacionExistente->comunidad_id ?? '');
        } else {
            $this->vinculacionTitulo = '';
            $this->vinculacionDescripcion = '';
            $this->vinculacionTipo = '';
            $this->vinculacionObservaciones = '';
            $this->vinculacionComunidadId = '';
        }
        $this->searchComunidad = '';
    }

    public function cerrar(): void
    {
        $this->selectedProyecto = null;
        $this->vinculacionExistente = null;
        $this->vinculacionTitulo = '';
        $this->vinculacionDescripcion = '';
        $this->vinculacionTipo = '';
        $this->vinculacionObservaciones = '';
        $this->vinculacionComunidadId = '';
        $this->searchComunidad = '';
    }

    public function guardarVinculacion(): void
    {
        if (!$this->selectedProyecto) {
            return;
        }

        $titulo = trim($this->vinculacionTitulo);
        if ($titulo === '') {
            $this->tipoMensaje = 'error';
            $this->mensaje = 'Debe escribir un título para la vinculación.';
            return;
        }

        $data = [
            'proyecto_id' => $this->selectedProyecto->id,
            'vin_titulo' => $titulo,
            'vin_descripcion' => trim($this->vinculacionDescripcion) ?: null,
            'tipo' => trim($this->vinculacionTipo) ?: null,
            'observaciones' => trim($this->vinculacionObservaciones) ?: null,
            'comunidad_id' => $this->vinculacionComunidadId !== '' ? (int) $this->vinculacionComunidadId : null,
        ];

        if ($this->vinculacionExistente) {
            $this->vinculacionExistente->update($data);
            $this->tipoMensaje = 'success';
            $this->mensaje = "Vinculación «{$titulo}» actualizada.";
        } else {
            $this->vinculacionExistente = Vinculacion::create($data);
            $this->tipoMensaje = 'success';
            $this->mensaje = "Vinculación «{$titulo}» creada.";
        }
    }

    public function render()
    {
        $query = Proyecto::with('comunidad')
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                try {
                    $q->whereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$search]);
                } catch (\Throwable) {
                    $term = '%' . $search . '%';
                    $q->whereRaw('pry_resumen ILIKE ?', [$term]);
                }
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

        $comunidades = Comunidad::orderBy('com_nombre')->get(['com_codigo', 'com_nombre', 'com_rif']);

        $comunidadSeleccionada = null;
        if ($this->vinculacionComunidadId !== '') {
            $comunidadSeleccionada = Comunidad::with('direccion.municipio.estado')
                ->find((int) $this->vinculacionComunidadId);
        }

        $comunidadesFiltradas = collect();
        if ($this->searchComunidad !== '') {
            $term = '%' . $this->searchComunidad . '%';
            $comunidadesFiltradas = Comunidad::where('nombre', 'ILIKE', $term)
                ->orWhere('rif', 'ILIKE', $term)
                ->orderBy('com_nombre')
                ->get(['com_codigo', 'com_nombre', 'com_rif']);
        }

        return view('livewire.vinculacion-manager', [
            'proyectos' => $proyectos,
            'vinculaciones' => $vinculaciones,
            'comunidades' => $comunidades,
            'comunidadSeleccionada' => $comunidadSeleccionada,
            'comunidadesFiltradas' => $comunidadesFiltradas,
        ]);
    }
}
