<?php

namespace App\Livewire;

use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Services\AcademicCatalog;
use App\Helpers\DbHelper;
use Illuminate\Support\Facades\DB;
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


    public $rows = [];

    // PNF assignment modal
    public $showPnfModal = false;
    public $pnfComponenteId = null;
    public $pnfSeleccionados = [];

    protected function rules()
    {
        return [
            'rows.*.nombre' => 'required|min:3',
            'rows.*.es_obligatorio' => 'boolean',
        ];
    }

    protected $messages = [
        'rows.*.nombre.required' => 'Debe nombrar el documento en esta fila.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()

    {
        $this->resetValidation();
        $this->resetFields();

        $this->rows = [['id' => null, 'nombre' => '', 'es_obligatorio' => true]];

        $this->viewMode = 'form';
    }

    public function addRow()
    {
        $this->rows[] = ['id' => null, 'nombre' => '', 'es_obligatorio' => true];
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

        // Validate unique names against existing DB records
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
            $createdCount = 0;
            foreach ($this->rows as $row) {
                if (!empty($row['id'])) {
                    $comp = Componente::find($row['id']);
                    if ($comp) {
                        $comp->update([
                            'nombre' => $row['nombre'],
                            'es_obligatorio' => $row['es_obligatorio'],
                        ]);
                    }
                } else {
                    $comp = Componente::create([
                        'nombre' => $row['nombre'],
                        'es_obligatorio' => $row['es_obligatorio'],
                        'estado_logico' => true,
                    ]);
                    $createdCount++;
                }
            }

            if ($createdCount > 0) {
                session()->flash('message', "Componente actualizado y {$createdCount} nuevos componentes agregados con éxito.");
            } else {
                session()->flash('message', 'Componente documental actualizado.');
            }
        } else {
            foreach ($this->rows as $row) {
                Componente::create([
                    'nombre' => $row['nombre'],
                    'es_obligatorio' => $row['es_obligatorio'],
                    'estado_logico' => true,
                ]);
            }
            session()->flash('message', count($this->rows) . ' Componentes creados con éxito.');
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

        $items = $query->latest('id')->paginate(10);

        return [
            'listaRegistros' => $items,
            'programas' => app(AcademicCatalog::class)->programasForSelect(),
        ];
    }

    public function render()
    {
        return view('livewire.componente-manager', $this->with());
    }
}
