<?php

namespace App\Livewire;

use App\Models\ObjetivoInvestigacion;
use App\Services\UnicidadNombreService;
use Livewire\Component;
use Livewire\WithPagination;

class ObjetivoInvestigacionManager extends Component
{
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    public ?string $nombreStatus = null;

    public function updatedNombre(): void
    {
        if (strlen(trim($this->nombre)) < 3) {
            $this->nombreStatus = null;
            $this->resetValidation('nombre');
            return;
        }
        $this->nombreStatus = app(UnicidadNombreService::class)->check(
            ObjetivoInvestigacion::class,
            'nombre',
            $this->nombre,
            $this->editingId,
        ) ? 'disponible' : 'no_disponible';
        if ($this->nombreStatus === 'disponible') {
            $this->resetValidation('nombre');
        }
    }

    protected $rules = [
        'nombre' => 'required|min:3|max:255',
        'descripcion' => 'required|max:500',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del objetivo es obligatorio.',
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
        $item = ObjetivoInvestigacion::find($id);
        $this->nombre = $item->nombre;
        $this->descripcion = $item->descripcion;
        $this->nombreStatus = 'disponible';
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
        $this->nombreStatus = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->nombreStatus === 'no_disponible') {
            $this->addError('nombre', 'Este nombre ya está en uso.');
            return;
        }

        ObjetivoInvestigacion::guardar(
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'estado_logico' => true,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        $this->dispatch('notify', type: 'success', message: $this->editingId ? 'Objetivo de Investigación actualizado con éxito.' : 'Objetivo de Investigación registrado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = ObjetivoInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $this->dispatch('notify', type: 'success', message: $item->estado_logico ? 'Objetivo habilitado correctamente.' : 'Objetivo deshabilitado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        $item = ObjetivoInvestigacion::findOrFail($id);
        $item->borrar();
        $this->dispatch('notify', type: 'success', message: 'Objetivo de Investigación eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'items' => ObjetivoInvestigacion::where('nombre', 'ILIKE', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }

    public function render()
    {
        return view('livewire.objetivo-investigacion-manager', $this->with());
    }
}
