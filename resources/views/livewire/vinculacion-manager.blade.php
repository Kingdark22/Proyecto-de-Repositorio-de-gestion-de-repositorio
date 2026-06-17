<div>
    <style>
        .cm-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0.55rem 0.95rem; font-size: 0.92rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; transition: background-color 0.2s ease, transform 0.2s ease; text-decoration: none; }
        .cm-btn:hover { transform: translateY(-1px); }
        .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
        .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
        .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
        .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
        .cm-tag { display: inline-block; background: #0d6efd; color: #fff; border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600; }
    </style>

    @if($mensaje)
        <div style="background-color: {{ $tipoMensaje === 'error' ? '#f8d7da' : '#d4edda' }}; color: {{ $tipoMensaje === 'error' ? '#721c24' : '#155724' }}; border: 1px solid {{ $tipoMensaje === 'error' ? '#f5c6cb' : '#c3e6cb' }}; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px; display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $mensaje }}</span>
            <a href="#" wire:click.prevent="limpiarMensaje" style="font-size:16px; font-weight:bold; text-decoration:none; color:inherit;">&times;</a>
        </div>
    @endif

    @if($selectedProyecto)
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                Vincular: {{ $selectedProyecto->titulo ?? 'Proyecto' }}
            </legend>

            <div style="margin-bottom: 12px;">
                <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary cm-btn-sm">&larr; Volver al listado</button>
                @if($vinculacionExistente)
                    <span class="cm-tag" style="margin-left: 8px;">Vinculado como: {{ $vinculacionExistente->tipo }}</span>
                @endif
            </div>

            <table width="100%" cellpadding="5" cellspacing="0" style="font-size: 13px;">
                <tr>
                    <td width="120" style="font-weight:bold; vertical-align:top;">T&iacute;tulo:</td>
                    <td>{{ $selectedProyecto->titulo ?? '(sin t&iacute;tulo)' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold; vertical-align:top;">Resumen:</td>
                    <td>{{ $selectedProyecto->resumen ?? '(sin resumen)' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Comunidad:</td>
                    <td>{{ $selectedProyecto->comunidad?->nombre ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Fecha aprobaci&oacute;n:</td>
                    <td>{{ $selectedProyecto->fecha_aprobacion ? $selectedProyecto->fecha_aprobacion->format('d/m/Y') : '-' }}</td>
                </tr>
                @if($selectedProyecto->archivo_path)
                <tr>
                    <td style="font-weight:bold;">Documento:</td>
                    <td><a href="{{ route('documentos.serve', ['path' => $selectedProyecto->archivo_path]) }}" target="_blank" style="color:#0000EE;">Ver PDF</a></td>
                </tr>
                @endif
            </table>

            <hr style="border:none; border-top:1px solid #ccc; margin:15px 0;">

            <table width="100%" cellpadding="5" cellspacing="0" style="font-size: 13px;">
                <tr>
                    <td width="140" style="font-weight:bold; vertical-align:top;">Tipo de Vinculaci&oacute;n:</td>
                    <td>
                        <input type="text" wire:model="vinculacionTipo" style="width: 80%; padding: 4px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Ej: Ciencia y Tecnolog&iacute;a...">
                        @error('vinculacionTipo') <div style="color:red;font-size:10px;">{{ $message }}</div> @enderror
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold; vertical-align:top;">Asociar Comunidad:</td>
                    <td>
                        @if($comunidadSeleccionada)
                            <div style="background:#e8f5e9;border:1px solid #c8e6c9;border-radius:4px;padding:8px;margin-bottom:6px;">
                                <div style="font-weight:bold;font-size:13px;">{{ $comunidadSeleccionada->nombre }}</div>
                                @if($comunidadSeleccionada->rif)
                                    <div style="font-size:11px;color:#555;">RIF: {{ $comunidadSeleccionada->rif }}</div>
                                @endif
                                @php $dir = $comunidadSeleccionada->direccion; @endphp
                                @if($dir && $dir->municipio)
                                    <div style="font-size:11px;color:#555;">
                                        Direcci&oacute;n: {{ $dir->dir_calle ?? '' }},
                                        {{ $dir->municipio->mun_nombre ?? '' }},
                                        {{ $dir->municipio?->estado?->est_nombre ?? '' }}
                                    </div>
                                @endif
                                <button type="button" wire:click="quitarComunidad" style="font-size:10px;color:#c62828;border:none;background:none;cursor:pointer;margin-top:4px;">&times; Quitar comunidad</button>
                            </div>
                        @else
                            <div style="margin-bottom:4px;">
                                <input type="text" wire:model.live.debounce.300ms="searchComunidad" style="width:80%;padding:4px;border:1px solid #ccc;border-radius:4px;" placeholder="Buscar comunidad por nombre o RIF...">
                            </div>
                            @if($searchComunidad !== '')
                                <div style="max-height:150px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:4px;margin-bottom:4px;">
                                    @forelse($comunidadesFiltradas as $com)
                                        <div wire:click="seleccionarComunidad({{ $com->com_codigo }})" style="cursor:pointer;padding:4px 6px;border-bottom:1px solid #eee;font-size:12px;">
                                            <b>{{ $com->nombre }}</b>
                                            @if($com->rif) <span style="color:#888;">({{ $com->rif }})</span> @endif
                                        </div>
                                    @empty
                                        <div style="padding:4px;color:#999;font-size:11px;font-style:italic;">Sin resultados</div>
                                    @endforelse
                                </div>
                            @endif
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold; vertical-align:top;">Observaciones:</td>
                    <td><textarea wire:model="vinculacionObservaciones" rows="3" style="width: 80%; padding: 4px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Observaciones opcionales..."></textarea></td>
                </tr>
            </table>

            <div style="text-align: right; margin-top: 12px;">
                <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary cm-btn-sm">Cancelar</button>
                <button type="button" wire:click="guardarVinculacion" class="cm-btn cm-btn-success cm-btn-sm" style="margin-left: 8px;">
                    {{ $vinculacionExistente ? 'Actualizar' : 'Guardar' }} Vinculaci&oacute;n
                </button>
            </div>
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Vinculaci&oacute;n de Proyectos</legend>

            <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por t&iacute;tulo..." style="padding:4px 8px; border:1px solid #ccc; border-radius:4px; font-size:12px; min-width:200px; flex:1;">
                <span style="font-size: 12px; color: #555;">
                    <b>{{ $proyectos->total() }}</b> proyecto(s)
                </span>
            </div>

            @if($proyectos->isEmpty())
                <p style="color:#666; font-style:italic; padding: 10px;">No hay proyectos aprobados.</p>
            @else
                <table width="100%" border="1" cellpadding="5" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="5%">N&deg;</th>
                            <th width="30%">T&iacute;tulo</th>
                            <th width="15%">Comunidad</th>
                            <th width="10%">Fecha</th>
                            <th width="20%">Vinculaci&oacute;n</th>
                            <th width="20%">Acci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach($proyectos as $proy)
                            @php
                                $vin = $vinculaciones[$proy->id] ?? null;
                                $rowNum = ($proyectos->currentPage() - 1) * $proyectos->perPage() + $loop->iteration;
                            @endphp
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                                <td align="center">{{ $rowNum }}</td>
                                <td style="font-weight:bold;">{{ $proy->titulo ?? 'N/A' }}</td>
                                <td>{{ $proy->comunidad->nombre ?? '-' }}</td>
                                <td align="center">{{ $proy->fecha_aprobacion ? $proy->fecha_aprobacion->format('d/m/Y') : '-' }}</td>
                                <td align="center">
                                    @if($vin)
                                        <span class="cm-tag">{{ $vin->tipo }}</span>
                                        @if($vin->comunidad)
                                            <div style="font-size:10px;color:#555;margin-top:2px;">{{ $vin->comunidad->nombre }}</div>
                                        @endif
                                    @else
                                        <span style="color:#999;">-</span>
                                    @endif
                                </td>
                                <td align="center">
                                    <button type="button" wire:click="vincular({{ $proy->id }})" class="cm-btn cm-btn-primary cm-btn-sm">
                                        {{ $vin ? 'Editar' : 'Vincular' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top: 10px;">
                    {{ $proyectos->links() }}
                </div>
            @endif
        </fieldset>
    @endif
</div>
