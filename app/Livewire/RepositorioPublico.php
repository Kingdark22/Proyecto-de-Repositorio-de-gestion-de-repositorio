<?php

namespace App\Livewire;

use App\Helpers\DualDatabase;
use App\Models\Proyecto;
use App\Services\AcademicCatalog;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class RepositorioPublico extends Component
{
    use WithPagination;

    public $search = '';
    public $filterPrograma = '';
    public $filterLapso = '';

    public function with()
    {
        return [
            'proyectos' => Proyecto::with('documentos')->busquedaPublica(
                $this->search,
                (int) $this->filterPrograma ?: null,
                $this->filterLapso
            )->latest()->paginate(9),
            'programas' => app(AcademicCatalog::class)->programasForSelect(),
            'lapsos' => Cache::remember('repositorio_lapsos_'.DualDatabase::academicConnection(), now()->addMinutes(60),
                fn() => DualDatabase::table('lapso_academico')
                    ->orderByDesc('lap_codigo')
                    ->limit(10)
                    ->get(['lap_codigo', 'lap_nombre'])
            ),
        ];
    }

    public function render()
    {
        return view('livewire.repositorio-publico', $this->with());
    }
}
