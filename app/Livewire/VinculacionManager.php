<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Models\Comunidad;
use App\Models\Proyecto;
use App\Models\Vinculacion;
use App\Services\UnicidadNombreService;
use App\Services\ValidacionRifService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class VinculacionManager extends Component
{
    use WithPagination;
    use WithSafeNotify;


    public string $search = '';

    public $selectedProyecto = null;
    public $integrantesProyecto;
    public $vinculacionTitulo = '';
    public $vinculacionExistente = null;

    public string $vinculacionComunidadId = '';

    public bool $mostrarModalComunidad = false;
    public string $modalComunidadNombre = '';

    public ?string $modalComunidadNombreStatus = null;

    public function updatedModalComunidadNombre(): void
    {
        if (strlen(trim($this->modalComunidadNombre)) < 3) {
            $this->modalComunidadNombreStatus = null;
            $this->resetValidation('modalComunidadNombre');
            return;
        }
        $this->modalComunidadNombreStatus = app(UnicidadNombreService::class)->check(
            Comunidad::class,
            'nombre',
            $this->modalComunidadNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalComunidadNombreStatus === 'disponible') {
            $this->resetValidation('modalComunidadNombre');
        }
    }

    public string $modalComunidadRifLetra = 'J';

    public string $modalComunidadRifNumero = '';

    public ?string $modalComunidadRifDigito = null;

    public ?string $modalComunidadRifStatus = null;

    public ?string $modalComunidadRifError = null;

    public function updatedModalComunidadRifNumero(ValidacionRifService $rifService): void
    {
        $num = preg_replace('/\D/', '', $this->modalComunidadRifNumero);
        $this->modalComunidadRifNumero = $num;
        if ($num === '') {
            $this->modalComunidadRifDigito = null;
            $this->modalComunidadRifStatus = null;
            $this->modalComunidadRifError = null;
            $this->resetValidation('modalComunidadRifNumero');
            return;
        }
        if (strlen($num) < 9) {
            $this->modalComunidadRifDigito = null;
            $this->modalComunidadRifStatus = 'invalido';
            $this->modalComunidadRifError = 'Debe tener 9 dígitos';
            $this->resetValidation('modalComunidadRifNumero');
            return;
        }
        $this->modalComunidadRifDigito = $rifService->calcularDigito($this->modalComunidadRifLetra, $num);
        $this->modalComunidadRifStatus = $this->modalComunidadRifDigito !== null ? 'valido' : 'invalido';
        $this->modalComunidadRifError = $this->modalComunidadRifStatus === 'valido' ? null : 'RIF inválido';
        if ($this->modalComunidadRifStatus === 'valido') {
            $this->resetValidation('modalComunidadRifNumero');
        }
    }

    public function updatedModalComunidadRifLetra(ValidacionRifService $rifService): void
    {
        if (strlen($this->modalComunidadRifNumero) >= 9) {
            $this->updatedModalComunidadRifNumero($rifService);
        }
    }

    public string $buscarComunidad = '';
    public Collection $comunidadesEncontradas;

    public function mount(): void
    {
        $this->comunidadesEncontradas = collect();
    }

    public function updatedBuscarComunidad(): void
    {
        $q = trim($this->buscarComunidad);
        if ($q === '') {
            $this->comunidadesEncontradas = collect();
            return;
        }
        $this->comunidadesEncontradas = Comunidad::whereRaw('com_nombre ILIKE ?', ["%{$q}%"])
            ->orWhereRaw('com_rif ILIKE ?', ["%{$q}%"])
            ->orderByRaw('com_nombre')
            ->get();
    }

    public function abrirModalComunidad(): void
    {
        $this->mostrarModalComunidad = true;
        $this->modalComunidadNombre = '';
        $this->modalComunidadNombreStatus = null;
        $this->modalComunidadRifLetra = 'J';
        $this->modalComunidadRifNumero = '';
        $this->modalComunidadRifDigito = null;
        $this->modalComunidadRifStatus = null;
        $this->modalComunidadRifError = null;
        $this->buscarComunidad = '';
        $this->comunidadesEncontradas = collect();
    }

    public function cerrarModalComunidad(): void
    {
        $this->mostrarModalComunidad = false;
    }

    public function seleccionarComunidadModal(string $id): void
    {
        $this->vinculacionComunidadId = $id;
        $this->cerrarModalComunidad();
    }

    public function guardarComunidadModal(): void
    {
        $this->validate([
            'modalComunidadNombre' => 'required|string|max:255',
        ], [
            'modalComunidadNombre.required' => 'El nombre de la comunidad es obligatorio.',
        ]);

        if ($this->modalComunidadNombreStatus === 'no_disponible') {
            $this->addError('modalComunidadNombre', 'Este nombre ya está en uso.');
            return;
        }

        if ($this->modalComunidadRifNumero !== '' && strlen($this->modalComunidadRifNumero) < 9) {
            $this->addError('modalComunidadRifNumero', 'El RIF debe tener exactamente 9 dígitos.');
            return;
        }

        if ($this->modalComunidadRifNumero !== '' && $this->modalComunidadRifStatus !== 'valido') {
            $this->addError('modalComunidadRifNumero', 'El RIF ingresado no es válido.');
            return;
        }

        $rif = null;
        if ($this->modalComunidadRifNumero !== '') {
            $rif = "{$this->modalComunidadRifLetra}-{$this->modalComunidadRifNumero}-{$this->modalComunidadRifDigito}";
        }

        $comunidad = Comunidad::create([
            'nombre' => $this->modalComunidadNombre,
            'rif' => $rif,
        ]);

        $this->vinculacionComunidadId = (string) $comunidad->id;
        $this->cerrarModalComunidad();
    }

    public function quitarComunidad(): void
    {
        $this->vinculacionComunidadId = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function vincular($proyectoId): void
    {
        $proyecto = Proyecto::with(['comunidad', 'documentos.componente',
            'linea_investigacion', 'metodologia', 'tipo_publicacion', 'tipo_investigacion'])
            ->find($proyectoId);
        if (!$proyecto) {
            $this->safeDispatch('error', 'Proyecto no encontrado.');
            return;
        }

        Proyecto::precargarTitulos(collect([$proyecto]));
        $this->selectedProyecto = $proyecto;
        $this->integrantesProyecto = app(\App\Services\IntranetEquipoSeccionService::class)
            ->integrantes($proyecto->equipo_ref ?? '');
        $this->vinculacionExistente = Vinculacion::with('comunidad')->where('proyecto_id', $proyectoId)->first();

        if ($this->vinculacionExistente) {
            $this->vinculacionTitulo = $this->vinculacionExistente->titulo;
            $this->vinculacionComunidadId = (string) ($this->vinculacionExistente->comunidad_id ?? '');
        } else {
            $this->vinculacionTitulo = '';
            $this->vinculacionComunidadId = '';
        }
    }

    public function cerrar(): void
    {
        $this->selectedProyecto = null;
        $this->integrantesProyecto = null;
        $this->vinculacionExistente = null;
        $this->vinculacionTitulo = '';
        $this->vinculacionComunidadId = '';
    }

    public function guardarVinculacion(): void
    {
        if (!$this->selectedProyecto) {
            return;
        }

        $titulo = trim($this->vinculacionTitulo);
        if ($titulo === '') {
            $this->safeDispatch('error', 'Debe escribir un título para la vinculación.');
            return;
        }

        $data = [
            'proyecto_id' => $this->selectedProyecto->id,
            'vin_titulo' => $titulo,
            'comunidad_id' => $this->vinculacionComunidadId !== '' ? (int) $this->vinculacionComunidadId : null,
            'tipo' => 'Vinculación',
        ];

        if ($this->vinculacionExistente) {
            $this->vinculacionExistente->update($data);
            $this->safeDispatch('success', "Vinculación «{$titulo}» actualizada.");
        } else {
            $this->vinculacionExistente = Vinculacion::create($data);
            $this->safeDispatch('success', "Vinculación «{$titulo}» creada.");
        }
    }

    public function render()
    {
        $query = Proyecto::with('comunidad')
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);

        if ($this->search !== '') {
            $search = trim($this->search);
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($search, $term) {
                $q->whereRaw('pry_resumen ILIKE ?', [$term])
                  ->orWhereRaw('pry_direccion_logica ILIKE ?', [$term])
                  ->orWhereRaw('pry_motivo_rechazo ILIKE ?', [$term])
                  ->orWhereRaw('pry_creador_cedula ILIKE ?', [$term])
                  ->orWhereHas('comunidad', fn($cq) => $cq->whereRaw('com_nombre ILIKE ?', [$term]))
                  ->orWhereHas('linea_investigacion', fn($cq) => $cq->whereRaw('lin_nombre_investigacion ILIKE ?', [$term]))
                  ->orWhereHas('metodologia', fn($cq) => $cq->whereRaw('mei_nombre ILIKE ?', [$term]))
                  ->orWhereHas('tipo_publicacion', fn($cq) => $cq->whereRaw('tpu_nombre ILIKE ?', [$term]))
                  ->orWhereHas('tipo_investigacion', fn($cq) => $cq->whereRaw('tin_nombre ILIKE ?', [$term]))
                  ->orWhereHas('objetivo_investigacion', fn($cq) => $cq->whereRaw('obi_nombre ILIKE ?', [$term]));
            });
        }

        $proyectos = $query->orderBy('id', 'desc')->paginate(10);

        Proyecto::precargarTitulos(collect($proyectos->items()));

        $proyectosCollection = collect($proyectos->items());
        $ids = $proyectosCollection->pluck('id');

        $vinculaciones = Vinculacion::with('comunidad')
            ->whereIn('proyecto_id', $ids)
            ->get()
            ->keyBy('proyecto_id');

        $comunidadSeleccionada = null;
        if ($this->vinculacionComunidadId !== '') {
            $comunidadSeleccionada = Comunidad::with('direccion.municipio.estado')
                ->find((int) $this->vinculacionComunidadId);
        }

        $comunidades = Comunidad::orderBy('com_nombre')->get();

        return view('livewire.vinculacion-manager', [
            'proyectos' => $proyectos,
            'vinculaciones' => $vinculaciones,
            'comunidadSeleccionada' => $comunidadSeleccionada,
            'comunidades' => $comunidades,
        ]);
    }
}
