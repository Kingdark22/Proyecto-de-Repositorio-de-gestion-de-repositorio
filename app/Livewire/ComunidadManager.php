<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Services\ComunidadGestionService;
use App\Services\IntranetProfessorService;
use Livewire\Component;
use Livewire\WithPagination;

class ComunidadManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $viewMode = 'list';

    public ?int $editingId = null;

    public string $nombre = '';

    public string $rif = '';

    public string $correo = '';

    public string $numero_telefono = '';

    public string $prefijo_telefono = '0424';

    public string $estado_id = '';

    public string $municipio_id = '';

    public string $dir_nombre = '';
 
    public function updatedEstadoId(): void
    {
        $this->municipio_id = '';
    }

    private function validarContactoCorreoRealtime(int $index): void
    {
    }

    public function updated(string $propertyName, ComunidadGestionService $gestion): void
    {
        try {
            $this->validateOnly($propertyName, $gestion->reglasValidacion());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        }
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la comunidad es obligatorio',
            'estado_id.required' => 'Debe seleccionar un estado',
            'municipio_id.required' => 'Debe seleccionar un municipio',
            'dir_nombre.required' => 'La dirección exacta es obligatoria',
            'correo.email' => 'El correo debe ser una dirección válida',
            'prefijo_telefono.required' => 'El prefijo del teléfono es obligatorio',
            'numero_telefono.required' => 'El teléfono es obligatorio',
            'numero_telefono.digits' => 'El teléfono debe tener 7 dígitos.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function puedeGestionar(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole('administrador', 'coordinador')) {
            return true;
        }

        if ($user->hasRole('profesor proyecto')) {
            return app(IntranetProfessorService::class)
                ->esProfesorProyectoVigente(trim((string) $user->usu_cedula));
        }

        return false;
    }

    public function create(): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para registrar comunidades.');
            return;
        }

        $this->reset(['editingId', 'nombre', 'rif', 'correo', 'numero_telefono', 'prefijo_telefono', 'estado_id', 'municipio_id', 'dir_nombre']);
        $this->prefijo_telefono = '0424';
        $this->resetValidation();

        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function edit(int $id, ComunidadGestionService $gestion): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para editar comunidades.');
            return;
        }

        $this->resetValidation();
        $datos = $gestion->cargarParaEdicion($id);
        $this->editingId = $id;
        $this->fill($datos);

        $telefonoCompleto = $datos['numero_telefono'];
        $prefijos = ['0424', '0414', '0412', '0422', '0416', '0426'];
        $this->prefijo_telefono = '0424';
        $this->numero_telefono = $telefonoCompleto;

        foreach ($prefijos as $prefijo) {
            if (str_starts_with($telefonoCompleto, $prefijo)) {
                $this->prefijo_telefono = $prefijo;
                $this->numero_telefono = substr($telefonoCompleto, strlen($prefijo));
                break;
            }
        }

        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function save(ComunidadGestionService $gestion): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para guardar comunidades.');
            return;
        }
 
        $this->validate($gestion->reglasValidacion());
 
        $gestion->guardar($this->editingId, [
            'nombre' => $this->nombre,
            'rif' => $this->rif,
            'correo' => $this->correo,
            'prefijo_telefono' => $this->prefijo_telefono,
            'numero_telefono' => $this->numero_telefono,
            'estado_id' => $this->estado_id,
            'municipio_id' => $this->municipio_id,
            'dir_nombre' => $this->dir_nombre,
        ]);
 
        session()->flash('message', 'Comunidad guardada correctamente.');
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function cancel(): void
    {
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function delete(int $id, ComunidadGestionService $gestion): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para eliminar comunidades.');
            return;
        }

        $gestion->eliminar($id);
        session()->flash('message', 'Comunidad eliminada correctamente.');
    }

    public function with(ComunidadGestionService $gestion): array
    {
        $listado = $gestion->datosVistaListado([
            'search' => trim($this->search),
        ], $this->getPage());

        $formulario = $gestion->datosVistaFormulario($this->estado_id);

        return array_merge($listado, $formulario, [
            'puedeGestionar' => $this->puedeGestionar(),
            'lapsoVigente' => app(IntranetProfessorService::class)->lapsosActivos()->first(),
        ]);
    }

    public function render(ComunidadGestionService $gestion)
    {
        return view('livewire.comunidad-manager', $this->with($gestion));
    }
}
