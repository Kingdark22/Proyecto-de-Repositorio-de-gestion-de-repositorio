<?php

namespace App\Livewire;

use App\Models\ComentarioProyecto;
use App\Models\Organizacion;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ProyectosPublicadosManager extends Component
{
    public $selectedPubId = null;
    public $nuevoComentario = '';

    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public array $selectedProjects = [];
    public bool $showEmailPanel = false;

    public string $searchOrg = '';
    public array $selectedEmails = [];
    public string $emailSubject = '';
    public string $emailBody = '';
    public bool $selectAllOrgs = false;

    public string $search = '';
    public string $filterComunidadId = '';

    public function seleccionar($pubId): void
    {
        $this->selectedPubId = $pubId;
        $this->nuevoComentario = '';
    }

    public function cerrar(): void
    {
        $this->selectedPubId = null;
        $this->nuevoComentario = '';
    }

    public function comentar(): void
    {
        $this->validate([
            'nuevoComentario' => 'required|min:3|max:1000',
        ]);

        if (!$this->selectedPubId) {
            return;
        }

        $pub = Proyecto::find($this->selectedPubId);
        if (!$pub) {
            return;
        }

        ComentarioProyecto::create([
            'descripcion' => trim($this->nuevoComentario),
            'proyecto_id' => $pub->id,
        ]);

        $this->nuevoComentario = '';
        $this->tipoMensaje = 'success';
        $this->mensaje = 'Comentario agregado correctamente.';
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function toggleProject($id): void
    {
        if (in_array($id, $this->selectedProjects)) {
            $this->selectedProjects = array_values(array_diff($this->selectedProjects, [$id]));
        } else {
            $this->selectedProjects[] = (int) $id;
        }
    }

    public function selectAll(): void
    {
        $this->selectedProjects = $this->proyectosQuery()->pluck('pry_codigo')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedProjects = [];
    }

    public function openEmailPanel(): void
    {
        $this->showEmailPanel = true;
        $this->searchOrg = '';
        $this->selectedEmails = [];
        $this->emailSubject = 'Proyectos aprobados - Sistema de Gestión';
        $this->emailBody = '';
        $this->selectAllOrgs = false;
    }

    public function closeEmailPanel(): void
    {
        $this->showEmailPanel = false;
    }

    public function updatedSearchOrg(): void
    {
        $this->selectAllOrgs = false;
    }

    public function toggleSelectAllEmails(): void
    {
        $this->selectAllOrgs = !$this->selectAllOrgs;
        if ($this->selectAllOrgs) {
            $organizations = $this->loadOrganizations();
            $allEmails = [];
            foreach ($organizations as $org) {
                if ($org->correo) {
                    $allEmails[] = $org->correo;
                }
                foreach ($org->contactos as $contacto) {
                    if ($contacto->oco_correo) {
                        $allEmails[] = $contacto->oco_correo;
                    }
                }
            }
            $this->selectedEmails = array_values(array_unique($allEmails));
        } else {
            $this->selectedEmails = [];
        }
    }

    public function toggleEmail($index): void
    {
        $idx = (int) $index;
        $emails = $this->buildEmailList();
        if (!isset($emails[$idx])) {
            return;
        }
        $email = $emails[$idx];
        if (in_array($email, $this->selectedEmails)) {
            $this->selectedEmails = array_values(array_diff($this->selectedEmails, [$email]));
        } else {
            $this->selectedEmails[] = $email;
        }
    }

    protected function loadOrganizations()
    {
        $query = Organizacion::with('contactos');
        if ($this->searchOrg !== '') {
            $term = '%' . $this->searchOrg . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('correo', 'like', $term)
                    ->orWhere('rif', 'like', $term);
            });
        }
        return $query->orderBy('nombre')->get();
    }

    protected function buildEmailList($orgs = null): array
    {
        $orgs ??= $this->loadOrganizations();
        $list = [];
        foreach ($orgs as $org) {
            if ($org->correo) {
                $list[] = $org->correo;
            }
            foreach ($org->contactos as $contacto) {
                if ($contacto->oco_correo) {
                    $list[] = $contacto->oco_correo;
                }
            }
        }
        return $list;
    }

    public function sendProjects(): void
    {
        $this->validate([
            'selectedProjects' => 'required|array|min:1',
            'selectedEmails' => 'required|array|min:1',
            'emailSubject' => 'required|min:5|max:255',
            'emailBody' => 'nullable|max:5000',
        ], [
            'selectedProjects.required' => 'Debe seleccionar al menos un proyecto.',
            'selectedEmails.required' => 'Debe seleccionar al menos un destinatario.',
            'emailSubject.required' => 'El asunto es obligatorio.',
        ]);

        $proyectos = Proyecto::whereIn('pry_codigo', $this->selectedProjects)
            ->where('estado_validacion', 'aprobado')
            ->with('comunidad')
            ->get();

        Proyecto::precargarTitulos($proyectos);

        if ($proyectos->isEmpty()) {
            $this->tipoMensaje = 'error';
            $this->mensaje = 'No se encontraron proyectos válidos para enviar.';
            return;
        }

        try {
            $html = $this->buildEmailHtml($proyectos);

            Mail::html($html, function ($message) use ($proyectos) {
                $message->to($this->selectedEmails)
                    ->subject($this->emailSubject);

                foreach ($proyectos as $proyecto) {
                    if ($proyecto->archivo_path) {
                        $path = storage_path('app/public/' . $proyecto->archivo_path);
                        if (file_exists($path)) {
                            $message->attach($path, [
                                'as' => 'proyecto_' . $proyecto->id . '.pdf',
                                'mime' => 'application/pdf',
                            ]);
                        }
                    }
                    $docs = $proyecto->documentos ?? [];
                    if (is_array($docs)) {
                        foreach ($docs as $i => $doc) {
                            if (!empty($doc['archivo_path'])) {
                                $docPath = storage_path('app/public/' . $doc['archivo_path']);
                                if (file_exists($docPath)) {
                                    $compName = !empty($doc['componente_nombre']) ? $doc['componente_nombre'] : 'documento';
                                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $compName) . '_' . ($i + 1) . '.pdf';
                                    $message->attach($docPath, [
                                        'as' => $safeName,
                                        'mime' => 'application/pdf',
                                    ]);
                                }
                            }
                        }
                    }
                }
            });

            $this->tipoMensaje = 'success';
            $this->mensaje = 'Proyectos enviados correctamente a ' . count($this->selectedEmails) . ' destinatario(s).';
            $this->showEmailPanel = false;
            $this->selectedEmails = [];
        } catch (\Throwable $e) {
            $this->tipoMensaje = 'error';
            $this->mensaje = 'Error al enviar: ' . $e->getMessage();
        }
    }

    protected function buildEmailHtml($proyectos): string
    {
        $html = '<html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;font-size:13px;color:#333;">';

        $html .= '<div style="background:#8b0000;color:#fff;padding:12px;border-radius:6px;margin-bottom:15px;">';
        $html .= '<h2 style="margin:0;font-size:16px;">Sistema de Gesti&oacute;n de Proyectos</h2>';
        $html .= '<p style="margin:4px 0 0 0;font-size:12px;opacity:0.9;">Proyectos aprobados</p>';
        $html .= '</div>';

        if (trim($this->emailBody) !== '') {
            $html .= '<div style="background:#f9f9f9;border:1px solid #ddd;border-radius:4px;padding:12px;margin-bottom:15px;">';
            $html .= nl2br(e($this->emailBody));
            $html .= '</div>';
        }

        $html .= '<p style="font-size:12px;color:#666;">Se adjuntan los proyectos seleccionados en formato PDF.</p>';

        $html .= '<hr style="border:none;border-top:1px solid #ddd;margin:20px 0;">';
        $html .= '<p style="font-size:11px;color:#999;text-align:center;">Sistema de Gesti&oacute;n de Proyectos</p>';
        $html .= '</body></html>';

        return $html;
    }

    protected function proyectosQuery()
    {
        $query = Proyecto::with('comunidad')
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);

        if ($this->filterComunidadId !== '') {
            $query->where('com_codigo', (int) $this->filterComunidadId);
        }

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                try {
                    $q->whereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$search]);
                } catch (\Throwable) {
                    $term = '%' . $search . '%';
                    $q->whereRaw('pry_resumen ILIKE ?', [$term]);
                }
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function render()
    {
        $proyectos = $this->proyectosQuery()->get();

        Proyecto::precargarTitulos($proyectos);

        $selectedProyecto = null;
        $comentarios = collect();
        if ($this->selectedPubId) {
            $selectedProyecto = Proyecto::find($this->selectedPubId);
            if (!$selectedProyecto) {
                $this->selectedPubId = null;
            } else {
                Proyecto::precargarTitulos(collect([$selectedProyecto]));
                $comentarios = ComentarioProyecto::where('proyecto_id', $selectedProyecto->id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        $comunidades = \App\Models\Comunidad::orderBy('nombre')->get();

        $organizations = $this->showEmailPanel ? $this->loadOrganizations() : collect();

        $emailList = $this->showEmailPanel ? $this->buildEmailList($organizations) : [];

        return view('livewire.proyectos-publicados-manager', [
            'proyectos' => $proyectos,
            'selectedProyecto' => $selectedProyecto,
            'comentarios' => $comentarios,
            'comunidades' => $comunidades,
            'organizations' => $organizations,
            'emailList' => $emailList,
        ]);
    }
}
