<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Services\ComunidadGestionService;
use App\Services\IntranetProfessorService;
use App\Services\UnicidadNombreService;
use App\Services\ValidacionCorreoService;
use App\Services\ValidacionRifService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;

#[Lazy]

class ComunidadManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $viewMode = 'list';

    public ?int $editingId = null;

    public string $nombre = '';

    public ?string $nombreStatus = null;

    public function updatedNombre(): void
    {
        if (strlen(trim($this->nombre)) < 3) {
            $this->nombreStatus = null;
            $this->resetValidation('nombre');
            return;
        }
        $this->nombreStatus = app(UnicidadNombreService::class)->check(
            Comunidad::class,
            'nombre',
            $this->nombre,
            $this->editingId,
        ) ? 'disponible' : 'no_disponible';
        if ($this->nombreStatus === 'disponible') {
            $this->resetValidation('nombre');
        }
    }

    public string $rif = '';

    public string $rifLetra = 'J';

    public string $rifNumero = '';

    public ?string $rifDigito = null;

    public ?string $rifStatus = null;

    public ?string $rifError = null;

    public function updatedRifNumero(ValidacionRifService $rifService): void
    {
        $num = preg_replace('/\D/', '', $this->rifNumero);
        $this->rifNumero = $num;
        if ($num === '') {
            $this->rifDigito = null;
            $this->rifStatus = null;
            $this->rifError = null;
            $this->resetValidation('rifNumero');
            return;
        }
        if (strlen($num) < 9) {
            $this->rifDigito = null;
            $this->rifStatus = 'invalido';
            $this->rifError = 'Debe tener 9 dígitos';
            $this->resetValidation('rifNumero');
            return;
        }
        $this->rifDigito = $rifService->calcularDigito($this->rifLetra, $num);
        $this->rifStatus = $this->rifDigito !== null ? 'valido' : 'invalido';
        $this->rifError = $this->rifStatus === 'valido' ? null : 'RIF inválido';
        if ($this->rifStatus === 'valido') {
            $this->resetValidation('rifNumero');
        }
    }

    public function updatedRifLetra(ValidacionRifService $rifService): void
    {
        if (strlen($this->rifNumero) >= 9) {
            $this->updatedRifNumero($rifService);
        }
    }

    public string $correo = '';

    public ?string $correoStatus = null;

    public ?string $correoError = null;

    public function updatedCorreo(ValidacionCorreoService $correoService): void
    {
        $correo = trim($this->correo);
        if ($correo === '' || strlen($correo) < 5) {
            $this->correoStatus = null;
            $this->correoError = null;
            $this->resetValidation('correo');
            return;
        }
        $resultado = $correoService->validarCompleto($correo);
        $this->correoStatus = $resultado['valido'] ? 'valido' : 'invalido';
        $this->correoError = $resultado['error'];
        if ($this->correoStatus === 'valido') {
            $this->resetValidation('correo');
        }
    }

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

        if ($user->hasRole('administrador', 'coordinador', 'gestionador')) {
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

        $this->reset(['editingId', 'nombre', 'rif', 'rifLetra', 'rifNumero', 'rifDigito', 'rifStatus', 'rifError', 'correo', 'correoStatus', 'correoError', 'numero_telefono', 'prefijo_telefono', 'estado_id', 'municipio_id', 'dir_nombre', 'nombreStatus']);
        $this->prefijo_telefono = '0424';
        $this->rifLetra = 'J';
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

        $parsed = app(ValidacionRifService::class)->parsear($datos['rif'] ?? '');
        $this->rifLetra = $parsed['letra'];
        $this->rifNumero = $parsed['numero'];
        $this->rifDigito = $parsed['digito'];
        $this->rifStatus = $parsed['digito'] !== null ? 'valido' : null;
        $this->fill(collect($datos)->except('rif')->toArray());
        $this->nombreStatus = 'disponible';
        $this->updatedCorreo(app(ValidacionCorreoService::class));

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

        if ($this->nombreStatus === 'no_disponible') {
            $this->addError('nombre', 'Este nombre ya está en uso.');
            return;
        }

        if ($this->rifNumero !== '' && strlen($this->rifNumero) < 9) {
            $this->addError('rifNumero', 'El RIF debe tener exactamente 9 dígitos.');
            return;
        }

        if ($this->rifNumero !== '' && $this->rifStatus !== 'valido') {
            $this->addError('rifNumero', 'El RIF ingresado no es válido.');
            return;
        }

        if ($this->correo !== '' && $this->correoStatus !== 'valido') {
            $this->addError('correo', $this->correoError ?? 'El correo ingresado no es válido.');
            return;
        }

        if ($this->correo !== '') {
            $resultado = app(ValidacionCorreoService::class)->validarCompleto($this->correo, true);
            if (!$resultado['valido']) {
                $this->addError('correo', $resultado['error'] ?? 'El dominio del correo no existe.');
                return;
            }
        }

        $payload = [
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'prefijo_telefono' => $this->prefijo_telefono,
            'numero_telefono' => $this->numero_telefono,
            'estado_id' => $this->estado_id,
            'municipio_id' => $this->municipio_id,
            'dir_nombre' => $this->dir_nombre,
        ];

        if ($this->rifNumero !== '') {
            $payload['rif'] = "{$this->rifLetra}-{$this->rifNumero}-{$this->rifDigito}";
        }

        $gestion->guardar($this->editingId, $payload);
 
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

        $result = array_merge($listado, [
            'puedeGestionar' => $this->puedeGestionar(),
            'lapsoVigente' => app(IntranetProfessorService::class)->lapsosActivos()->first(),
        ]);

        if ($this->viewMode === 'form') {
            $result = array_merge($result, $gestion->datosVistaFormulario($this->estado_id));
        }

        return $result;
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div style="padding: 20px; margin: 10px 0;">
            <style>
                @keyframes plcPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.85; } }
                @keyframes plcShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
            </style>
            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Cargando comunidades...</legend>
                <div style="animation: plcPulse 1.5s ease-in-out infinite;">
                    <div style="height: 28px; width: 40%; background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%); background-size: 200% 100%; animation: plcShimmer 1.5s infinite; border-radius: 3px; margin-bottom: 16px;"></div>
                    <div style="height: 18px; width: 100%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: plcShimmer 1.5s infinite; border-radius: 3px; margin-bottom: 6px;"></div>
                    <div style="height: 18px; width: 100%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: plcShimmer 1.5s infinite; border-radius: 3px; margin-bottom: 6px;"></div>
                    <div style="height: 18px; width: 85%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: plcShimmer 1.5s infinite; border-radius: 3px; margin-bottom: 6px;"></div>
                    <div style="height: 18px; width: 70%; background: linear-gradient(90deg, #f0f0f0 25%, #fafafa 50%, #f0f0f0 75%); background-size: 200% 100%; animation: plcShimmer 1.5s infinite; border-radius: 3px;"></div>
                </div>
                <div style="text-align: center; margin-top: 15px; font-size: 11px; color: #888;">
                    Consultando comunidades y catálogos...
                </div>
            </fieldset>
        </div>
        HTML;
    }

    public function render(ComunidadGestionService $gestion)
    {
        return view('livewire.comunidad-manager', $this->with($gestion));
    }
}
