<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Models\LineaInvestigacion;
use App\Services\UnicidadNombreService;
use App\Helpers\DbHelper;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LineaInvestigacionManager extends Component
{
    use WithPagination;
    use WithSafeNotify;

    public $nombre_investigacion = '';
    public $descripcion = '';
    public $area_de_investigacion = '';
    public $programa_id = '';
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    public ?string $nombreStatus = null;

    public function updatedNombreInvestigacion(): void
    {
        if (strlen(trim($this->nombre_investigacion)) < 3) {
            $this->nombreStatus = null;
            $this->resetValidation('nombre_investigacion');
            return;
        }
        $this->nombreStatus = app(UnicidadNombreService::class)->check(
            LineaInvestigacion::class,
            'nombre_investigacion',
            $this->nombre_investigacion,
            $this->editingId,
        ) ? 'disponible' : 'no_disponible';
        if ($this->nombreStatus === 'disponible') {
            $this->resetValidation('nombre_investigacion');
        }
    }

    protected $rules = [
        'nombre_investigacion' => 'required|min:3|max:255',
        'descripcion' => 'required|max:500',
        'area_de_investigacion' => 'required|max:255',
        'programa_id' => 'required',
    ];

    public function messages()
    {
        return [
            'nombre_investigacion.required' => 'El nombre de la línea de investigación es obligatorio.',
            'nombre_investigacion.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre_investigacion.max' => 'El nombre no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no debe exceder los 500 caracteres.',
            'area_de_investigacion.required' => 'El área académica es obligatoria.',
            'area_de_investigacion.max' => 'El área no debe exceder los 255 caracteres.',
            'programa_id.required' => 'Seleccionar un Programa es obligatorio.',
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
        $item = LineaInvestigacion::find($id);
        $this->nombre_investigacion = $item->nombre_investigacion;
        $this->descripcion = $item->descripcion;
        $this->area_de_investigacion = $item->area_de_investigacion;
        $this->programa_id = $item->programa_id;
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
        $this->nombre_investigacion = '';
        $this->descripcion = '';
        $this->area_de_investigacion = '';
        $this->programa_id = '';
        $this->editingId = null;
        $this->nombreStatus = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->nombreStatus === 'no_disponible') {
            $this->addError('nombre_investigacion', 'Este nombre ya está en uso.');
            return;
        }

        LineaInvestigacion::guardar(
            [
                'nombre_investigacion' => $this->nombre_investigacion,
                'descripcion' => $this->descripcion,
                'area_de_investigacion' => $this->area_de_investigacion,
                'programa_id' => $this->programa_id,
                'activo' => true,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        $this->safeDispatch('success', $this->editingId ? 'Línea de Investigación actualizada con éxito.' : 'Línea de Investigación registrada con éxito.');
        $this->safeRefreshIcons();
    }

    public function toggleStatus($id)
    {
        $item = LineaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        $this->safeDispatch('success', $item->activo ? 'Línea habilitada correctamente.' : 'Línea deshabilitada correctamente.');
        $this->safeRefreshIcons();
    }

    public function delete($id)
    {
        LineaInvestigacion::find($id)->delete();
        $this->safeDispatch('success', 'Línea de Investigación eliminada correctamente.');
        $this->safeRefreshIcons();
    }

    public function with()
    {
        $items = LineaInvestigacion::where(function ($q) {
                $q->where('nombre_investigacion', 'ILIKE', $this->search . '%')
                  ->orWhere('area_de_investigacion', 'ILIKE', $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        $this->cargarNombresPrograma($items);

        return [
            'items' => $items,
            'programas' => app(\App\Services\AcademicCatalog::class)->programasForSelect(),
        ];
    }

    protected function cargarNombresPrograma($items): void
    {
        $ids = $items->pluck('programa_id')->filter()->unique()->values()->toArray();
        if (empty($ids)) {
            return;
        }
        try {
            $progs = DB::connection(DbHelper::connection())
                ->table('programa')
                ->whereIn('pro_codigo', $ids)
                ->pluck('pro_nombre', 'pro_codigo');
            $items->each(function ($item) use ($progs) {
                $item->setAttribute('nombre_programa_cache', $progs[$item->programa_id] ?? "Programa #{$item->programa_id}");
            });
        } catch (\Throwable) {
        }
    }

    public function render()
    {
        return view('livewire.linea-investigacion-manager', $this->with());
    }
}
