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

    // Vinculación (PNF-centrica para coordinador)
    public $selectedProgramaId = '';
    public $vinculacionRows = [];
    public $trayectosVinculacion = [];

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

    // ── Vinculación (Coordinador: PNF → Componentes) ──

    public function irAVinculacion(): void
    {
        $this->resetValidation();
        $this->resetFields();
        $this->selectedProgramaId = '';
        $this->vinculacionRows = [];
        $this->trayectosVinculacion = [];
        $this->viewMode = 'vinculacion';
    }

    public function updatedSelectedProgramaId(string $value): void
    {
        if ($value !== '') {
            $this->trayectosVinculacion = app(CatalogoRepository::class)
                ->trayectosPorPrograma((int) $value)
                ->toArray();
        } else {
            $this->trayectosVinculacion = [];
        }

        $this->cargarVinculacionRows();
    }

    protected function cargarVinculacionRows(): void
    {
        $this->vinculacionRows = [];

        if ($this->selectedProgramaId === '') return;

        $proCodigo = (int) $this->selectedProgramaId;

        $componentes = Componente::where('estado_logico', true)
            ->orderBy('nombre')
            ->get();

        $asignaciones = ComponentePrograma::where('pro_codigo', $proCodigo)
            ->get()
            ->groupBy('comp_codigo');

        foreach ($componentes as $comp) {
            $asigs = $asignaciones->get($comp->id, collect());

            $trayectos = [];
            foreach ($this->trayectosVinculacion as $tra) {
                $codigo = is_object($tra) ? $tra->tra_codigo : $tra['tra_codigo'];
                $asig = $asigs->firstWhere('tra_codigo', $codigo);
                $trayectos[$codigo] = [
                    'selected' => $asig !== null,
                    'cantidad' => $asig ? (int) ($asig->cantidad ?? 1) : 1,
                ];
            }

            $this->vinculacionRows[$comp->id] = [
                'comp_codigo' => $comp->id,
                'nombre' => $comp->nombre,
                'activo' => $asigs->isNotEmpty(),
                'trayectos' => $trayectos,
            ];
        }
    }

    public function toggleTrayecto(int $compCodigo, string $traCodigo): void
    {
        if (!isset($this->vinculacionRows[$compCodigo]['trayectos'][$traCodigo])) return;
        $this->vinculacionRows[$compCodigo]['trayectos'][$traCodigo]['selected'] =
            !$this->vinculacionRows[$compCodigo]['trayectos'][$traCodigo]['selected'];
        $this->vinculacionRows[$compCodigo]['activo'] = collect($this->vinculacionRows[$compCodigo]['trayectos'])
            ->contains('selected', true);
    }

    public function cambiarCantidadTrayecto(int $compCodigo, string $traCodigo, int $cantidad): void
    {
        if (isset($this->vinculacionRows[$compCodigo]['trayectos'][$traCodigo])) {
            $this->vinculacionRows[$compCodigo]['trayectos'][$traCodigo]['cantidad'] = max(1, $cantidad);
        }
    }

    public function guardarVinculacion(): void
    {
        if ($this->selectedProgramaId === '') {
            $this->safeDispatch('error', 'Debe seleccionar un PNF.');
            return;
        }

        $proCodigo = (int) $this->selectedProgramaId;

        ComponentePrograma::where('pro_codigo', $proCodigo)->delete();

        foreach ($this->vinculacionRows as $compCodigo => $row) {
            if (!$row['activo']) continue;
            foreach ($row['trayectos'] ?? [] as $traCodigo => $traData) {
                if (!($traData['selected'] ?? false)) continue;
                ComponentePrograma::create([
                    'comp_codigo' => (int) $compCodigo,
                    'pro_codigo' => $proCodigo,
                    'tra_codigo' => $traCodigo,
                    'cantidad' => max(1, (int) ($traData['cantidad'] ?? 1)),
                ]);
            }
        }

        $this->safeDispatch('success', 'Vinculación PNF → Componentes guardada exitosamente.');

        $this->viewMode = 'list';
        $this->safeRefreshIcons();
    }

    public function cancelarVinculacion(): void
    {
        $this->selectedProgramaId = '';
        $this->vinculacionRows = [];
        $this->trayectosVinculacion = [];
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
