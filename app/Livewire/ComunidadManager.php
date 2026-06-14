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

    public string $estado_id = '';

    public string $municipio_id = '';

    public string $dir_nombre = '';

    public array $contactos = [];

    public function updatedEstadoId(): void
    {
        $this->municipio_id = '';
    }

    private function validarContactoCorreoRealtime(int $index): void
    {
        $correo = trim($this->contactos[$index]['correo'] ?? '');
        $correo_conf = trim($this->contactos[$index]['correo_confirmacion'] ?? '');

        $this->resetErrorBag("contactos.{$index}.correo_confirmacion");
        $this->resetErrorBag("contactos.{$index}.correo");

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->addError("contactos.{$index}.correo", 'Debe ingresar un correo electrónico válido.');
            return;
        }

        if ($correo === '') {
            return;
        }

        if ($correo_conf !== '' && !filter_var($correo_conf, FILTER_VALIDATE_EMAIL)) {
            $this->addError("contactos.{$index}.correo_confirmacion", 'Debe ingresar un correo electrónico válido.');
            return;
        }

        if ($correo_conf !== '') {
            if ($correo !== $correo_conf) {
                $this->addError("contactos.{$index}.correo_confirmacion", 'La confirmación del correo no coincide.');
            }
        }
    }

    public function updated(string $propertyName, ComunidadGestionService $gestion): void
    {
        if (str_starts_with($propertyName, 'contactos.')) {
            preg_match('/contactos\.(\d+)\.(correo|correo_confirmacion)/', $propertyName, $matches);
            if ($matches) {
                $index = (int)$matches[1];
                $this->validarContactoCorreoRealtime($index);
            } else {
                try {
                    $this->validateOnly($propertyName, $gestion->reglasValidacion());
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $this->setErrorBag($e->validator->errors());
                }
            }
        } else {
            try {
                $this->validateOnly($propertyName, $gestion->reglasValidacion());
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->setErrorBag($e->validator->errors());
            }
        }
    }

    public function agregarContacto(): void
    {
        $this->contactos[] = ['nombre' => '', 'apellido' => '', 'correo' => '', 'correo_confirmacion' => '', 'prefijo' => '0424', 'telefono' => '', 'cargo' => '', 'mostrar_input_cargo' => false, 'cargo_custom' => ''];
    }

    public function mostrarCargoPersonalizado(int $index): void
    {
        $this->contactos[$index]['mostrar_input_cargo'] = true;
    }

    public function aceptarCargoPersonalizado(int $index): void
    {
        $custom = trim($this->contactos[$index]['cargo_custom'] ?? '');
        if ($custom !== '') {
            $this->contactos[$index]['cargo'] = $custom;
        }
        $this->contactos[$index]['mostrar_input_cargo'] = false;
    }

    public function cancelarCargoPersonalizado(int $index): void
    {
        $this->contactos[$index]['cargo_custom'] = '';
        $this->contactos[$index]['mostrar_input_cargo'] = false;
    }

    private function normalizarContactos(): array
    {
        return array_map(function ($c) {
            unset($c['cargo_custom'], $c['mostrar_input_cargo']);
            return $c;
        }, $this->contactos);
    }

    public function quitarContacto(int $index): void
    {
        unset($this->contactos[$index]);
        $this->contactos = array_values($this->contactos);
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la comunidad es obligatorio',
            'estado_id.required' => 'Debe seleccionar un estado',
            'municipio_id.required' => 'Debe seleccionar un municipio',
            'dir_nombre.required' => 'La dirección exacta es obligatoria',
            'correo.email' => 'El correo debe ser una dirección válida',
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

        $this->reset(['editingId', 'nombre', 'rif', 'correo', 'contactos', 'estado_id', 'municipio_id', 'dir_nombre']);
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

        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function guardarContactosAhora(ComunidadGestionService $gestion): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para guardar.');
            return;
        }

        $this->validate([
            'nombre' => 'required|string|max:255',
            'estado_id' => 'required|integer|exists:estados,est_codigo',
            'municipio_id' => 'required|integer|exists:municipios,mun_codigo',
            'dir_nombre' => 'required|string|max:500',
            'contactos' => 'nullable|array',
            'contactos.*.nombre' => 'required|string|max:255',
            'contactos.*.apellido' => 'nullable|string|max:255',
            'contactos.*.correo' => 'nullable|email|max:150',
            'contactos.*.correo_confirmacion' => 'nullable|email|max:150',
            'contactos.*.prefijo' => 'nullable|in:0424,0414,0412,0422,0416,0426',
            'contactos.*.telefono' => 'nullable|string|max:50',
            'contactos.*.cargo' => 'nullable|string|max:100',
        ]);

        foreach ($this->contactos as $i => $contacto) {
            $this->validarContactoCorreoRealtime($i);
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $id = $gestion->guardar($this->editingId, [
            'nombre' => $this->nombre,
            'rif' => $this->rif,
            'correo' => $this->correo,
            'estado_id' => $this->estado_id,
            'municipio_id' => $this->municipio_id,
            'dir_nombre' => $this->dir_nombre,
            'contactos' => $this->normalizarContactos(),
        ]);

        if ($this->editingId === null) {
            $this->editingId = $id;
        }

        session()->flash('message', 'Contactos guardados correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function save(ComunidadGestionService $gestion): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para guardar comunidades.');
            return;
        }

        $this->validate($gestion->reglasValidacion());

        foreach ($this->contactos as $i => $contacto) {
            $this->validarContactoCorreoRealtime($i);
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $gestion->guardar($this->editingId, [
            'nombre' => $this->nombre,
            'rif' => $this->rif,
            'correo' => $this->correo,
            'estado_id' => $this->estado_id,
            'municipio_id' => $this->municipio_id,
            'dir_nombre' => $this->dir_nombre,
            'contactos' => $this->normalizarContactos(),
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
