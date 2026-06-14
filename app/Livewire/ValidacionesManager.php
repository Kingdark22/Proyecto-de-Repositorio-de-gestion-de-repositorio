<?php

namespace App\Livewire;

use App\Models\Proyecto;
use Livewire\Component;
use Livewire\WithPagination;

class ValidacionesManager extends Component
{
    use WithPagination;

    public $search = '';
    public $motivo_rechazo = '';
    public $selectedProjectId = null;
    public $selectedProject = null;
    public $viewMode = 'list';

    public function approve($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->aprobar();

        session()->flash('message', 'Proyecto aprobado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function openRejectModal($id)
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function openDetails($id)
    {
        $this->selectedProject = Proyecto::with(['tipo_publicacion', 'linea_investigacion', 'metodologia', 'tipo_investigacion', 'comunidad', 'documentos.componente'])->findOrFail($id);
        $this->viewMode = 'details';
        $this->dispatch('refresh-icons');
    }

    public function backToList()
    {
        $this->viewMode = 'list';
        $this->selectedProjectId = null;
        $this->selectedProject = null;
        $this->motivo_rechazo = '';
    }

    public function reject()
    {
        $this->validate([
            'motivo_rechazo' => 'required|min:10',
        ]);

        $proyecto = Proyecto::findOrFail($this->selectedProjectId);
        $proyecto->rechazar($this->motivo_rechazo);

        $this->backToList();
        session()->flash('message', 'Proyecto rechazado.');
        $this->dispatch('refresh-icons');
    }

    public function approveFromDetails($id)
    {
        $this->approve($id);
        $this->backToList();
    }

    public function rejectFromDetails($id)
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function with()
    {
        return [
            'proyectos' => Proyecto::pendientes($this->search)->latest()->paginate(10),
        ];
    }

    public function render()
    {
        return view('livewire.validaciones-manager', $this->with());
    }
}
