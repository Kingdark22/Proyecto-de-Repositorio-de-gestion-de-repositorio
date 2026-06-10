<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Services\AcademicCatalog;
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
            'proyectos' => Proyecto::busquedaPublica(
                $this->search,
                (int) $this->filterPrograma ?: null,
                $this->filterLapso
            )->latest()->paginate(9),
            'programas' => app(AcademicCatalog::class)->programasForSelect(),
        ];
    }

    public function render()
    {
        return view('livewire.repositorio-publico', $this->with());
    }
}
