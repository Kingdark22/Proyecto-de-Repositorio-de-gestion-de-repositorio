<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Models\TipoInvestigacion;
use App\Services\UnicidadNombreService;
use Livewire\Component;
use Livewire\WithPagination;

class TipoInvestigacionManager extends Component
{
    use WithPagination;
    use WithSafeNotify;

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
            TipoInvestigacion::class,
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
            'nombre.required' => 'El nombre del tipo es obligatorio.',
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
        $item = TipoInvestigacion::find($id);
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

        TipoInvestigacion::guardar(
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'estado_logico' => true,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        $this->safeDispatch('success', $this->editingId ? 'Tipo de Investigación actualizado con éxito.' : 'Tipo de Investigación registrado con éxito.');
        $this->safeRefreshIcons();
    }

    public function toggleStatus($id)
    {
        $item = TipoInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $this->safeDispatch('success', $item->estado_logico ? 'Tipo habilitado correctamente.' : 'Tipo deshabilitado correctamente.');
        $this->safeRefreshIcons();
    }

    public function delete($id)
    {
        $item = TipoInvestigacion::findOrFail($id);
        $item->borrar();
        $this->safeDispatch('success', 'Tipo de Investigación eliminado correctamente.');
        $this->safeRefreshIcons();
    }

    public function with()
    {
        return [
            'items' => TipoInvestigacion::where('nombre', 'ILIKE', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }

    public function render()
    {
        return view('livewire.tipo-investigacion-manager', $this->with());
    }
}
