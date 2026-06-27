<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Repositories\CatalogoRepository;
use App\Services\UnicidadNombreService;
use Livewire\Component;
use Livewire\WithPagination;

class ComponenteManager extends Component
{
    use WithPagination;
    use WithSafeNotify;

    public $search = '';
    public $viewMode = 'list';
    public $editingId = null;

    public $nombre = '';
    public $es_obligatorio = true;
    public $tipo_archivo = 'pdf';
    public $tamano_maximo_mb = 10;

    public $rows = [];

    public array $rowsNombreStatus = [];

    public function updated($propertyName, $value): void
    {
        if (preg_match('/^rows\.(\d+)\.nombre$/', $propertyName, $m)) {
            $index = (int) $m[1];
            if (strlen(trim((string) $value)) < 3) {
                $this->rowsNombreStatus[$index] = 'vacio';
                $this->resetValidation("rows.$index.nombre");
                return;
            }
            $this->rowsNombreStatus[$index] = app(UnicidadNombreService::class)->check(
                Componente::class,
                'nombre',
                $value,
                !empty($this->rows[$index]['id']) ? (int) $this->rows[$index]['id'] : null,
            ) ? 'disponible' : 'no_disponible';
            if ($this->rowsNombreStatus[$index] === 'disponible') {
                $this->resetValidation("rows.$index.nombre");
            }
        }
    }

    // Vinculación (Componente-centrica para coordinador)
    public $selectedComponenteId = '';
    public $vinculacionPnfRows = [];
    public $componentesDisponiblesVinculacion = [];
    public $trayectosDisponibles = [];

    protected function rules()
    {
        return [
            'rows.*.nombre' => 'required|min:3',
            'rows.*.es_obligatorio' => 'boolean',
            'rows.*.tipo_archivo' => 'required|string|max:100',
            'rows.*.tamano_maximo_mb' => 'required|integer|min:1|max:200',
        ];
    }

    protected $messages = [
        'rows.*.nombre.required' => 'Debe nombrar el documento en esta fila.',
        'rows.*.tipo_archivo.required' => 'Seleccione el tipo de archivo.',
        'rows.*.tamano_maximo_mb.required' => 'Indique el tamaño máximo permitido.',
        'rows.*.tamano_maximo_mb.max' => 'El tamaño máximo no puede superar 200 MB.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ── Vinculación (Coordinador: Componente → PNFs + Trayectos) ──

    public function irAVinculacion(): void
    {
        $this->resetValidation();
        $this->resetFields();
        $this->selectedComponenteId = '';
        $this->vinculacionPnfRows = [];
        $this->componentesDisponiblesVinculacion = Componente::where('estado_logico', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $this->trayectosDisponibles = [];
        $this->viewMode = 'vinculacion';
    }

    public function updatedSelectedComponenteId(string $value): void
    {
        $this->vinculacionPnfRows = [];
        $this->trayectosDisponibles = [];

        if ($value === '') return;

        $compCodigo = (int) $value;
        $catalogoRepo = app(CatalogoRepository::class);
        $programas = $catalogoRepo->programasDisponibles();
        $trayectos = $catalogoRepo->trayectosPorPrograma(0);
        $asignaciones = ComponentePrograma::where('comp_codigo', $compCodigo)->get();

        $this->trayectosDisponibles = $trayectos->toArray();

        foreach ($programas as $prog) {
            $proCodigo = (int) $prog->pro_codigo;
            $asigsPorPnf = $asignaciones->where('pro_codigo', $proCodigo);
            $trayectosData = [];
            foreach ($trayectos as $tra) {
                $traCodigo = (string) $tra->tra_codigo;
                $asig = $asigsPorPnf->firstWhere('tra_codigo', $traCodigo);
                $trayectosData[$traCodigo] = [
                    'selected' => $asig !== null,
                    'cantidad' => $asig ? (int) ($asig->cantidad ?? 1) : 1,
                ];
            }
            $this->vinculacionPnfRows[$proCodigo] = [
                'pro_codigo' => $proCodigo,
                'pro_siglas' => $prog->pro_siglas ?? $prog->pro_nombre,
                'activo' => $asigsPorPnf->isNotEmpty(),
                'trayectos' => $trayectosData,
            ];
        }
    }

    public function togglePnfVinculacion(string $proCodigo): void
    {
        if (!isset($this->vinculacionPnfRows[$proCodigo])) return;
        $activo = !$this->vinculacionPnfRows[$proCodigo]['activo'];
        $this->vinculacionPnfRows[$proCodigo]['activo'] = $activo;
        foreach ($this->vinculacionPnfRows[$proCodigo]['trayectos'] as $traCodigo => $traData) {
            $this->vinculacionPnfRows[$proCodigo]['trayectos'][$traCodigo]['selected'] = $activo;
        }
    }

    public function toggleTrayectoPnf(string $proCodigo, string $traCodigo): void
    {
        if (!isset($this->vinculacionPnfRows[$proCodigo]['trayectos'][$traCodigo])) return;
        $this->vinculacionPnfRows[$proCodigo]['trayectos'][$traCodigo]['selected'] =
            !$this->vinculacionPnfRows[$proCodigo]['trayectos'][$traCodigo]['selected'];
        // auto-activar/desactivar el PNF según si hay algún trayecto seleccionado
        $tieneSeleccion = collect($this->vinculacionPnfRows[$proCodigo]['trayectos'])
            ->contains('selected', true);
        $this->vinculacionPnfRows[$proCodigo]['activo'] = $tieneSeleccion;
    }

    public function cambiarCantidadPnf(string $proCodigo, string $traCodigo, int $cantidad): void
    {
        if (isset($this->vinculacionPnfRows[$proCodigo]['trayectos'][$traCodigo])) {
            $this->vinculacionPnfRows[$proCodigo]['trayectos'][$traCodigo]['cantidad'] = max(1, $cantidad);
        }
    }

    public function guardarVinculacion(): void
    {
        if ($this->selectedComponenteId === '') {
            $this->safeDispatch('error', 'Debe seleccionar un componente.');
            return;
        }

        $compCodigo = (int) $this->selectedComponenteId;

        // Eliminar todas las asignaciones existentes del componente
        ComponentePrograma::where('comp_codigo', $compCodigo)->delete();

        // Crear las nuevas asignaciones
        foreach ($this->vinculacionPnfRows as $proCodigo => $row) {
            if (!$row['activo']) continue;
            foreach ($row['trayectos'] ?? [] as $traCodigo => $traData) {
                if (!($traData['selected'] ?? false)) continue;
                ComponentePrograma::create([
                    'comp_codigo' => $compCodigo,
                    'pro_codigo' => (int) $proCodigo,
                    'tra_codigo' => $traCodigo,
                    'cantidad' => max(1, (int) ($traData['cantidad'] ?? 1)),
                ]);
            }
        }

        $this->safeDispatch('success', 'Vinculación del componente guardada exitosamente.');
        $this->viewMode = 'list';
        $this->safeRefreshIcons();
    }

    public function cancelarVinculacion(): void
    {
        $this->selectedComponenteId = '';
        $this->vinculacionPnfRows = [];
        $this->componentesDisponiblesVinculacion = [];
        $this->trayectosDisponibles = [];
        $this->viewMode = 'list';
    }

    // ── CRUD Componentes ──

    public function create()
    {
        $this->resetValidation();
        $this->resetFields();

        $this->rows = [[
            'id' => null,
            'nombre' => '',
            'es_obligatorio' => true,
            'tipo_archivo' => 'pdf',
            'tamano_maximo_mb' => 4,
        ]];
        $this->rowsNombreStatus = [0 => null];

        $this->viewMode = 'form';
    }

    public function addRow()
    {
        $this->rows[] = [
            'id' => null,
            'nombre' => '',
            'es_obligatorio' => true,
            'tipo_archivo' => 'pdf',
            'tamano_maximo_mb' => 4,
        ];
    }

    public function removeRow($index)
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
        }
    }

    public function edit($id)
    {
        $this->resetValidation();
        $this->editingId = $id;

        $comp = Componente::find($id);

        if (!$comp) {
            abort(404);
        }

        $this->rows = [
            [
                'id' => $comp->id,
                'nombre' => $comp->nombre,
                'es_obligatorio' => (bool) $comp->es_obligatorio,
                'tipo_archivo' => $comp->tipo_archivo ?? 'pdf',
                'tamano_maximo_mb' => $comp->tamano_maximo_mb ?? 10,
            ]
        ];
        $this->rowsNombreStatus = [0 => 'disponible'];

        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->resetFields();
        $this->viewMode = 'list';
    }

    public function resetFields()
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->es_obligatorio = true;
        $this->tipo_archivo = 'pdf';
        $this->tamano_maximo_mb = 10;
        $this->rows = [];
        $this->rowsNombreStatus = [];
    }

    public function save()
    {
        $this->validate();

        foreach ($this->rowsNombreStatus as $status) {
            if ($status === 'no_disponible') {
                $this->addError('rows', 'Uno o más nombres de componente ya están en uso.');
                return;
            }
        }

        // Validate unique names within the submission (case-insensitive)
        $nombres = array_map(fn($r) => mb_strtolower(trim($r['nombre'])), $this->rows);
        if (count($nombres) !== count(array_unique($nombres))) {
            $this->addError('rows', 'No puede haber nombres de componentes duplicados.');
            return;
        }

        // Validate unique names globally
        foreach ($this->rows as $row) {
            $query = Componente::whereRaw('TRIM(comp_nombre) = ?', [trim($row['nombre'])]);
            if (!empty($row['id'])) {
                $query->where('id', '!=', $row['id']);
            }
            if ($query->exists()) {
                $this->addError('rows', "El componente '{$row['nombre']}' ya existe.");
                return;
            }
        }

        if ($this->editingId) {
            foreach ($this->rows as $row) {
                if (!empty($row['id'])) {
                    $comp = Componente::find($row['id']);
                    if ($comp) {
                        $comp->update([
                            'nombre' => $row['nombre'],
                            'es_obligatorio' => $row['es_obligatorio'],
                            'tipo_archivo' => $row['tipo_archivo'] ?? 'pdf',
                            'tamano_maximo_mb' => $row['tamano_maximo_mb'] ?? 10,
                        ]);
                    }
                } else {
                    Componente::create([
                        'nombre' => $row['nombre'],
                        'es_obligatorio' => $row['es_obligatorio'],
                        'tipo_archivo' => $row['tipo_archivo'] ?? 'pdf',
                        'tamano_maximo_mb' => $row['tamano_maximo_mb'] ?? 10,
                        'estado_logico' => true,
                    ]);
                }
            }
            $this->safeDispatch('success', 'Componente documental actualizado.');
        } else {
            foreach ($this->rows as $row) {
                Componente::create([
                    'nombre' => $row['nombre'],
                    'es_obligatorio' => $row['es_obligatorio'],
                    'tipo_archivo' => $row['tipo_archivo'] ?? 'pdf',
                    'tamano_maximo_mb' => $row['tamano_maximo_mb'] ?? 10,
                    'estado_logico' => true,
                ]);
            }
            $n = count($this->rows);
            $this->safeDispatch('success', $n . ' componente' . ($n !== 1 ? 's' : '') . ' creado' . ($n !== 1 ? 's' : '') . ' con éxito.');
        }

        $this->viewMode = 'list';
        $this->safeRefreshIcons();
    }

    public function toggleStatus($id)
    {
        $comp = Componente::find($id);
        if ($comp) {
            $comp->update(['estado_logico' => !$comp->estado_logico]);
            $this->safeDispatch('success', 'Estado del componente actualizado.');
        }
        $this->safeRefreshIcons();
    }

    public function delete($id)
    {
        Componente::find($id)?->delete();
        $this->safeDispatch('success', 'Componente eliminado correctamente.');
        $this->safeRefreshIcons();
    }

    public function with()
    {
        $query = Componente::query();

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('nombre', 'ILIKE', $this->search . '%');
            });
        }

        $items = $query->with('programas')->latest('id')->paginate(10);

        $catalogoRepo = app(CatalogoRepository::class);

        return [
            'listaRegistros' => $items,
            'programasDisponibles' => $catalogoRepo->programasDisponibles(),
        ];
    }

    public function render()
    {
        return view('livewire.componente-manager', $this->with());
    }
}
