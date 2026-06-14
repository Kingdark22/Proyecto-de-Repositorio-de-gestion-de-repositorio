<?php

namespace App\Livewire;

use App\Models\MetodologiaInvestigacion;
use Livewire\Component;
use Livewire\WithPagination;

class MetodologiaManager extends Component
{
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    protected $rules = [
        'nombre' => 'required|min:3|max:255',
        'descripcion' => 'required|max:500',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la metodología es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no debe exceder los 500 caracteres.',
        ];
    }

    public function create()
    {
        $this->resetFields();
        $this->viewMode = 'form';
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->editingId = $id;
        $item = MetodologiaInvestigacion::find($id);
        $this->nombre = $item->nombre;
        $this->descripcion = $item->descripcion;
        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        MetodologiaInvestigacion::guardar(
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Metodología actualizada con éxito.' : 'Metodología registrada con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        session()->flash('message', $item->estado_logico ? 'Metodología habilitada correctamente.' : 'Metodología deshabilitada correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        $item->borrar();
        session()->flash('message', 'Metodología eliminada correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'items' => MetodologiaInvestigacion::where('nombre', 'ILIKE', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }

    public function render()
    {
        return view('livewire.metodologia-manager', $this->with());
    }
}
