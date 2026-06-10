<div>
    <style>
        .cm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0.55rem 0.95rem;
            font-size: 0.92rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }

        .cm-btn:hover {
            transform: translateY(-1px);
        }

        .cm-btn-primary {
            background: #19692e;
            border-color: #154f26;
            color: #fff;
        }

        .cm-btn-success {
            background: #198754;
            border-color: #166f43;
            color: #fff;
        }

        .cm-btn-warning {
            background: #f0b606;
            border-color: #d99e00;
            color: #212529;
        }

        .cm-btn-danger {
            background: #c82333;
            border-color: #a71d2a;
            color: #fff;
        }

        .cm-btn-secondary {
            background: #f4f4f4;
            border-color: #c2c2c2;
            color: #222;
        }

        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Validaci&oacute;n de Proyectos</h2>

    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if ($viewMode === 'list')
        <div style="margin-bottom: 15px;">
            <b>B&uacute;squeda (T&iacute;tulo):</b>
            <input wire:model.live="search" type="text" style="width: 250px;" placeholder="Título del proyecto...">
        </div>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Revisi&oacute;n de Expedientes
                Pendientes</legend>

            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="35%">T&iacute;tulo del Proyecto / Resumen</th>
                        <th padding="5" width="25%">Lapso / Coordinaci&oacute;n</th>
                        <th padding="5" width="15%">Documento</th>
                        <th padding="5" width="25%">Acciones de Validaci&oacute;n</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($proyectos as $p)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};"
                            valign="top">
                            <td align="left" style="padding: 5px;">
                                <span style="font-weight: bold;">{{ $p->titulo }}</span>
                                <br>
                                <span style="font-size: 10px; color: #555;">{{ Str::limit($p->resumen, 60) }}</span>
                                <br>
                                <span style="font-size: 9px; color: #888; font-weight: bold;">Registrado el:
                                    {{ $p->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $p->lapso_academico->nombre }}
                            </td>
                            <td align="center" style="padding: 5px;">
                                @if ($p->archivo_path)
                                    <a href="{{ Storage::url($p->archivo_path) }}" target="_blank"
                                        style="color: #0000EE; text-decoration: none; font-weight: bold;">[Ver Documento
                                        PDF]</a>
                                @else
                                    <span style="color: #999; font-size: 10px;">No aportado</span>
                                @endif
                                <br><br>
                                <a href="#" wire:click.prevent="openDetails({{ $p->id }})"
                                    title="Ver Detalles"
                                    style="color: #0000EE; text-decoration: none; display: inline-block;">
                                    [Ver Ficha T&eacute;cnica]
                                </a>
                            </td>
                            <td align="center" style="padding: 5px;">
                                <button type="button" wire:click="approve({{ $p->id }})"
                                    onclick="return confirm('&iquest;Confirma que el documento es v&aacute;lido y aprueba el proyecto?')"
                                    class="cm-btn cm-btn-success cm-btn-sm"
                                    style="display: block; width: 90%; margin: 0 auto 5px auto; font-size: 10px;">
                                    Aprobar / Confirmar
                                </button>
                                <button type="button" wire:click="openRejectModal({{ $p->id }})"
                                    class="cm-btn cm-btn-danger cm-btn-sm"
                                    style="display: block; width: 90%; margin: 0 auto; font-size: 10px;">
                                    Rechazar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if ($proyectos->isEmpty())
                        <tr>
                            <td colspan="5" align="center"
                                style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                Bandeja vac&iacute;a. No hay expedientes pendientes de revisi&oacute;n.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div style="margin-top: 10px;">
                {{ $proyectos->links() }}
            </div>
        </fieldset>
    @elseif($viewMode === 'reject')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Indicar Motivo de
                Rechazo</legend>

            <div style="margin-bottom: 15px; font-size: 12px;">
                Por favor, detalle la justificaci&oacute;n para no aprobar el expediente:
            </div>

            <div style="text-align: left;">
                <textarea wire:model="motivo_rechazo" rows="6" style="width: 100%; max-width: 600px; padding: 5px;"></textarea>
                @error('motivo_rechazo')
                    <div class="obligatorio" style="font-size: 11px; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="text-align: left; margin-top: 20px;">
                <button type="button" wire:click="backToList" class="cm-btn cm-btn-danger"
                    style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="reject" class="cm-btn cm-btn-danger">Confirmar Rechazo</button>
            </div>
        </fieldset>
    @elseif($viewMode === 'details' && $selectedProject)
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Ficha T&eacute;cnica del
                Proyecto</legend>

            <div
                style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #CCC; padding-bottom: 10px; margin-bottom: 15px;">
                <div style="width: 100%;">
                    <span
                        style="background-color: #8bb2b7; color: #000; font-size: 10px; font-weight: bold; padding: 2px 5px; margin-right: 5px; border: 1px solid #777;">
                        {{ $selectedProject->lapso_academico->nombre }}
                    </span>
                    <h3 style="margin-top: 5px; margin-bottom: 0; color: #000; font-size: 16px; font-weight: bold;">
                        {{ $selectedProject->titulo }}
                    </h3>
                </div>
            </div>

            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Resumen
                    del Proyecto</legend>
                <div style="font-size: 12px; text-align: justify; padding: 5px;">
                    {{ $selectedProject->resumen }}
                </div>
            </fieldset>

            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="48%" valign="top">
                        <fieldset style="border: 1px solid #CCC; padding: 10px; height: 100%;">
                            <legend
                                style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">
                                Detalles de Investigaci&oacute;n</legend>
                            <table width="100%" border="0" cellpadding="4" cellspacing="0"
                                style="font-size: 11px;">
                                <tr>
                                    <td width="35%"><b>Publicaci&oacute;n:</b></td>
                                    <td width="65%">{{ $selectedProject->tipo_publicacion->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><b>Investigaci&oacute;n:</b></td>
                                    <td>{{ $selectedProject->tipo_investigacion->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><b>Metodolog&iacute;a:</b></td>
                                    <td>{{ $selectedProject->metodologia->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><b>L&iacute;nea de Inv.:</b></td>
                                    <td>{{ $selectedProject->linea_investigacion->nombre_investigacion }}</td>
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <div style="text-align: center; margin-top: 20px; border-top: 1px solid #CCC; padding-top: 15px;">
                <button type="button" wire:click="approveFromDetails({{ $selectedProject->id }})"
                    onclick="return confirm('&iquest;Confirma que aprueba el proyecto?')" class="cm-btn cm-btn-success"
                    style="margin-right: 15px;">
                    Aprobar Proyecto Ahora
                </button>
                <button type="button" wire:click="rejectFromDetails({{ $selectedProject->id }})"
                    class="cm-btn cm-btn-danger" style="margin-right: 15px;">
                    Rechazar Revisi&oacute;n
                </button>
                <button type="button" wire:click="backToList" class="cm-btn cm-btn-secondary">
                    Regresar al Listado
                </button>
            </div>
        </fieldset>
    @endif
</div>
