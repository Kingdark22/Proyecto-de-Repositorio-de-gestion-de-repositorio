<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Models\MetodologiaInvestigacion;
use App\Services\UnicidadNombreService;
use Livewire\Component;
use Livewire\WithPagination;

class MetodologiaManager extends Component
{
    use WithPagination;
    use WithSafeNotify;

    public $nombre = '';
    public $descripcion = '';
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    public ?string $nombreStatus = null;

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

    public function updatedNombre(): void
    {
        if (strlen(trim($this->nombre)) < 3) {
            $this->nombreStatus = null;
            $this->resetValidation('nombre');
            return;
        }
        $this->nombreStatus = app(UnicidadNombreService::class)->check(
            MetodologiaInvestigacion::class,
            'nombre',
            $this->nombre,
            $this->editingId,
        ) ? 'disponible' : 'no_disponible';
        if ($this->nombreStatus === 'disponible') {
            $this->resetValidation('nombre');
        }
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

        MetodologiaInvestigacion::guardar(
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'estado_logico' => true,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        $this->safeDispatch('success', $this->editingId ? 'Metodología actualizada con éxito.' : 'Metodología registrada con éxito.');
        $this->safeRefreshIcons();
    }

    public function toggleStatus($id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $this->safeDispatch('success', $item->estado_logico ? 'Metodología habilitada correctamente.' : 'Metodología deshabilitada correctamente.');
        $this->safeRefreshIcons();
    }

    public function delete($id)
    {
        $item = MetodologiaInvestigacion::findOrFail($id);
        $item->borrar();
        $this->safeDispatch('success', 'Metodología eliminada correctamente.');
        $this->safeRefreshIcons();
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
