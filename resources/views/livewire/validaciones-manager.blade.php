<div>
    <style>
        .vm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0.5rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }
        .vm-btn:hover {
            transform: translateY(-1px);
        }
        .vm-btn-approve {
            background: #198754;
            color: #fff;
        }
        .vm-btn-reject {
            background: #dc3545;
            color: #fff;
        }
        .vm-btn-details {
            background: #0d6efd;
            color: #fff;
        }
        .vm-btn-back {
            background: #6c757d;
            color: #fff;
        }
    </style>

    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Validaci&oacute;n de Proyectos</h2>

    @if ($viewMode === 'list')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Proyectos pendientes de validaci&oacute;n</legend>
            <div style="margin-bottom: 10px;">
                <input wire:model.live.debounce.300ms="search" type="text" style="width: 300px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Buscar proyecto...">
            </div>
            <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold; text-align: center;">
                        <th width="30%">T&iacute;tulo</th>
                        <th width="25%">Equipo</th>
                        <th width="15%">Estado</th>
                        <th width="30%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($proyectos as $p)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};">
                            <td style="padding: 5px; font-weight: bold;">{{ $p->titulo }}</td>
                            <td style="padding: 5px;">{{ $p->equipo_resumen }}</td>
                            <td align="center" style="padding: 5px;">
                                @if($p->estado_validacion === 'pendiente')
                                    <span style="color: #d4a017; font-weight: bold;">Pendiente</span>
                                @elseif($p->estado_validacion === 'completado')
                                    <span style="color: #2e7d32; font-weight: bold;">Completado</span>
                                @endif
                            </td>
                            <td align="center" style="padding: 5px;">
                                <button type="button" wire:click="approve({{ $p->id }})" onclick="return confirm('¿Aprobar este proyecto?')" class="vm-btn vm-btn-approve">Aprobar</button>
                                <button type="button" wire:click="openRejectModal({{ $p->id }})" class="vm-btn vm-btn-reject">Rechazar</button>
                                <button type="button" wire:click="openDetails({{ $p->id }})" class="vm-btn vm-btn-details">Detalles</button>
                            </td>
                        </tr>
                    @endforeach
                    @if($proyectos->isEmpty())
                        <tr>
                            <td colspan="4" align="center" style="padding: 20px;">No hay proyectos pendientes de validaci&oacute;n.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $proyectos->links() }}</div>
        </fieldset>
    @elseif($viewMode === 'details' && $selectedProject)
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Detalles del proyecto</legend>
            <h3 style="margin: 5px 0; font-size: 16px; font-weight: bold;">{{ $selectedProject->titulo }}</h3>
            <p style="font-size: 13px;"><b>Equipo:</b> {{ $selectedProject->equipo_resumen }}</p>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin: 15px 0;">
                <legend style="font-weight: bold; font-size: 12px;">Resumen</legend>
                <div style="font-size: 14px; text-align: justify;">{{ $selectedProject->resumen }}</div>
            </fieldset>
            @if($selectedProject->documentos->isNotEmpty())
                <div style="margin-top: 10px; font-size: 13px;">
                    <b>Documentos:</b><br>
                    @foreach($selectedProject->documentos as $doc)
                        <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank" style="color: #0000EE;">[{{ $doc->componente?->nombre ?? 'Documento' }}]</a><br>
                    @endforeach
                </div>
            @endif
            <div style="text-align: center; margin-top: 20px; border-top: 1px solid #CCC; padding-top: 15px;">
                @if(in_array($selectedProject->estado_validacion, ['pendiente', 'completado']))
                    <button type="button" wire:click="approveFromDetails({{ $selectedProject->id }})" onclick="return confirm('¿Aprobar?')" class="vm-btn vm-btn-approve">Aprobar</button>
                    <button type="button" wire:click="rejectFromDetails({{ $selectedProject->id }})" class="vm-btn vm-btn-reject">Rechazar</button>
                @endif
                <button type="button" wire:click="backToList" class="vm-btn vm-btn-back">Volver</button>
            </div>
        </fieldset>
    @elseif($viewMode === 'reject')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Motivo de rechazo</legend>
            <div style="margin-bottom: 15px; font-size: 12px;">Indique la justificaci&oacute;n para no aprobar el expediente:</div>
            <textarea wire:model="motivo_rechazo" rows="6" style="width: 100%; max-width: 600px; padding: 5px;"></textarea>
            @error('motivo_rechazo') <div class="validation-error">{{ $message }}</div> @enderror
            <div style="margin-top: 20px;">
                <button type="button" wire:click="backToList" class="vm-btn vm-btn-back" style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="reject" class="vm-btn vm-btn-reject">Confirmar rechazo</button>
            </div>
        </fieldset>
    @endif
</div>
