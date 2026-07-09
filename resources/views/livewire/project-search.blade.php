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
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Consulta de Proyectos
        Institucionales</h2>

    @if (!$intranetDisponible)
        <div
            style="background-color: #fff3cd; color: #856404; padding: 8px; margin-bottom: 12px; border: 1px solid #ffeeba; font-size: 11px;">
            Filtros académicos (programa, trayecto, sección) requieren conexión con intranet. Los demás criterios siguen
            disponibles.
        </div>
    @endif

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Criterios de búsqueda
        </legend>
        <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="50%" colspan="2">
                    <b>Término (título o resumen):</b><br>
                    <input wire:model.live.debounce.300ms="search" type="text" style="width: 98%;"
                        placeholder="Palabras clave...">
                </td>
                <td width="25%">
                    <b>Lapso académico:</b><br>
                    <select wire:model.live="lapsoFilter" style="width: 95%;">
                        <option value="">- Todos los lapsos -</option>
                        @foreach ($lapsos as $l)
                            <option value="{{ $l->lap_codigo }}">{{ $l->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="25%">
                    <b>Programa:</b><br>
                    <select wire:model.live="programaFilter" style="width: 95%;" @disabled(!$lapsoFilter || !$intranetDisponible)>
                        <option value="">- Todos -</option>
                        @foreach ($programas as $pro)
                            <option value="{{ $pro->pro_codigo }}">{{ trim($pro->pro_siglas) }} -
                                {{ trim($pro->pro_nombre) }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr>
                <td width="25%">
                    <b>Trayecto:</b><br>
                    <select wire:model.live="trayectoFilter" style="width: 95%;" @disabled(!$lapsoFilter || !$intranetDisponible)>
                        <option value="">- Todos -</option>
                        @foreach ($trayectosCatalogo as $tra)
                            <option value="{{ $tra->tra_codigo }}">{{ trim($tra->tra_nombre) }}</option>
                        @endforeach
                    </select>
                </td>
                    <td width="25%">
                        <b>Sección:</b><br>
                        <select wire:model.live="seccionFilter" style="width: 95%;" @disabled(!$lapsoFilter || !$intranetDisponible)>
                            <option value="">- Todas -</option>
                            @foreach ($secciones->merge($seccionesDesdeGrupos)->unique('sec_codigo') as $sec)
                        @php $secLabel = trim($sec->sec_nombre ?? '') . (!empty($sec->pro_siglas) ? ' (' . trim($sec->pro_siglas) . ')' : ''); @endphp
                                <option value="{{ $sec->sec_codigo }}">{{ $secLabel }}</option>
                            @endforeach
                        </select>
                    </td>
                <td width="25%">
                    <b>Comunidad:</b><br>
                    <select wire:model.live="comunidadFilter" style="width: 95%;">
                        <option value="">- Todas -</option>
                        @foreach ($comunidades as $com)
                            <option value="{{ $com->id }}">{{ $com->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="25%">
                    <b>Línea de investigación:</b><br>
                    <select wire:model.live="lineaFilter" style="width: 95%;">
                        <option value="">- Todas -</option>
                        @foreach ($lineas as $lin)
                            <option value="{{ $lin->id }}">{{ Str::limit($lin->nombre_investigacion, 35) }}
                            </option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr>
                <td width="33%">
                    <b>Tipo de publicación:</b><br>
                    <select wire:model.live="tipoPublicacionFilter" style="width: 95%;">
                        <option value="">- Todos -</option>
                        @foreach ($tipos_publicacion as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="33%">
                    <b>Tipo de investigación:</b><br>
                    <select wire:model.live="tipoInvestigacionFilter" style="width: 95%;">
                        <option value="">- Todos -</option>
                        @foreach ($tipos_investigacion as $ti)
                            <option value="{{ $ti->id }}">{{ $ti->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td width="34%" colspan="2">
                    <b>Metodología:</b><br>
                    <select wire:model.live="metodologiaFilter" style="width: 95%;">
                        <option value="">- Todas -</option>
                        @foreach ($metodologias as $mei)
                            <option value="{{ $mei->id }}">{{ $mei->nombre }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="4" align="right" style="padding-top: 6px;">
                    <button type="button" wire:click="limpiarFiltros" class="cm-btn cm-btn-secondary cm-btn-sm">
                        Limpiar filtros
                    </button>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset
        style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px; min-height: 220px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Resultados de la búsqueda
        </legend>

        <table width="100%" border="1" cellpadding="4" cellspacing="0"
            style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px; min-height: 160px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th width="4%">N&deg;</th>
                    <th width="48%">Título / equipo / comunidad</th>
                    <th width="24%">Resumen</th>
                    <th width="24%">Acciones</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach ($proyectos as $p)
                    @php $rowNum = ($proyectos->currentPage() - 1) * $proyectos->perPage() + $loop->iteration; @endphp
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};"
                        valign="top">
                        <td align="center" style="padding: 5px; font-weight: bold; font-size: 11px;">{{ $rowNum }}</td>
                        <td style="padding: 5px;">
                            <div style="font-weight: bold; font-size: 12px; color: #8b0000;">{{ $p->titulo }}</div>
                            <div style="font-size: 10px; color: #333;">
                                <b>Equipo:</b> {{ $p->equipo_resumen }}
                            </div>
                            @if ($p->comunidad)
                                <div style="font-size: 10px;"><b>Comunidad original:</b> {{ $p->comunidad->nombre }}</div>
                            @endif
                            
                            {{-- Vinculación asociada --}}
                            @if($p->vinculaciones && $p->vinculaciones->isNotEmpty())
                                @foreach($p->vinculaciones as $v)
                                    <div style="font-size: 10px; margin-top: 3px; padding: 2px 5px; background: #fff8f0; border-left: 2px solid #8b0000; border-radius: 3px;">
                                        <div><b>T&iacute;tulo Vinculaci&oacute;n:</b> <span style="color:#8b0000; font-weight:bold;">{{ $v->tituloVinculacion?->titulo }}</span></div>
                                        @if($v->comunidad)
                                            <div><b>Comunidad Vinculada:</b> <span style="font-weight:600;">{{ $v->comunidad->nombre }}</span></div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </td>
                        <td style="padding: 5px; font-size: 10px;">{{ Str::limit($p->resumen, 100) }}</td>
                        <td align="center" style="padding: 5px;">
                            <a href="#" wire:click.prevent="openDetails({{ $p->id }})"
                                style="color:#0000EE; font-weight:bold;">Ver detalles</a>
                            @php $srchDocs = $p->documentos; @endphp
                            @if ($srchDocs->isNotEmpty())
                                <br><span style="font-size:9px; color:#666;">Docs:</span>
                                @foreach ($srchDocs as $doc)
                                    <a href="{{ route('documentos.view', $doc->pd_codigo) }}"
                                        style="color:#0000EE; font-size:10px;">{{ $doc->componente?->nombre ?? 'Doc' }}</a>@if (!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($proyectos->isEmpty())
                    <tr>
                        <td colspan="4" align="center" style="padding: 20px; font-weight: bold;">
                            No se encontraron proyectos con los criterios seleccionados
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 10px;">{{ $proyectos->links() }}</div>
    </fieldset>

    @if ($isDetailsModalOpen && $selectedProject)
        <div
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; display: flex; justify-content: center; align-items: center; overflow-y: auto;">
            <div
                style="background-color: #FFF; border: 2px solid #8b0000; border-radius: 6px; padding: 20px; width: 850px; max-height: 90vh; overflow-y: auto;">
                <div
                    style="display: flex; justify-content: space-between; border-bottom: 2px solid #8b0000; padding-bottom: 10px; margin-bottom: 15px;">
                    <div style="width: 90%;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: bold; color: #8b0000;">{{ $selectedProject->titulo }}
                        </h3>
                        <span style="font-size: 11px;"><b>Equipo:</b> {{ $selectedProject->equipo_resumen }}</span>
                    </div>
                    <button type="button" wire:click="closeDetails"
                        style="background: #8b0000; border: none; font-size: 14px; color: #FFF; cursor: pointer; font-weight: bold; padding: 2px 8px; border-radius: 3px;">X</button>
                </div>

                <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 10px;">
                    <tr>
                        <td width="50%" valign="top">
                            @if($selectedProject->vinculaciones && $selectedProject->vinculaciones->isNotEmpty())
                                @php $v = $selectedProject->vinculaciones->first(); @endphp
                                <fieldset style="border: 1px solid #8b0000; padding: 8px; height: 100%; background: #fff8f0; border-radius: 4px;">
                                    <legend style="font-weight: bold; font-size: 11px; color: #8b0000;">Vinculaci&oacute;n y Equipo</legend>
                                    <b>T&iacute;tulo Vinculaci&oacute;n:</b> <span style="font-size: 12px; color: #8b0000; font-weight: bold;">{{ $v->tituloVinculacion?->titulo }}</span><br>
                                    @if($v->comunidad)
                                        <b>Comunidad Vinculada:</b> <span style="font-weight: bold;">{{ $v->comunidad->nombre }}</span><br>
                                        @if($v->comunidad->rif) <b>RIF:</b> {{ $v->comunidad->rif }}<br> @endif
                                        @if($v->comunidad->numero_telefono) <b>Tel&eacute;fono:</b> {{ $v->comunidad->numero_telefono }}<br> @endif
                                    @endif
                                    <b>Equipo:</b> {{ $selectedProject->equipo_resumen }}
                                </fieldset>
                            @else
                                <fieldset style="border: 1px solid #CCC; padding: 8px; height: 100%;">
                                    <legend style="font-weight: bold; font-size: 11px;">Informaci&oacute;n del equipo</legend>
                                    <b>Equipo:</b> {{ $selectedProject->equipo_resumen }}<br>
                                    <span style="color:#666; font-style:italic;">Sin vinculaci&oacute;n registrada</span>
                                </fieldset>
                            @endif
                        </td>
                        <td width="50%" valign="top">
                            <fieldset style="border: 1px solid #CCC; padding: 8px; height: 100%;">
                                <legend style="font-weight: bold; font-size: 11px;">Comunidad original</legend>
                                <b>Nombre:</b> {{ $selectedProject->comunidad->nombre ?? 'N/A' }}<br>
                                <b>RIF:</b> {{ $selectedProject->comunidad->rif ?? 'N/A' }}<br>
                                <b>Direcci&oacute;n:</b> {{ $selectedProject->comunidad->direccion?->dir_calle ?? 'N/A' }}
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <fieldset style="border: 1px solid #CCC; padding: 8px; margin-bottom: 10px;">
                    <legend style="font-weight: bold; font-size: 11px;">Detalles del Proyecto</legend>
                    <div style="margin-bottom: 8px;">
                        <b>T&iacute;tulo del Proyecto:</b><br>
                        <span style="font-size: 12px; font-weight: bold; color: #111;">{{ $selectedProject->titulo }}</span>
                    </div>
                    <div style="border-top: 1px solid #eee; padding-top: 6px;">
                        <b>Resumen:</b><br>
                        <div style="font-size: 11px; text-align: justify; line-height: 1.5; margin-top: 2px;">{{ $selectedProject->resumen ?: 'Sin resumen disponible.' }}</div>
                    </div>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 8px; margin-bottom: 10px;">
                    <legend style="font-weight: bold; font-size: 11px;">Ficha t&eacute;cnica</legend>
                    <table width="100%" cellpadding="3" cellspacing="0" style="font-size: 11px;">
                        <tr>
                            <td width="25%"><b>Publicaci&oacute;n:</b></td>
                            <td width="25%">{{ $selectedProject->tipo_publicacion?->nombre ?? 'N/D' }}</td>
                            <td width="25%"><b>Investigaci&oacute;n:</b></td>
                            <td width="25%">{{ $selectedProject->tipo_investigacion?->nombre ?? 'N/D' }}</td>
                        </tr>
                        <tr>
                            <td><b>Metodolog&iacute;a:</b></td>
                            <td>{{ $selectedProject->metodologia?->nombre ?? 'N/D' }}</td>
                            <td><b>L&iacute;nea de investigaci&oacute;n:</b></td>
                            <td>{{ $selectedProject->linea_investigacion?->nombre_investigacion ?? 'N/D' }}</td>
                        </tr>
                        <tr>
                            <td><b>Objetivo de investigaci&oacute;n:</b></td>
                            <td colspan="3">{{ $selectedProject->objetivo_investigacion?->nombre ?? 'N/D' }}</td>
                        </tr>
                    </table>
                </fieldset>

                        @php $detSrchDocs = $selectedProject->documentos; @endphp
                @if ($detSrchDocs->isNotEmpty())
                    <fieldset style="border: 1px solid #CCC; padding: 8px; margin-bottom: 10px;">
                        <legend style="font-weight: bold; font-size: 11px;">Documentos</legend>
                        <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 11px; border-collapse: collapse;">
                            @foreach ($detSrchDocs as $doc)
                                <tr style="border-bottom: 1px solid #EEE;">
                                    <td width="60%" style="padding: 4px;">{{ $doc->componente?->nombre ?? 'Documento' }}</td>
                                    <td width="20%" align="center" style="padding: 4px;">
                                        @if ($doc->pd_estado === 1)
                                            <span style="color: green; font-weight: bold;">Aprobado</span>
                                        @elseif ($doc->pd_estado === 2)
                                            <span style="color: red; font-weight: bold;">Rechazado</span>
                                        @else
                                            <span style="color: #888;">Pendiente</span>
                                        @endif
                                    </td>
                                    <td width="20%" align="center" style="padding: 4px;">
                                        <a href="{{ route('documentos.view', $doc->pd_codigo) }}"
                                            style="color: #0000EE;">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </fieldset>
                @endif

                <div style="text-align: center; margin-top: 15px;">
                    <button type="button" wire:click="closeDetails" style="background: #8b0000; border: none; color: #FFF; font-weight: bold; padding: 6px 20px; border-radius: 4px; cursor: pointer; font-size: 12px;">Cerrar detalles</button>
                </div>
            </div>
        </div>
    @endif
</div>
