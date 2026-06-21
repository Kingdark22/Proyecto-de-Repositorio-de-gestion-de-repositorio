<?php

namespace App\Livewire;

use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Repositories\CatalogoRepository;
use Livewire\Component;
use Livewire\WithPagination;

class ComponenteManager extends Component
{
    use WithPagination;

    public $search = '';
    public $viewMode = 'list';
    public $editingId = null;

    public $nombre = '';
    public $es_obligatorio = true;
    public $tipo_archivo = 'pdf';
    public $tamano_maximo_mb = 10;

    public $rows = [];

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

        // Todos los componentes activos
        $componentes = Componente::where('estado_logico', true)
            ->orderBy('nombre')
            ->get();

        // Asignaciones existentes para este PNF
        $asignaciones = ComponentePrograma::where('pro_codigo', $proCodigo)
            ->get()
            ->keyBy(function ($item) {
                return $item->comp_codigo . '_' . ($item->tra_codigo ?? '__todos__');
            });

        foreach ($componentes as $comp) {
            $key = $comp->id . '_' . '__todos__';
            $asig = $asignaciones->get($key);

            // También buscar por trayecto específico (tomamos la primera coincidencia)
            if (!$asig) {
                $asig = $asignaciones->first(function ($a) use ($comp) {
                    return (int) $a->comp_codigo === (int) $comp->id;
                });
            }

            $this->vinculacionRows[$comp->id] = [
                'comp_codigo' => $comp->id,
                'nombre' => $comp->nombre,
                'asignado' => $asig !== null,
                'tra_codigo' => $asig ? ($asig->tra_codigo ?? '') : '',
                'cantidad' => $asig ? (int) ($asig->cantidad ?? 1) : 1,
            ];
        }
    }

    public function toggleAsignacionVinculacion(int $compCodigo): void
    {
        if (!isset($this->vinculacionRows[$compCodigo])) return;

        $row = &$this->vinculacionRows[$compCodigo];
        $row['asignado'] = !$row['asignado'];

        if ($row['asignado']) {
            $row['tra_codigo'] = '';
            $row['cantidad'] = 1;
        } else {
            $row['tra_codigo'] = '';
            $row['cantidad'] = 1;
        }
    }

    public function cambiarTrayectoVinculacion(int $compCodigo, string $traCodigo): void
    {
        if (isset($this->vinculacionRows[$compCodigo])) {
            $this->vinculacionRows[$compCodigo]['tra_codigo'] = $traCodigo;
        }
    }

    public function cambiarCantidadVinculacion(int $compCodigo, int $cantidad): void
    {
        if (isset($this->vinculacionRows[$compCodigo])) {
            $this->vinculacionRows[$compCodigo]['cantidad'] = max(1, $cantidad);
        }
    }

    public function guardarVinculacion(): void
    {
        if ($this->selectedProgramaId === '') {
            session()->flash('message', 'Debe seleccionar un PNF.');
            return;
        }

        $proCodigo = (int) $this->selectedProgramaId;

        // Construir array para sincronizarAsignacionesPorPrograma
        $asignaciones = [];
        foreach ($this->vinculacionRows as $compCodigo => $row) {
            $asignaciones[(int) $compCodigo] = [
                'activo' => (bool) ($row['asignado'] ?? false),
                'tra_codigo' => !empty($row['tra_codigo']) ? $row['tra_codigo'] : null,
                'cantidad' => max(1, (int) ($row['cantidad'] ?? 1)),
            ];
        }

        app(CatalogoRepository::class)
            ->sincronizarAsignacionesPorPrograma($proCodigo, $asignaciones);

        session()->flash('message', 'Vinculación PNF → Componentes guardada exitosamente.');

        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
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
    }

    public function save()
    {
        $this->validate();

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
            session()->flash('message', 'Componente documental actualizado.');
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
            session()->flash('message', count($this->rows) . ' Componente(s) creado(s) con éxito.');
        }

        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $comp = Componente::find($id);
        if ($comp) {
            $comp->update(['estado_logico' => !$comp->estado_logico]);
            session()->flash('message', 'Estado lógico del componente actualizado.');
        }
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        Componente::find($id)?->delete();
        session()->flash('message', 'Regla de componente eliminada de la base de datos.');
        $this->dispatch('refresh-icons');
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
