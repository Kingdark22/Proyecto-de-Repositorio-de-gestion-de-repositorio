<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Models\TipoPublicacion;
use App\Services\UnicidadNombreService;
use Livewire\Component;
use Livewire\WithPagination;

class TipoPublicacionManager extends Component
{
    use WithPagination;
    use WithSafeNotify;

    public $nombre = '';
    public $mencion_honorifica = false;
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
            TipoPublicacion::class,
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
        'mencion_honorifica' => 'boolean',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del tipo de publicación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'mencion_honorifica.required' => 'El campo mención honorífica es obligatorio.',
            'mencion_honorifica.integer' => 'Formato de mención no válido.',
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
        $item = TipoPublicacion::find($id);
        $this->nombre = $item->nombre;
        $this->mencion_honorifica = $item->mencion_honorifica;
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
        $this->mencion_honorifica = false;
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

        TipoPublicacion::guardar(
            [
                'nombre' => $this->nombre,
                'mencion_honorifica' => $this->mencion_honorifica,
                'estado_logico' => true,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        $this->safeDispatch('success', $this->editingId ? 'Tipo de Publicación actualizado con éxito.' : 'Tipo de Publicación registrado con éxito.');
        $this->safeRefreshIcons();
    }

    public function toggleStatus($id)
    {
        $item = TipoPublicacion::findOrFail($id);
        $item->alternarEstado();

        $this->safeDispatch('success', $item->estado_logico ? 'Tipo habilitado correctamente.' : 'Tipo deshabilitado correctamente.');
        $this->safeRefreshIcons();
    }

    public function delete($id)
    {
        $item = TipoPublicacion::findOrFail($id);
        $item->borrar();
        $this->safeDispatch('success', 'Tipo de Publicación eliminado correctamente.');
        $this->safeRefreshIcons();
    }

    public function with()
    {
        return [
            'items' => TipoPublicacion::where('nombre', 'ILIKE', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }

    public function render()
    {
        return view('livewire.tipo-publicacion-manager', $this->with());
    }
}
