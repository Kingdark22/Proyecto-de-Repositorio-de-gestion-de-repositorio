<div>
    <style>
        .cm-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0.55rem 0.95rem; font-size: 0.92rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; transition: background-color 0.2s ease, transform 0.2s ease; text-decoration: none; }
        .cm-btn:hover { transform: translateY(-1px); }
        .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
        .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
        .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
        .cm-btn-danger { background: #c62828; border-color: #a02121; color: #fff; }
        .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
        .cm-tag { display: inline-block; background: #0d6efd; color: #fff; border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600; }
        .sel-checkbox { width: 16px; height: 16px; accent-color: #8b0000; cursor: pointer; vertical-align: middle; }
        tr.sel-row { cursor: pointer; }
        tr.sel-row:hover td { background: #f5f0f0 !important; }
        tr.selected { background-color: #fce4e4 !important; }
        .grupo-card { border: 1px solid #c8e6c9; border-radius: 8px; padding: 14px; margin-bottom: 12px; background: #f1faf1; }
        .grupo-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .paso-indicator { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 20px; }
        .paso-step { display: flex; align-items: center; }
        .paso-circle { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; border: 2px solid #ccc; color: #999; background: #fff; }
        .paso-circle.active { border-color: #8b0000; color: #fff; background: #8b0000; }
        .paso-circle.done { border-color: #198754; color: #fff; background: #198754; }
        .paso-label { font-size: 11px; color: #999; margin-top: 4px; text-align: center; }
        .paso-label.active { color: #8b0000; font-weight: bold; }
        .paso-label.done { color: #198754; }
        .paso-line { width: 50px; height: 2px; background: #ccc; margin: 0 4px; }
        .paso-line.active { background: #8b0000; }
        .paso-line.done { background: #198754; }
        .uppercase { text-transform: uppercase; }
        .contenido-uppercase td, .contenido-uppercase th, .contenido-uppercase div, .contenido-uppercase span { text-transform: uppercase; }
        .wizard-titulo { text-transform: uppercase; }
        
        /* Sobrescribir altura global de select para evitar recorte de texto por padding */
        select {
            height: auto !important;
        }
    </style>


    {{-- ─── CABECERA ─── --}}
    <fieldset style="border: 2px solid #8b0000; border-radius: 8px; padding: 16px;" class="contenido-uppercase">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 10px; font-size:15px;">
            Adjuntar los Proyectos
        </legend>

        <div style="display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap;">
            <button type="button" wire:click="abrirWizard" class="cm-btn cm-btn-success" style="font-size:13px;">
                + Vincular Proyectos
            </button>
            <button type="button" wire:click="abrirModalReporte" class="cm-btn cm-btn-primary" style="font-size:13px;">
                &darr; Reporte de Proyectos
            </button>
        </div>

        {{-- ─── VINCULACIONES EXISTENTES ─── --}}
        @if($vinculacionesPorProyecto->isEmpty() && trim($busquedaListado) === '')
            <p style="color:#666; font-style:italic; padding: 10px;">No hay vinculaciones registradas.</p>
        @else
            <div style="margin-bottom:10px;display:flex;gap:10px;align-items:center;">
                <input wire:model.live.debounce.300ms="busquedaListado" type="text" placeholder="Buscar por proyecto, titulo, comunidad o lapso..." style="flex:1;padding:8px 10px;border:2px solid #8b0000;border-radius:6px;font-size:13px;box-sizing:border-box;">
                @if(trim($busquedaListado) !== '')
                    <button type="button" wire:click="$set('busquedaListado', '')" style="background:none;border:1px solid #8b0000;color:#8b0000;border-radius:4px;padding:5px 12px;font-size:12px;cursor:pointer;font-weight:600;white-space:nowrap;">Limpiar</button>
                @endif
                <span style="font-size:12px;color:#555;">{{ $vinculacionesPorProyecto->count() }} resultado(s)</span>
            </div>
            <div style="margin-top:10px;">
                <table width="100%" style="font-size:12px;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#8bb2b7;color:#000;font-weight:bold;">
                            <th width="4%" style="padding:8px 4px;text-align:center;">N&deg;</th>
                            <th width="30%" style="padding:8px 4px;">Proyecto</th>
                            <th width="25%" style="padding:8px 4px;">T&iacute;tulos vinculados</th>
                            <th width="12%" style="padding:8px 4px;">Lapso</th>
                            <th width="22%" style="padding:8px 4px;">Comunidades</th>
                            <th width="7%" style="padding:8px 4px;">Acci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowNum = 0; @endphp
                        @foreach($vinculacionesPorProyecto as $pid => $grupo)
                            @php
                                $rowNum++;
                                $proyecto = $grupo->first()->proyecto;
                                $titulos = $grupo->pluck('titulo')->filter()->unique()->values()->toArray();
                                $comunidades = $grupo->pluck('comunidad.nombre')->filter()->unique()->values()->toArray();
                                $lapso = $grupo->first()->lapso_nombre ?? '';
                                $countTitulos = count($titulos);
                                $countComunidades = count($comunidades);
                                $maxShow = 3;
                            @endphp
                            <tr style="background:{{ $rowNum % 2 == 0 ? '#E0E0E0' : '#FFF' }};" valign="top">
                                <td align="center" style="padding:6px 4px;">{{ $rowNum }}</td>
                                <td style="font-weight:bold;padding:6px 4px;">{{ $proyecto->titulo ?? 'N/A' }}</td>
                                <td style="padding:6px 4px;">
                                    @foreach(array_slice($titulos, 0, $maxShow) as $t)
                                        <span style="display:inline-block;background:#0d6efd;color:#fff;border-radius:3px;padding:1px 6px;font-size:10px;margin:1px 0;">{{ $t }}</span>
                                    @endforeach
                                    @if($countTitulos > $maxShow)
                                        <span style="display:inline-block;background:#6c757d;color:#fff;border-radius:3px;padding:1px 6px;font-size:10px;margin:1px 0;">+{{ $countTitulos - $maxShow }}</span>
                                    @endif
                                </td>
                                <td style="padding:6px 4px;font-size:11px;">{{ $lapso }}</td>
                                <td style="padding:6px 4px;">
                                    @foreach(array_slice($comunidades, 0, $maxShow) as $c)
                                        <span style="display:inline-block;background:#198754;color:#fff;border-radius:3px;padding:1px 6px;font-size:10px;margin:1px 0;">{{ $c }}</span>
                                    @endforeach
                                    @if($countComunidades > $maxShow)
                                        <span style="display:inline-block;background:#6c757d;color:#fff;border-radius:3px;padding:1px 6px;font-size:10px;margin:1px 0;">+{{ $countComunidades - $maxShow }}</span>
                                    @endif
                                </td>
                                <td align="center" style="padding:6px 4px;">
                                    @foreach($grupo as $v)
                                        <button type="button" wire:click="quitarVinculacion({{ $v->vin_codigo }})" style="background:none;border:1px solid #c62828;color:#c62828;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;font-weight:600;margin:1px 0;display:block;width:100%;" onmouseover="this.style.background='#c62828';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='#c62828'">Quitar {{ $loop->iteration }}</button>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($vinculacionesPorProyecto->isEmpty() && trim($busquedaListado) !== '')
                    <p style="color:#666;font-style:italic;padding:10px;text-align:center;">No se encontraron resultados para "{{ $busquedaListado }}".</p>
                @endif
            </div>
        @endif
    </fieldset>

    {{-- ─── WIZARD: NUEVA VINCULACIÓN ─── --}}
    @if ($mostrarWizard)
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:750px;width:94%;max-height:92vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">

                {{-- Indicador de pasos --}}
                <div class="paso-indicator">
                    @foreach([1 => 'Proyectos', 2 => 'Título', 3 => 'Comunidad', 4 => 'Confirmar'] as $num => $label)
                        <div class="paso-step" style="flex-direction:column;">
                            <div class="paso-circle {{ $pasoActual == $num ? 'active' : '' }} {{ $pasoActual > $num ? 'done' : '' }}">
                                @if($pasoActual > $num) &#10003; @else {{ $num }} @endif
                            </div>
                            <div class="paso-label {{ $pasoActual == $num ? 'active' : '' }} {{ $pasoActual > $num ? 'done' : '' }}">{{ $label }}</div>
                        </div>
                        @if($num < 4)
                            <div class="paso-line {{ $pasoActual > $num ? 'done' : ($pasoActual == $num ? 'active' : '') }}"></div>
                        @endif
                    @endforeach
                </div>

                <hr style="border:none;border-top:1px solid #e0e0e0;margin:0 0 16px 0;">

                {{-- PASO 1: PROYECTOS --}}
                @if($pasoActual === 1)
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 1: Seleccionar Proyectos</h4>

                    <div style="margin-bottom:12px;display:flex;gap:10px;align-items:center;">
                        <div style="position:relative;flex:1;min-width:200px;">
                            <input wire:model.live.debounce.50ms="search" type="text" placeholder="Buscar proyecto por titulo, resumen, linea, comunidad..." id="vincSearchInput" autocomplete="off" oninput="buscarVinc()" style="width:100%;padding:8px 10px;border:2px solid #8b0000;border-radius:6px;font-size:14px;box-sizing:border-box;">
                            <div id="vincSearchAutocomplete" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #ccc;border-radius:4px;max-height:220px;overflow-y:auto;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
                        </div>
                        <span style="font-size:13px;color:#555;">
                            <b>{{ $proyectos->total() ?? 0 }}</b> proyecto(s)
                        </span>
                        <button type="button" wire:click="toggleSelectAll" style="background:none;border:1px solid #8b0000;color:#8b0000;border-radius:4px;padding:5px 12px;font-size:12px;cursor:pointer;font-weight:600;white-space:nowrap;" onmouseover="this.style.background='#8b0000';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='#8b0000'">
                            @if(count($selectedProjects) > 0 && count($selectedProjects) >= ($proyectos->total() ?? 0))
                                Deseleccionar todo
                            @else
                                Seleccionar todo
                            @endif
                        </button>
                        @if(count($selectedProjects) > 0)
                            <span style="background:#8b0000;color:#fff;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:bold;">
                                {{ count($selectedProjects) }} seleccionado(s)
                            </span>
                        @endif
                    </div>

                    @if($proyectos && $proyectos->isNotEmpty())
                        <div style="max-height:55vh;overflow-y:auto;">
                            @foreach($proyectos as $proy)
                                @php
                                    $rowNum = ($proyectos->currentPage() - 1) * $proyectos->perPage() + $loop->iteration;
                                    $isSelected = in_array($proy->id, $selectedProjects);
                                @endphp
                                <div wire:click="toggleProject({{ $proy->id }})" style="border:{{ $isSelected ? '2px solid #8b0000' : '1px solid #ccc' }};border-radius:6px;padding:10px 12px;margin-bottom:8px;cursor:pointer;background:{{ $isSelected ? '#fce4e4' : '#fff' }};{{ $isSelected ? 'box-shadow:0 0 0 1px #8b0000;' : '' }}">
                                    {{-- Header --}}
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                        <input type="checkbox" wire:model.live="selectedProjects" value="{{ $proy->id }}" class="sel-checkbox" onclick="event.stopPropagation()" style="width:16px;height:16px;">
                                        <span style="background:#8bb2b7;color:#000;border-radius:3px;padding:1px 6px;font-size:10px;font-weight:bold;">{{ $rowNum }}</span>
                                        <div style="flex:1;font-weight:bold;font-size:13px;">{{ $proy->titulo ?? 'N/A' }}</div>
                                        <button type="button" wire:click.stop="verDetalle({{ $proy->id }})" class="cm-btn cm-btn-secondary cm-btn-sm" style="font-size:10px;padding:3px 8px;">Ver detalle</button>
                                    </div>

                                    {{-- Info del proyecto en 2 columnas --}}
                                    <table style="width:100%;font-size:11px;border-collapse:collapse;">
                                        <tr>
                                            <td style="width:50%;vertical-align:top;padding:2px 8px 2px 24px;">
                                                <b>Resumen:</b> {{ \Illuminate\Support\Str::limit(strip_tags($proy->resumen ?? ''), 200) }}
                                            </td>
                                            <td style="width:50%;vertical-align:top;padding:2px 8px 2px 4px;">
                                                @if($proy->equipo_ref)
                                                    <b>Equipo:</b> {{ $proy->equipo_resumen ?? $proy->equipo_ref }}<br>
                                                @endif
                                                @if($proy->creador_cedula)
                                                    <b>Creador C.I.:</b> {{ $proy->creador_cedula }}<br>
                                                @endif
                                                @if($proy->pry_cantidad_beneficiados)
                                                    <b>Beneficiarios:</b> {{ $proy->pry_cantidad_beneficiados }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;padding:2px 8px 2px 24px;">
                                                <b>Linea:</b> {{ $proy->linea_investigacion->nombre_investigacion ?? '-' }}<br>
                                                <b>Metodologia:</b> {{ $proy->metodologia->nombre ?? '-' }}
                                            </td>
                                            <td style="vertical-align:top;padding:2px 8px 2px 4px;">
                                                <b>Tipo:</b> {{ $proy->tipo_investigacion->nombre ?? '-' }}<br>
                                                <b>Objetivo:</b> {{ $proy->objetivo_investigacion->nombre ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;padding:2px 8px 2px 24px;">
                                                <b>Comunidad:</b> {{ $proy->comunidad->nombre ?? '-' }}
                                                @if($proy->comunidad?->rif) ({{ $proy->comunidad->rif }})@endif
                                                @if($proy->comunidad?->direccion)
                                                    <br><span style="font-size:10px;color:#555;">
                                                        {{ $proy->comunidad->direccion->dir_calle ?? '' }}
                                                        {{ $proy->comunidad->direccion->municipio?->mun_nombre ? ', ' . $proy->comunidad->direccion->municipio->mun_nombre : '' }}
                                                        {{ $proy->comunidad->direccion->municipio?->estado?->est_nombre ? ', ' . $proy->comunidad->direccion->municipio->estado->est_nombre : '' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="vertical-align:top;padding:2px 8px 2px 4px;">
                                                @if($proy->documentos->isNotEmpty())
                                                    <b>Documentos:</b> {{ $proy->documentos->count() }} archivo(s)
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                        <div style="margin-top:10px;">{{ $proyectos->links() }}</div>
                    @else
                        <p style="color:#666;font-style:italic;padding:10px;">No hay proyectos aprobados.</p>
                    @endif
                @endif

                {{-- PASO 2: TÍTULO --}}
                @if($pasoActual === 2)
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 2: Seleccionar T&iacute;tulo</h4>

                    <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:6px;padding:8px 12px;margin-bottom:12px;font-size:13px;">
                        <span style="font-weight:bold;color:#198754;">Proyectos:</span>
                        <span style="color:#333;">{{ count($selectedProjects) }} seleccionado(s)</span>
                        <button type="button" wire:click="pasoEspecifico(1)" style="background:none;border:none;color:#8b0000;text-decoration:underline;cursor:pointer;font-size:12px;margin-left:4px;">Cambiar</button>
                    </div>

                    <p style="font-size:12px;color:#666;margin-bottom:10px;">Seleccione un título existente o cree uno nuevo. Este título se aplicará a todos los proyectos seleccionados.</p>

                    @if($tituloSeleccionado !== '' && $tituloSeleccionado !== 'nuevo')
                        <div style="background:#e8f5e9;border:2px solid #198754;border-radius:8px;padding:16px;margin-bottom:12px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:40px;height:40px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">&#10003;</div>
                                <div style="flex:1;">
                                    <div style="font-weight:bold;font-size:16px;">{{ $titulosDisponibles[(int)$tituloSeleccionado] ?? '' }}</div>
                                </div>
                                <button type="button" wire:click="$set('tituloSeleccionado', '')" class="cm-btn cm-btn-secondary" style="font-size:13px;padding:8px 16px;">Cambiar</button>
                            </div>
                        </div>
                    @else
                        <div style="margin-bottom:12px;">
                            <select wire:model.live="tituloSeleccionado" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:15px;box-sizing:border-box;background:#fff;">
                                <option value="">Seleccione un título...</option>
                                @foreach($titulosDisponibles as $tid => $ttitulo)
                                    <option value="{{ $tid }}">{{ $ttitulo }}</option>
                                @endforeach
                                <option value="nuevo">[ + Crear nuevo título ]</option>
                            </select>
                        </div>

                        @if($tituloSeleccionado === 'nuevo')
                            <div style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:12px;margin-top:8px;">
                                <label style="font-size:12px;font-weight:bold;color:#555;display:block;margin-bottom:6px;">Nombre del nuevo título:</label>
                                <div style="display:flex;gap:8px;">
                                    <input type="text" wire:model.live="nuevoTitulo" placeholder="Escriba el nombre del título..." style="flex:1;padding:8px 10px;border:1px solid #8b0000;border-radius:4px;font-size:13px;text-transform:uppercase;">
                                </div>
                                @error('nuevoTitulo') <span style="font-size:11px;color:#c62828;">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    @endif
                @endif

                {{-- PASO 3: COMUNIDAD (OPCIONAL) --}}
                @if($pasoActual === 3)
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 3: Comunidad (Opcional)</h4>

                    <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:6px;padding:8px 12px;margin-bottom:12px;font-size:13px;">
                        <div style="display:flex;align-items:center;gap:4px;">
                            <span style="font-weight:bold;color:#198754;">Proyectos:</span>
                            <span style="color:#333;">{{ count($selectedProjects) }} seleccionado(s)</span>
                            <button type="button" wire:click="pasoEspecifico(1)" style="background:none;border:none;color:#8b0000;text-decoration:underline;cursor:pointer;font-size:12px;margin-left:4px;">Cambiar</button>
                        </div>
                        <div style="display:flex;align-items:center;gap:4px;margin-top:4px;">
                            <span style="font-weight:bold;color:#e65100;">T&iacute;tulo:</span>
                            <span style="color:#333;">{{ $tituloSeleccionado === 'nuevo' ? ($nuevoTitulo ?: 'Nuevo título') : ($titulosDisponibles[(int)$tituloSeleccionado] ?? 'N/A') }}</span>
                            <button type="button" wire:click="pasoEspecifico(2)" style="background:none;border:none;color:#8b0000;text-decoration:underline;cursor:pointer;font-size:12px;margin-left:4px;">Cambiar</button>
                        </div>
                    </div>

                    <p style="font-size:12px;color:#666;margin-bottom:10px;">Opcionalmente seleccione una comunidad. Si no selecciona ninguna, la vinculación se guardará sin comunidad.</p>

                    @if($comunidadSeleccionada)
                        <div style="background:#e8f5e9;border:2px solid #198754;border-radius:8px;padding:16px;margin-bottom:12px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:40px;height:40px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">&#10003;</div>
                                <div style="flex:1;">
                                    <div style="font-weight:bold;font-size:16px;">{{ $comunidadSeleccionada->nombre }}</div>
                                    @if($comunidadSeleccionada->rif)
                                        <div style="font-size:13px;color:#555;">RIF: {{ $comunidadSeleccionada->rif }}</div>
                                    @endif
                                </div>
                                <button type="button" wire:click="quitarComunidad" class="cm-btn cm-btn-secondary" style="font-size:13px;padding:8px 16px;">Cambiar</button>
                            </div>
                        </div>
                    @else
                        <div style="margin-bottom:12px;">
                            <select wire:model.live="comunidadId" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:15px;box-sizing:border-box;background:#fff;">
                                <option value="">Sin comunidad (opcional)...</option>
                                @foreach(($comunidades ?? collect()) as $com)
                                    <option value="{{ $com->id }}">{{ $com->nombre }} @if($com->rif)({{ $com->rif }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="text-align:center;margin:8px 0;">
                            <button type="button" wire:click="abrirModalComunidad" class="cm-btn cm-btn-primary" style="font-size:13px;padding:8px 20px;">+ Crear nueva comunidad</button>
                        </div>
                    @endif
                @endif

                {{-- PASO 4: CONFIRMAR --}}
                @if($pasoActual === 4)
                    <h4 style="margin:0 0 16px 0;font-size:15px;color:#333;">Paso 4: Confirmar Vinculaci&oacute;n</h4>
                    <div style="background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-bottom:16px;">
                        <table style="font-size:14px;border-collapse:separate;border-spacing:0 10px;">
                            <tr>
                                <td style="font-weight:bold;color:#555;padding-right:20px;">T&iacute;tulo:</td>
                                <td><span style="font-weight:bold;">{{ $tituloSeleccionado === 'nuevo' ? ($nuevoTitulo ?: 'Nuevo título') : ($titulosDisponibles[(int)$tituloSeleccionado] ?? 'N/A') }}</span></td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding-right:20px;">Comunidad:</td>
                                <td><span style="font-weight:bold;">{{ $comunidadSeleccionada?->nombre ?? 'Sin comunidad' }}</span></td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;color:#555;padding-right:20px;">Proyectos a vincular:</td>
                                <td><span style="font-weight:bold;color:#8b0000;">{{ count($selectedProjects) }} proyecto(s)</span></td>
                            </tr>
                        </table>
                        @if(count($selectedProjects) > 0)
                            <div style="margin-top:10px;font-size:12px;color:#555;">
                                @php
                                    $proyConfirm = \App\Models\Proyecto::whereIn('id', $selectedProjects)->get()->keyBy('id');
                                @endphp
                                @foreach($selectedProjects as $pid)
                                    @php $p = $proyConfirm->get($pid); @endphp
                                    @if($p)
                                        <span style="display:inline-block;background:#fff;border:1px solid #c8e6c9;border-radius:4px;padding:3px 8px;margin:2px;">
                                            {{ $p->titulo ?? 'ID:'.$pid }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <hr style="border:none;border-top:1px solid #e0e0e0;margin:16px 0;">

                {{-- Botones de navegación --}}
                <div style="display:flex;gap:10px;justify-content:space-between;">
                    <div>
                        <button type="button" wire:click="cerrarWizard" class="cm-btn cm-btn-secondary" style="font-size:14px;padding:8px 20px;">Cancelar</button>
                    </div>
                    <div style="display:flex;gap:10px;">
                        @if($pasoActual > 1)
                            <button type="button" wire:click="pasoAnterior" class="cm-btn cm-btn-secondary" style="font-size:14px;padding:8px 20px;">&larr; Anterior</button>
                        @endif
                        @if($pasoActual < 4)
                            <button type="button" wire:click="siguientePaso" class="cm-btn cm-btn-success" style="font-size:14px;padding:8px 24px;">Siguiente &rarr;</button>
                        @else
                            <button type="button" wire:click="guardarVinculacion" class="cm-btn cm-btn-success" style="font-size:14px;padding:8px 24px;">Guardar Vinculaci&oacute;n</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── MODAL REPORTE PDF ─── --}}
    @if ($mostrarModalReporte)
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:520px;width:92%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:bold;">R</div>
                    <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Reporte de Vinculaciones</h3>
                </div>

                <div style="margin-bottom:16px;">
                    <b style="font-size:13px;color:#555;display:block;margin-bottom:8px;">Tipo de reporte:</b>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:8px 14px;border:2px solid {{ $tipoReporte === 'todos' ? '#8b0000' : '#ddd' }};border-radius:8px;background:{{ $tipoReporte === 'todos' ? '#fff0f0' : '#fafafa' }};flex:1;min-width:120px;transition:all 0.2s;">
                            <input type="radio" wire:model.live="tipoReporte" value="todos" style="accent-color:#8b0000;">
                            Todos los vinculados
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:8px 14px;border:2px solid {{ $tipoReporte === 'titulo' ? '#8b0000' : '#ddd' }};border-radius:8px;background:{{ $tipoReporte === 'titulo' ? '#fff0f0' : '#fafafa' }};flex:1;min-width:120px;transition:all 0.2s;">
                            <input type="radio" wire:model.live="tipoReporte" value="titulo" style="accent-color:#8b0000;">
                            Por título
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:8px 14px;border:2px solid {{ $tipoReporte === 'lapso' ? '#8b0000' : '#ddd' }};border-radius:8px;background:{{ $tipoReporte === 'lapso' ? '#fff0f0' : '#fafafa' }};flex:1;min-width:120px;transition:all 0.2s;">
                            <input type="radio" wire:model.live="tipoReporte" value="lapso" style="accent-color:#8b0000;">
                            Por lapso académico
                        </label>
                        @if(count($selectedProjects) > 0)
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:8px 14px;border:2px solid {{ $tipoReporte === 'wizard' ? '#8b0000' : '#ddd' }};border-radius:8px;background:{{ $tipoReporte === 'wizard' ? '#fff0f0' : '#fafafa' }};flex:1;min-width:120px;transition:all 0.2s;">
                                <input type="radio" wire:model.live="tipoReporte" value="wizard" style="accent-color:#8b0000;">
                                Selección actual ({{ count($selectedProjects) }} proyecto(s))
                            </label>
                        @endif
                    </div>
                </div>

                @if($tipoReporte === 'todos')
                    <div style="margin-bottom:16px;background:#f0fff4;border:1px solid #c8e6c9;border-radius:6px;padding:12px;">
                        <div style="font-size:13px;color:#19692e;font-weight:600;margin-bottom:4px;">&#10003; Reporte general de todos los proyectos vinculados</div>
                        <div style="font-size:12px;color:#555;">Se incluirán todos los proyectos vinculados registrados en el sistema, agrupados por título y comunidad vinculada.</div>
                    </div>
                @elseif($tipoReporte === 'titulo')
                    <div style="margin-bottom:16px;">
                        <b style="font-size:13px;color:#555;display:block;margin-bottom:6px;">Seleccione el t&iacute;tulo:</b>
                        <select wire:model.live="reporteTituloId" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:13px;background:#fff;cursor:pointer;outline:none;font-family:inherit;color:#222;">
                            <option value="">&mdash; Seleccione t&iacute;tulo &mdash;</option>
                            @foreach($titulosReporte as $tid => $ttitulo)
                                <option value="{{ $tid }}" @selected((string)$reporteTituloId === (string)$tid)>{{ $ttitulo }}</option>
                            @endforeach
                        </select>
                        @if(!empty($reporteTituloId))
                            <div style="margin-top:4px;font-size:11px;color:#19692e;font-weight:600;">
                                &#10003; Seleccionado: {{ $titulosReporte[(int)$reporteTituloId] ?? '' }}
                            </div>
                        @endif
                    </div>
                @elseif($tipoReporte === 'lapso')
                    <div style="margin-bottom:16px;">
                        <b style="font-size:13px;color:#555;display:block;margin-bottom:6px;">Seleccione el lapso acad&eacute;mico:</b>
                        <select wire:model.live="reporteLapsoId" style="width:100%;padding:10px 12px;border:2px solid #8b0000;border-radius:6px;font-size:13px;background:#fff;cursor:pointer;outline:none;font-family:inherit;color:#222;">
                            <option value="">&mdash; Seleccione lapso &mdash;</option>
                            @foreach($lapsosReporte as $lid => $lnombre)
                                <option value="{{ $lid }}" @selected((string)$reporteLapsoId === (string)$lid)>{{ $lnombre }}</option>
                            @endforeach
                        </select>
                        @if(!empty($reporteLapsoId))
                            <div style="margin-top:4px;font-size:11px;color:#19692e;font-weight:600;">
                                &#10003; Seleccionado: {{ $lapsosReporte[$reporteLapsoId] ?? $reporteLapsoId }}
                            </div>
                        @endif
                        @if(empty($lapsosReporte))
                            <div style="margin-top:6px;font-size:12px;color:#888;font-style:italic;">No hay lapsos disponibles con vinculaciones registradas.</div>
                        @endif
                    </div>
                @elseif($tipoReporte === 'wizard')
                    <div style="margin-bottom:16px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:6px;padding:12px;">
                        <div style="font-size:12px;color:#555;margin-bottom:6px;">
                            Se generará un reporte con los <b>{{ count($selectedProjects) }}</b> proyecto(s) seleccionados actualmente en el wizard.
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($selectedProjects as $pid)
                                @php
                                    $p = \App\Models\Proyecto::find($pid);
                                @endphp
                                @if($p)
                                    <span style="display:inline-block;background:#fff;border:1px solid #c8e6c9;border-radius:3px;padding:2px 6px;font-size:10px;">{{ $p->titulo ?? 'ID:'.$pid }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div style="margin-top:20px;text-align:right;display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarModalReporte" style="padding:10px 24px;font-size:13px;border-radius:6px;">Cancelar</button>
                    <button type="button" class="cm-btn cm-btn-primary" wire:click="generarReporte" style="padding:10px 24px;font-size:13px;border-radius:6px;">Generar PDF</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── MODAL COMUNIDAD (crear nueva) ─── --}}
    @if ($mostrarModalComunidad)
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:10000;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:600px;width:94%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:bold;">C</div>
                    <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Nueva Comunidad</h3>
                </div>

                <div style="margin-bottom:14px;">
                    <b style="font-size:12px;color:#555;">Buscar comunidad existente:</b>
                    <input wire:model.live="buscarComunidad" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o RIF...">
                    @if($comunidadesEncontradas->isNotEmpty())
                        <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;">
                            @foreach(($comunidadesEncontradas ?? collect()) as $com)
                                <div wire:click="seleccionarComunidadModal({{ $com->id }})" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;" onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                    <b style="color:#8b0000;">{{ $com->nombre }}</b>
                                    @if($com->rif)<br><small style="color:#888;">RIF: {{ $com->rif }}</small>@endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                    <b style="font-size:13px;color:#333;">O crear nueva comunidad</b>
                </div>

                <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 8px;">
                    <tr>
                        <td width="30%" style="vertical-align:middle;"><b>Nombre:</b> <span style="color:red;">*</span></td>
                        <td>
                            <input wire:model.live.debounce.500ms="modalComunidadNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="Nombre de la comunidad">
                            @if($modalComunidadNombreStatus === 'disponible')
                                <br><span style="color:#28a745;font-size:11px;">✓ Nombre disponible</span>
                            @elseif($modalComunidadNombreStatus === 'no_disponible')
                                <br><span style="color:#dc3545;font-size:11px;">✗ Este nombre ya está en uso</span>
                            @endif
                            @error('modalComunidadNombre') <br><span style="font-size:11px;color:#c62828;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>RIF:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select wire:model.live="modalComunidadRifLetra" style="padding:4px 6px;border:1px solid #ccc;border-radius:4px;background:#fff;font-size:11px;width:48px;">
                                    <option value="V">V</option>
                                    <option value="C">C</option>
                                    <option value="J">J</option>
                                    <option value="G">G</option>
                                    <option value="P">P</option>
                                </select>
                                <input wire:model.live.debounce.500ms="modalComunidadRifNumero" type="text" inputmode="numeric" maxlength="9" style="flex:1;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Número (máx. 9 dígitos)">
                            </div>
                            @if($modalComunidadRifStatus === 'valido')
                                <br><span style="color:#28a745;font-size:11px;">✓ RIF válido</span>
                            @elseif($modalComunidadRifStatus === 'invalido')
                                <br><span style="color:#dc3545;font-size:11px;">✗ {{ $modalComunidadRifError ?? 'RIF inválido' }}</span>
                            @endif
                            <div style="font-size:10px;color:#888;margin-top:2px;">(opcional)</div>
                            @error('modalComunidadRifNumero') <br><span style="font-size:11px;color:#c62828;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Correo:</b></td>
                        <td>
                            <input wire:model="modalComunidadCorreo" type="email" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="correo@ejemplo.com">
                            <div style="font-size:10px;color:#888;margin-top:2px;">(opcional)</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Teléfono:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select wire:model="modalComunidadPrefijo" style="padding:5px 6px;border:1px solid #ccc;border-radius:4px;background:#fff;font-size:11px;">
                                    @foreach(['0424','0414','0412','0422','0416','0426'] as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                </select>
                                <input wire:model="modalComunidadTelefono" type="text" style="flex:1;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="XXX-XXXX" maxlength="7" oninput="this.value=this.value.replace(/\D/g,'').slice(0,7)">
                            </div>
                            <div style="font-size:10px;color:#888;margin-top:2px;">(opcional)</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Estado:</b> <span style="color:red;">*</span></td>
                        <td>
                            <select wire:model.live="modalComunidadEstadoId" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                                <option value="">-- Seleccione estado --</option>
                                @foreach(($estados ?? collect()) as $e)
                                    <option value="{{ $e->est_codigo }}">{{ $e->est_nombre }}</option>
                                @endforeach
                            </select>
                            @error('modalComunidadEstadoId') <br><span style="font-size:11px;color:#c62828;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Municipio:</b> <span style="color:red;">*</span></td>
                        <td>
                            <select wire:model="modalComunidadMunicipioId" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                                <option value="">-- Seleccione municipio --</option>
                                @foreach(($municipiosFiltrados ?? collect()) as $m)
                                    <option value="{{ $m->mun_codigo }}">{{ $m->mun_nombre }}</option>
                                @endforeach
                            </select>
                            @error('modalComunidadMunicipioId') <br><span style="font-size:11px;color:#c62828;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:middle;"><b>Dirección exacta:</b> <span style="color:red;">*</span></td>
                        <td>
                            <input wire:model="modalComunidadDirNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="Av./Calle/Casa Nro., sector, referencia...">
                            @error('modalComunidadDirNombre') <br><span style="font-size:11px;color:#c62828;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                </table>

                <div style="margin-top:8px;font-size:11px;color:#888;">
                    Los campos con <span style="color:red;">*</span> son obligatorios
                </div>

                <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                    <button type="button" class="cm-btn cm-btn-success" wire:click="guardarComunidadModal" style="padding:8px 20px;font-size:13px;border-radius:6px;">Guardar comunidad</button>
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarModalComunidad" style="padding:8px 20px;font-size:13px;border-radius:6px;">Cancelar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── MODAL DETALLE PROYECTO ─── --}}
    @if ($mostrarModalDetalle && $proyectoDetalle)
        @php
            $estadoColor = match($proyectoDetalle->estado_validacion) {
                'aprobado' => '#198754',
                'rechazado' => '#c62828',
                'completado' => '#0d6efd',
                default => '#f9a825'
            };
        @endphp
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:10000;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:750px;width:94%;max-height:92vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">

                {{-- Cabecera --}}
                <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:2px solid #8b0000;">
                    <div style="flex:1;">
                        <h3 style="margin:0;font-size:17px;font-weight:bold;color:#333;">{{ $proyectoDetalle->titulo ?? 'Proyecto' }}</h3>
                        @if($proyectoDetalle->equipo_resumen)
                            <div style="font-size:12px;color:#666;margin-top:4px;">{{ $proyectoDetalle->equipo_resumen }}</div>
                        @endif
                    </div>
                    <span style="display:inline-block;padding:4px 14px;border-radius:12px;font-size:12px;font-weight:bold;color:#fff;background:{{ $estadoColor }};white-space:nowrap;">
                        {{ ucfirst($proyectoDetalle->estado_validacion ?? 'N/A') }}
                    </span>
                    <button type="button" wire:click="cerrarDetalle" style="background:none;border:none;font-size:24px;cursor:pointer;color:#888;padding:0 6px;">&times;</button>
                </div>

                {{-- Resumen --}}
                @if($proyectoDetalle->resumen)
                    <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                        <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Resumen</legend>
                        <p style="margin:0;font-size:12px;color:#333;line-height:1.5;">{{ $proyectoDetalle->resumen }}</p>
                    </fieldset>
                @endif

                {{-- Clasificación --}}
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Clasificación</legend>
                    <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 5px;">
                        <tr>
                            <td width="28%" style="font-weight:bold;color:#555;padding:2px 4px;">Línea de investigación:</td>
                            <td style="padding:2px 4px;">{{ $proyectoDetalle->linea_investigacion->nombre_investigacion ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Metodología:</td>
                            <td style="padding:2px 4px;">{{ $proyectoDetalle->metodologia->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Tipo de investigación:</td>
                            <td style="padding:2px 4px;">{{ $proyectoDetalle->tipo_investigacion->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Objetivo de investigación:</td>
                            <td style="padding:2px 4px;">{{ $proyectoDetalle->objetivo_investigacion->nombre ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </fieldset>

                {{-- Comunidad --}}
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Comunidad asociada</legend>
                    @if($proyectoDetalle->comunidad)
                        <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 5px;">
                            <tr>
                                <td width="28%" style="font-weight:bold;color:#555;padding:2px 4px;">Nombre:</td>
                                <td style="padding:2px 4px;">{{ $proyectoDetalle->comunidad->nombre }}</td>
                            </tr>
                            @if($proyectoDetalle->comunidad->rif)
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">RIF:</td>
                                <td style="padding:2px 4px;">{{ $proyectoDetalle->comunidad->rif }}</td>
                            </tr>
                            @endif
                            @if($proyectoDetalle->comunidad->correo)
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">Correo:</td>
                                <td style="padding:2px 4px;">{{ $proyectoDetalle->comunidad->correo }}</td>
                            </tr>
                            @endif
                            @if($proyectoDetalle->comunidad->numero_telefono)
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">Teléfono:</td>
                                <td style="padding:2px 4px;">{{ $proyectoDetalle->comunidad->numero_telefono }}</td>
                            </tr>
                            @endif
                            @if($proyectoDetalle->comunidad->direccion)
                            <tr>
                                <td style="font-weight:bold;color:#555;padding:2px 4px;">Dirección:</td>
                                <td style="padding:2px 4px;">
                                    {{ $proyectoDetalle->comunidad->direccion->dir_calle ?? '' }}
                                    {{ $proyectoDetalle->comunidad->direccion->municipio->mun_nombre ?? '' ? ', ' . $proyectoDetalle->comunidad->direccion->municipio->mun_nombre : '' }}
                                    {{ $proyectoDetalle->comunidad->direccion->municipio->estado->est_nombre ?? '' ? ', ' . $proyectoDetalle->comunidad->direccion->municipio->estado->est_nombre : '' }}
                                </td>
                            </tr>
                            @endif
                        </table>
                    @else
                        <p style="margin:0;font-size:12px;color:#999;font-style:italic;">Sin comunidad asignada</p>
                    @endif
                </fieldset>

                {{-- Datos del equipo --}}
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;margin-bottom:14px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Datos del equipo</legend>
                    <table width="100%" style="font-size:12px;border-collapse:separate;border-spacing:0 5px;">
                        <tr>
                            <td width="28%" style="font-weight:bold;color:#555;padding:2px 4px;">Equipo / Sección:</td>
                            <td style="padding:2px 4px;">{{ $proyectoDetalle->equipo_ref ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Creador (cédula):</td>
                            <td style="padding:2px 4px;">{{ $proyectoDetalle->creador_cedula ?? 'N/A' }}</td>
                        </tr>
                        @if($proyectoDetalle->fecha_actualizacion_estudiante)
                        <tr>
                            <td style="font-weight:bold;color:#555;padding:2px 4px;">Últ. actualización:</td>
                            <td style="padding:2px 4px;">{{ $proyectoDetalle->fecha_actualizacion_estudiante ? \Carbon\Carbon::parse($proyectoDetalle->fecha_actualizacion_estudiante)->format('d/m/Y h:i A') : 'N/A' }}</td>
                        </tr>
                        @endif
                        @if($proyectoDetalle->motivo_rechazo)
                        <tr>
                            <td style="font-weight:bold;color:#c62828;padding:2px 4px;">Motivo de rechazo:</td>
                            <td style="padding:2px 4px;color:#c62828;">{{ $proyectoDetalle->motivo_rechazo }}</td>
                        </tr>
                        @endif
                    </table>
                </fieldset>

                {{-- Vinculaciones actuales --}}
                @if($proyectoDetalle->vinculaciones->isNotEmpty())
                    <fieldset style="border:1px solid #c8e6c9;border-radius:6px;padding:12px;margin-bottom:14px;background:#f1faf1;">
                        <legend style="font-weight:bold;font-size:13px;color:#19692e;padding:0 8px;">Vinculaciones</legend>
                        @foreach($proyectoDetalle->vinculaciones as $vinc)
                            <div style="font-size:12px;margin:2px 0;">
                                <strong>{{ $vinc->titulo }}</strong>
                                @if($vinc->comunidad)
                                    &rarr; {{ $vinc->comunidad->nombre }}
                                @endif
                            </div>
                        @endforeach
                    </fieldset>
                @endif

                {{-- Documentos --}}
                <fieldset style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;">
                    <legend style="font-weight:bold;font-size:13px;color:#555;padding:0 8px;">Documentos</legend>
                    @if($proyectoDetalle->documentos->isNotEmpty())
                        <table width="100%" style="font-size:11px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#8bb2b7;color:#000;">
                                    <th style="padding:6px 8px;text-align:left;">#</th>
                                    <th style="padding:6px 8px;text-align:left;">Componente</th>
                                    <th style="padding:6px 8px;text-align:left;">Archivo</th>
                                    <th style="padding:6px 8px;text-align:center;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proyectoDetalle->documentos as $doc)
                                    <tr style="border-top:1px solid #e0e0e0;">
                                        <td style="padding:5px 8px;">{{ $doc->pd_orden ?? $loop->iteration }}</td>
                                        <td style="padding:5px 8px;font-weight:bold;">{{ $doc->componente->nombre ?? 'Documento' }}</td>
                                        <td style="padding:5px 8px;">
                                            @if($doc->pd_archivo_path)
                                                <span style="font-size:10px;color:#555;">{{ basename($doc->pd_archivo_path) }}</span>
                                            @else
                                                <span style="color:#999;">Sin archivo</span>
                                            @endif
                                        </td>
                                        <td style="padding:5px 8px;text-align:center;">
                                            @if($doc->pd_estado === 1)
                                                <span style="color:#198754;font-weight:bold;">Aceptado</span>
                                            @elseif($doc->pd_estado === 2)
                                                <span style="color:#c62828;font-weight:bold;">Rechazado</span>
                                                @if($doc->pd_observacion)
                                                    <br><span style="font-size:10px;color:#888;">{{ $doc->pd_observacion }}</span>
                                                @endif
                                            @else
                                                <span style="color:#f9a825;">Pendiente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="margin:0;font-size:12px;color:#999;font-style:italic;">Sin documentos cargados</p>
                    @endif
                </fieldset>

                <div style="margin-top:18px;text-align:right;">
                    <button type="button" class="cm-btn cm-btn-secondary" wire:click="cerrarDetalle" style="padding:8px 24px;font-size:13px;">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    <script>
        var _vincTimer = null;
        var _vincCache = {};

        function buscarVinc() {
            clearTimeout(_vincTimer);
            var input = document.getElementById('vincSearchInput');
            var dropdown = document.getElementById('vincSearchAutocomplete');
            var q = input.value.trim();

            if (q.length < 2) { dropdown.style.display = 'none'; return; }

            var cacheKey = q.toLowerCase();
            if (_vincCache[cacheKey] && (Date.now() - _vincCache[cacheKey].ts < 30000)) {
                if (_vincCache[cacheKey].html) {
                    dropdown.innerHTML = _vincCache[cacheKey].html;
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
                return;
            }

            _vincTimer = setTimeout(function() {
                fetch('{{ route("proyectos.gestion.buscar-ajax") }}?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!data.length) {
                            dropdown.style.display = 'none';
                            _vincCache[cacheKey] = { html: null, ts: Date.now() };
                            return;
                        }
                        var html = '';
                        data.forEach(function(p) {
                            html += '<div class="vinc-autocomplete-item" style="padding:6px 10px;cursor:pointer;border-bottom:1px solid #eee;font-size:12px;" onclick="seleccionarVinc(\'' + p.title.replace(/'/g, "\\'") + '\')">';
                            html += '<strong>' + p.title + '</strong>';
                            html += '</div>';
                        });
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';
                        _vincCache[cacheKey] = { html: html, ts: Date.now() };
                    }).catch(function() {});
            }, 200);
        }

        function seleccionarVinc(title) {
            var dropdown = document.getElementById('vincSearchAutocomplete');
            dropdown.style.display = 'none';
            @this.set('search', title);
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#vincSearchInput') && !e.target.closest('#vincSearchAutocomplete')) {
                var d = document.getElementById('vincSearchAutocomplete');
                if (d) d.style.display = 'none';
            }
        });

        window.addEventListener('descargar-pdf', event => {
            window.open(event.detail.url, '_blank');
        });
    </script>
</div>
