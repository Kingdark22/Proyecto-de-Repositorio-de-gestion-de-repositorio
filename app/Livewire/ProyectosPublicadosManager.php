<?php

namespace App\Livewire;

use App\Models\ComentarioProyecto;
use App\Models\Proyecto;
use Livewire\Component;

class ProyectosPublicadosManager extends Component
{
    public $selectedPubId = null;
    public $nuevoComentario = '';

    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public string $search = '';
    public string $filterComunidadId = '';

    public function seleccionar($pubId): void
    {
        $this->selectedPubId = $pubId;
        $this->nuevoComentario = '';
    }

    public function cerrar(): void
    {
        $this->selectedPubId = null;
        $this->nuevoComentario = '';
    }

    public function comentar(): void
    {
        $this->validate([
            'nuevoComentario' => 'required|min:3|max:1000',
        ]);

        if (!$this->selectedPubId) {
            return;
        }

        $pub = Proyecto::find($this->selectedPubId);
        if (!$pub) {
            return;
        }

        ComentarioProyecto::create([
            'descripcion' => trim($this->nuevoComentario),
            'proyecto_id' => $pub->id,
        ]);

        $this->nuevoComentario = '';
        $this->tipoMensaje = 'success';
        $this->mensaje = 'Comentario agregado correctamente.';
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    protected function proyectosQuery()
    {
        $query = Proyecto::with('comunidad')
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);

        if ($this->filterComunidadId !== '') {
            $query->where('comunidad_id', (int) $this->filterComunidadId);
        }

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

        return $query->orderBy('id', 'desc');
    }

    public function render()
    {
        $proyectos = $this->proyectosQuery()->get();

        Proyecto::precargarTitulos($proyectos);

        $selectedProyecto = null;
        $comentarios = collect();
        if ($this->selectedPubId) {
            $selectedProyecto = Proyecto::with('documentos.componente')->find($this->selectedPubId);
            if (!$selectedProyecto) {
                $this->selectedPubId = null;
            } else {
                Proyecto::precargarTitulos(collect([$selectedProyecto]));
                $comentarios = ComentarioProyecto::where('proyecto_id', $selectedProyecto->id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        $comunidades = \App\Models\Comunidad::orderBy('nombre')->get();

        return view('livewire.proyectos-publicados-manager', [
            'proyectos' => $proyectos,
            'selectedProyecto' => $selectedProyecto,
            'comentarios' => $comentarios,
            'comunidades' => $comunidades,
        ]);
    }
}
