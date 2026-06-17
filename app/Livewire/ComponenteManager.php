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

    // Estado de asignación PNF/Trayecto
    public $asignandoRowIndex = null;
    public $asignacionProCodigo = '';
    public $asignacionTraCodigo = '';
    public $trayectosAsignacion = [];

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

    /**
     * Inicia el proceso de asignación de PNF/Trayecto para una fila.
     */
    public function iniciarAsignacion(int $rowIndex): void
    {
        $this->asignandoRowIndex = $rowIndex;
        $this->asignacionProCodigo = '';
        $this->asignacionTraCodigo = '';
        $this->trayectosAsignacion = [];
    }

    /**
     * Cuando cambia el PNF seleccionado en la asignación, cargar trayectos.
     */
    public function updatedAsignacionProCodigo(string $value): void
    {
        if ($value !== '') {
            $this->trayectosAsignacion = app(CatalogoRepository::class)
                ->trayectosPorPrograma((int) $value)
                ->toArray();
        } else {
            $this->trayectosAsignacion = [];
        }
        $this->asignacionTraCodigo = '';
    }

    /**
     * Confirma la asignación actual y la agrega a la fila.
     */
    public function confirmarAsignacion(): void
    {
        if ($this->asignandoRowIndex === null || $this->asignacionProCodigo === '' || $this->asignacionTraCodigo === '') {
            return;
        }

        $proCodigo = (int) $this->asignacionProCodigo;
        $traCodigo = (string) $this->asignacionTraCodigo;

        // Verificar duplicado
        $existe = collect($this->rows[$this->asignandoRowIndex]['asignaciones'] ?? [])
            ->contains(fn ($a) => (int) ($a['pro_codigo'] ?? 0) === $proCodigo && ($a['tra_codigo'] ?? '') === $traCodigo);

        if ($existe) {
            $this->addError('asignacion', 'Esta asignación ya existe para este componente.');
            return;
        }

        $this->rows[$this->asignandoRowIndex]['asignaciones'][] = [
            'pro_codigo' => $proCodigo,
            'tra_codigo' => $traCodigo,
        ];

        $this->limpiarAsignacion();
    }

    /**
     * Elimina una asignación de una fila.
     */
    public function removerAsignacion(int $rowIndex, int $asigIndex): void
    {
        if (isset($this->rows[$rowIndex]['asignaciones'][$asigIndex])) {
            unset($this->rows[$rowIndex]['asignaciones'][$asigIndex]);
            $this->rows[$rowIndex]['asignaciones'] = array_values($this->rows[$rowIndex]['asignaciones']);
        }
    }

    /**
     * Cancela el proceso de asignación.
     */
    public function cancelarAsignacion(): void
    {
        $this->limpiarAsignacion();
    }

    protected function limpiarAsignacion(): void
    {
        $this->asignandoRowIndex = null;
        $this->asignacionProCodigo = '';
        $this->asignacionTraCodigo = '';
        $this->trayectosAsignacion = [];
    }

    public function create()
    {
        $this->resetValidation();
        $this->resetFields();
        $this->limpiarAsignacion();

        $this->rows = [[
            'id' => null,
            'nombre' => '',
            'es_obligatorio' => true,
            'tipo_archivo' => 'pdf',
            'tamano_maximo_mb' => 4,
            'asignaciones' => [],
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
            'asignaciones' => [],
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
        $this->limpiarAsignacion();

        $comp = Componente::find($id);

        if (!$comp) {
            abort(404);
        }

        // Cargar asignaciones existentes
        $asignaciones = ComponentePrograma::where('comp_codigo', $comp->id)
            ->get(['pro_codigo', 'tra_codigo'])
            ->map(fn ($a) => [
                'pro_codigo' => (int) $a->pro_codigo,
                'tra_codigo' => (string) ($a->tra_codigo ?? ''),
            ])
            ->toArray();

        $this->rows = [
            [
                'id' => $comp->id,
                'nombre' => $comp->nombre,
                'es_obligatorio' => (bool) $comp->es_obligatorio,
                'tipo_archivo' => $comp->tipo_archivo ?? 'pdf',
                'tamano_maximo_mb' => $comp->tamano_maximo_mb ?? 10,
                'asignaciones' => $asignaciones,
            ]
        ];

        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->resetFields();
        $this->limpiarAsignacion();
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

        // Validate that each component has at least one PNF/Trayecto assignment
        foreach ($this->rows as $idx => $row) {
            if (empty($row['asignaciones'])) {
                $this->addError('rows', "El componente '{$row['nombre']}' debe tener al menos una asignación a PNF + Trayecto.");
                return;
            }
        }

        $catalogoRepo = app(CatalogoRepository::class);

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

                        $catalogoRepo->sincronizarAsignaciones($comp->id, $row['asignaciones']);
                    }
                } else {
                    $comp = Componente::create([
                        'nombre' => $row['nombre'],
                        'es_obligatorio' => $row['es_obligatorio'],
                        'tipo_archivo' => $row['tipo_archivo'] ?? 'pdf',
                        'tamano_maximo_mb' => $row['tamano_maximo_mb'] ?? 10,
                        'estado_logico' => true,
                    ]);
                    $catalogoRepo->sincronizarAsignaciones($comp->id, $row['asignaciones']);
                }
            }
            session()->flash('message', 'Componente documental actualizado.');
        } else {
            foreach ($this->rows as $row) {
                $comp = Componente::create([
                    'nombre' => $row['nombre'],
                    'es_obligatorio' => $row['es_obligatorio'],
                    'tipo_archivo' => $row['tipo_archivo'] ?? 'pdf',
                    'tamano_maximo_mb' => $row['tamano_maximo_mb'] ?? 10,
                    'estado_logico' => true,
                ]);
                $catalogoRepo->sincronizarAsignaciones($comp->id, $row['asignaciones']);
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
