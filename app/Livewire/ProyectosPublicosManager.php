<?php

namespace App\Livewire;

use App\Models\ComentarioProyecto;
use App\Models\ProyectoPublicado;
use Livewire\Component;

class ProyectosPublicosManager extends Component
{
    public $selectedPubId = null;
    public $nuevoComentario = '';
    public $nombreContacto = '';
    public $correoContacto = '';

    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public function seleccionar($pubId): void
    {
        $this->selectedPubId = $pubId;
        $this->nuevoComentario = '';
        $this->nombreContacto = '';
        $this->correoContacto = '';
    }

    public function cerrar(): void
    {
        $this->selectedPubId = null;
        $this->nuevoComentario = '';
        $this->nombreContacto = '';
        $this->correoContacto = '';
    }

    public function comentar(): void
    {
        $rules = [
            'nuevoComentario' => 'required|min:3|max:1000',
        ];
        if (!auth()->check()) {
            $rules['nombreContacto'] = 'required|min:2|max:255';
        }
        $this->validate($rules);

        if (!$this->selectedPubId) {
            return;
        }

        $pub = ProyectoPublicado::find($this->selectedPubId);
        if (!$pub) {
            return;
        }

        $data = [
            'descripcion' => trim($this->nuevoComentario),
            'proyecto_id' => $pub->proyecto_id,
        ];

        if (auth()->check()) {
            $data['nombre_contacto'] = auth()->user()->name ?? null;
        } else {
            $data['nombre_contacto'] = trim($this->nombreContacto);
        }

        ComentarioProyecto::create($data);

        $this->nuevoComentario = '';
        $this->nombreContacto = '';
        $this->correoContacto = '';
        $this->tipoMensaje = 'success';
        $this->mensaje = 'Comentario enviado correctamente.';
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function render()
    {
        $publicaciones = ProyectoPublicado::where('estado', 'publicado')
            ->with('proyecto')
            ->orderBy('id', 'desc')
            ->get();

        $comentarios = collect();
        if ($this->selectedPubId) {
            $pub = ProyectoPublicado::find($this->selectedPubId);
            if ($pub) {
                $comentarios = ComentarioProyecto::where('proyecto_id', $pub->proyecto_id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        return view('livewire.proyectos-publicos-manager', [
            'publicaciones' => $publicaciones,
            'comentarios' => $comentarios,
        ]);
    }
}
