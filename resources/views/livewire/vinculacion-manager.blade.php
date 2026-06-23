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



    @if($selectedProyecto)
        <fieldset style="border: 2px solid #8b0000; border-radius: 8px; padding: 16px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 10px; font-size:15px;">
                Vincular: {{ $selectedProyecto->titulo ?? 'Proyecto' }}
            </legend>

            <div style="margin-bottom: 16px; display:flex; align-items:center; gap:10px;">
                <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary" style="font-size:13px;">&larr; Volver al listado</button>
                @if($vinculacionExistente)
                    <span class="cm-tag" style="background: #198754; font-size:12px; padding:3px 12px;">Ya vinculado</span>
                @endif
            </div>

            {{-- Datos del proyecto --}}
            <fieldset style="border: 1px solid #CCC; padding: 16px; margin-bottom: 16px; background:#fafafa;">
                <legend style="font-weight: bold; font-size: 14px; color:#333; padding: 0 8px;">Datos del proyecto vinculado</legend>
                <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 14px; border-collapse: separate; border-spacing: 0 6px;">
                    <tr>
                        <td width="130" style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">T&iacute;tulo:</td>
                        <td style="color:#222;">{{ $selectedProyecto->titulo ?? '(sin t&iacute;tulo)' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Resumen:</td>
                        <td style="text-align:justify; color:#444; line-height:1.5;">{{ $selectedProyecto->resumen ?? '(sin resumen)' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Comunidad:</td>
                        <td>{{ $selectedProyecto->comunidad?->nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Equipo:</td>
                        <td>
                            @php $integrantes = $integrantesProyecto ?? collect(); @endphp
                            @if($integrantes->isNotEmpty())
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($integrantes as $i)
                                        <div style="display:inline-flex;align-items:center;background:#e8e8e8;border-radius:14px;padding:2px 10px 2px 4px;gap:5px;font-size:11px;">
                                            <span style="width:20px;height:20px;border-radius:50%;background:#8b0000;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:bold;flex-shrink:0;">
                                                {{ strtoupper(substr($i->nombre, 0, 1)) }}{{ strtoupper(substr($i->apellido, 0, 1)) }}
                                            </span>
                                            <span style="font-weight:500;color:#333;">{{ $i->nombre }} {{ $i->apellido }}</span>
                                            @if($i->rol)
                                                <span style="background:#8b0000;color:#fff;border-radius:10px;padding:1px 7px;font-size:9px;font-weight:600;">{{ $i->rol }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:#999;font-style:italic;">{{ $selectedProyecto->equipo_resumen }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Clasificaci&oacute;n:</td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @if($selectedProyecto->linea_investigacion)<span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">L&iacute;nea: {{ $selectedProyecto->linea_investigacion->nombre_investigacion }}</span>@endif
                                @if($selectedProyecto->metodologia) <span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">Metodolog&iacute;a: {{ $selectedProyecto->metodologia->nombre }}</span>@endif
                                @if($selectedProyecto->tipo_publicacion) <span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">T. Publicaci&oacute;n: {{ $selectedProyecto->tipo_publicacion->nombre }}</span>@endif
                                @if($selectedProyecto->tipo_investigacion) <span style="background:#e8f0fe;padding:3px 8px;border-radius:4px;font-size:12px;border:1px solid #c4d7f5;">T. Investigaci&oacute;n: {{ $selectedProyecto->tipo_investigacion->nombre }}</span>@endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:top; color:#555; white-space:nowrap;">Fecha aprobaci&oacute;n:</td>
                        <td style="color:#222;">{{ $selectedProyecto->fecha_aprobacion ? $selectedProyecto->fecha_aprobacion->format('d/m/Y') : '-' }}</td>
                    </tr>
                </table>

                {{-- Documentos del proyecto --}}
                @php $docs = $selectedProyecto->documentos ?? collect(); @endphp
                @if($docs->isNotEmpty())
                    <div style="margin-top:12px; padding-top:12px; border-top:1px dashed #ddd;">
                        <b style="font-size:13px; color:#333;">Documentos del proyecto:</b>
                        <div style="margin-top:6px; display:flex; flex-wrap:wrap; gap:4px;">
                            @foreach($docs as $doc)
                                <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank"
                                    style="display:inline-flex;align-items:center;gap:4px; background:#f0f7ff; border:1px solid #b3d4fc; border-radius:5px; padding:5px 12px; font-size:12px; color:#004080; text-decoration:none;">
                                    <span style="font-size:14px;">&#128196;</span> {{ $doc->componente?->nombre ?? 'Documento' }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div style="margin-top:12px; padding-top:12px; border-top:1px dashed #ddd; font-size:13px; color:#999;">
                        <i>Este proyecto no tiene documentos asociados.</i>
                    </div>
                @endif
            </fieldset>

            <hr style="border:none; border-top:1px solid #ccc; margin:15px 0;">

            {{-- Formulario de vinculación --}}
            <fieldset style="border: 1px solid #CCC; padding: 16px; margin-bottom: 12px; background:#fafafa;">
                <legend style="font-weight: bold; font-size: 14px; color:#333; padding: 0 8px;">Datos de la vinculaci&oacute;n</legend>
                <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 14px; border-collapse: separate; border-spacing: 0 8px;">
                    <tr>
                        <td width="160" style="font-weight:bold; vertical-align:middle; color:#555; white-space:nowrap;">T&iacute;tulo de Vinculaci&oacute;n:
                            <span style="color:red;">*</span></td>
                        <td>
                            <input type="text" wire:model="vinculacionTitulo" style="width: 100%; padding: 8px 10px; border: 1px solid #bbb; border-radius: 5px; font-size: 14px; box-sizing:border-box;" placeholder="Ej: Proyecto de desarrollo comunitario...">
                            @error('vinculacionTitulo') <div style="font-size:12px;color:#c62828;margin-top:3px;">{{ $message }}</div> @enderror
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; vertical-align:middle; color:#555; white-space:nowrap;">Asociar Comunidad:</td>
                        <td>
                            @if($comunidadSeleccionada)
                                <div style="background:#e8f5e9;border:1px solid #c8e6c9;border-radius:6px;padding:12px;">
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <div style="width:36px;height:36px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">&#10003;</div>
                                        <div style="flex:1;">
                                            <div style="font-weight:bold;font-size:14px;">{{ $comunidadSeleccionada->nombre }}</div>
                                            @if($comunidadSeleccionada->rif)
                                                <div style="font-size:12px;color:#555;">RIF: {{ $comunidadSeleccionada->rif }}</div>
                                            @endif
                                            @php $dir = $comunidadSeleccionada->direccion; @endphp
                                            @if($dir && $dir->municipio)
                                                <div style="font-size:12px;color:#555;">
                                                    Direcci&oacute;n: {{ $dir->dir_calle ?? '' }},
                                                    {{ $dir->municipio->mun_nombre ?? '' }},
                                                    {{ $dir->municipio?->estado?->est_nombre ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="quitarComunidad" class="cm-btn cm-btn-secondary" style="font-size:12px;padding:6px 14px;">Cambiar</button>
                                    </div>
                                </div>
                            @else
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <select wire:model="vinculacionComunidadId" style="flex:1;padding:8px 10px;border:1px solid #bbb;border-radius:5px;font-size:14px;background:#fff;">
                                        <option value="">Seleccione comunidad...</option>
                                        @foreach($comunidades as $com)
                                            <option value="{{ $com->id }}">{{ $com->nombre }} @if($com->rif)({{ $com->rif }})@endif</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="abrirModalComunidad" class="cm-btn cm-btn-primary" style="white-space:nowrap;padding:8px 14px;font-size:13px;" title="Crear nueva comunidad">+ Nueva</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </fieldset>

            <div style="text-align: right; margin-top: 16px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" wire:click="cerrar" class="cm-btn cm-btn-secondary" style="font-size:14px; padding:8px 20px;">Cancelar</button>
                <button type="button" wire:click="guardarVinculacion" class="cm-btn cm-btn-success" style="font-size:14px; padding:8px 24px;">
                    {{ $vinculacionExistente ? 'Actualizar' : 'Guardar' }} Vinculaci&oacute;n
                </button>
            </div>
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; border-radius: 8px; padding: 16px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 10px; font-size:15px;">Vinculaci&oacute;n de Proyectos</legend>

            <div style="margin-bottom: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por t&iacute;tulo..." style="padding:6px 10px; border:1px solid #ccc; border-radius:5px; font-size:13px; min-width:250px; flex:1;">
                <span style="font-size: 13px; color: #555;">
                    <b>{{ $proyectos->total() }}</b> proyecto(s)
                </span>
            </div>

            @if($proyectos->isEmpty())
                <p style="color:#666; font-style:italic; padding: 10px;">No hay proyectos aprobados.</p>
            @else
                <table width="100%" border="1" cellpadding="6" cellspacing="0"
                    style="border-collapse: collapse; border-color: #ccc; font-size: 12px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="5%" style="padding:8px 4px;">N&deg;</th>
                            <th width="30%" style="padding:8px 4px;">T&iacute;tulo</th>
                            <th width="15%" style="padding:8px 4px;">Comunidad</th>
                            <th width="10%" style="padding:8px 4px;">Fecha Aprob.</th>
                            <th width="20%" style="padding:8px 4px;">Vinculaci&oacute;n</th>
                            <th width="20%" style="padding:8px 4px;">Acci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach($proyectos as $proy)
                            @php
                                $vin = $vinculaciones[$proy->id] ?? null;
                                $rowNum = ($proyectos->currentPage() - 1) * $proyectos->perPage() + $loop->iteration;
                            @endphp
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                                <td align="center" style="padding:6px 4px;">{{ $rowNum }}</td>
                                <td style="font-weight:bold; padding:6px 4px;">{{ $proy->titulo ?? 'N/A' }}</td>
                                <td style="padding:6px 4px;">{{ $proy->comunidad->nombre ?? '-' }}</td>
                                <td align="center" style="padding:6px 4px;">{{ $proy->fecha_aprobacion ? $proy->fecha_aprobacion->format('d/m/Y') : '-' }}</td>
                                <td align="center" style="padding:6px 4px;">
                                    @if($vin)
                                        <span class="cm-tag" style="background: #198754; font-size:11px;">Vinculado</span>
                                        @if($vin->comunidad)
                                            <div style="font-size:11px;color:#555;margin-top:3px;">{{ $vin->comunidad->nombre }}</div>
                                        @endif
                                    @else
                                        <span style="color:#999;">-</span>
                                    @endif
                                </td>
                                <td align="center" style="padding:6px 4px;">
                                    <button type="button" wire:click="vincular({{ $proy->id }})" class="cm-btn cm-btn-primary cm-btn-sm" style="font-size:12px;">
                                        {{ $vin ? 'Editar' : 'Vincular' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top: 12px;">
                    {{ $proyectos->links() }}
                </div>
            @endif
        </fieldset>
    @endif

    @if ($mostrarModalComunidad)
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:480px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:bold;">C</div>
                    <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Comunidad</h3>
                </div>

                <div style="margin-bottom: 14px;">
                    <b style="font-size:12px;color:#555;">Buscar comunidad existente:</b>
                    <input wire:model.live="buscarComunidad" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o RIF...">
                    @if($comunidadesEncontradas->isNotEmpty())
                        <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                            @foreach($comunidadesEncontradas as $com)
                                <div wire:click="seleccionarComunidadModal({{ $com->id }})" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;transition:background 0.15s;"
                                     onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                    <b style="color:#8b0000;">{{ $com->nombre }}</b>
                                    @if($com->rif)<br><small style="color:#888;">RIF: {{ $com->rif }}</small>@endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if($buscarComunidad && $comunidadesEncontradas->isEmpty())
                        <div style="margin-top:4px;font-size:11px;color:#999;padding:4px 0;">No se encontraron comunidades. Cree una nueva abajo.</div>
                    @endif
                </div>

                <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                    <b style="font-size:13px;color:#333;">O crear nueva comunidad</b>
                </div>
                <table width="100%" style="font-size:12px;margin-top:4px;border-collapse:separate;border-spacing:0 6px;">
                    <tr>
                        <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                        <td><input wire:model.live.debounce.500ms="modalComunidadNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;" placeholder="Nombre de la comunidad">
                        @if($modalComunidadNombreStatus === 'disponible')
                            <br><span style="color: #28a745; font-size: 11px;">✓ Nombre disponible</span>
                        @elseif($modalComunidadNombreStatus === 'no_disponible')
                            <br><span style="color: #dc3545; font-size: 11px;">✗ Este nombre ya está en uso</span>
                        @endif
                        </td>
                    </tr>
                    @error('modalComunidadNombre') <tr><td></td><td class="validation-error" style="font-size:11px;color:#c62828;">{{ $message }}</td></tr> @enderror
                    <tr>
                        <td style="vertical-align:top;"><b>RIF:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select wire:model.live="modalComunidadRifLetra" style="padding:4px 6px;border:1px solid #ccc;border-radius:4px;background:#fff;font-size:11px;width:48px;">
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                    <option value="J">J</option>
                                    <option value="G">G</option>
                                    <option value="P">P</option>
                                </select>
                                <input wire:model.live.debounce.500ms="modalComunidadRifNumero" type="text" inputmode="numeric" maxlength="9" style="flex:1;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;" oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Número (máx. 9 dígitos)">
                            </div>
                            @if($modalComunidadRifStatus === 'valido')
                                <span style="color: #28a745; font-size: 11px;">✓ RIF válido</span>
                            @elseif($modalComunidadRifStatus === 'invalido')
                                <span style="color: #dc3545; font-size: 11px;">✗ {{ $modalComunidadRifError ?? 'RIF inválido' }}</span>
                            @endif
                            @error('modalComunidadRifNumero')
                                <span class="validation-error" style="font-size:11px;color:#c62828;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                    <button type="button" class="cm-btn cm-btn-success" wire:click="guardarComunidadModal" style="padding:8px 20px;font-size:13px;">Guardar comunidad</button>
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarModalComunidad" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                </div>
            </div>
        </div>
    @endif
</div>
