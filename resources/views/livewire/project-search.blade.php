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
                        @foreach ($secciones as $sec)
                            <option value="{{ $sec->sec_codigo }}">{{ trim($sec->sec_nombre) }}@if ($sec->pro_siglas)
                                    ({{ trim($sec->pro_siglas) }})
                                @endif
                            </option>
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
                    <th width="50%">Título / equipo / comunidad</th>
                    <th width="25%">Resumen</th>
                    <th width="25%">Acciones</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach ($proyectos as $p)
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};"
                        valign="top">
                        <td style="padding: 5px;">
                            <div style="font-weight: bold; font-size: 12px; color: #8b0000;">{{ $p->titulo }}</div>
                            <div style="font-size: 10px; color: #333;">
                                <b>Equipo:</b> {{ $p->equipo_resumen }}
                            </div>
                            @if ($p->comunidad)
                                <div style="font-size: 10px;"><b>Comunidad:</b>
                                    {{ $p->comunidad->nombre }}</div>
                            @endif
                            @if ($p->linea_investigacion)
                                <div style="font-size: 9px; color: #666;">
                                    {{ $p->linea_investigacion->nombre_investigacion }}</div>
                            @endif
                        </td>
                        <td style="padding: 5px; font-size: 10px;">{{ Str::limit($p->resumen, 100) }}</td>
                        <td align="center" style="padding: 5px;">
                            <a href="#" wire:click.prevent="openDetails({{ $p->id }})"
                                style="color: #0000EE; font-weight: bold;">[Ver detalles]</a>
                            @php $srchDocs = $p->documentos; @endphp
                            @if ($srchDocs->isNotEmpty())
                                @foreach ($srchDocs as $doc)
                                    <br><a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank"
                                        style="color: #008000; font-size: 10px;">[{{ $doc->componente?->nombre ?? 'Documento' }}]</a>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($proyectos->isEmpty())
                    <tr>
                        <td colspan="3" align="center" style="padding: 20px; font-weight: bold;">
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
                    style="display: flex; justify-content: space-between; border-bottom: 1px solid #CCC; padding-bottom: 10px; margin-bottom: 15px;">
                    <div style="width: 90%;">
                        <h3 style="margin: 5px 0; font-size: 16px; font-weight: bold;">{{ $selectedProject->titulo }}
                        </h3>
                        <span style="font-size: 10px;"><b>Equipo:</b> {{ $selectedProject->equipo_resumen }}</span>
                    </div>
                    <button type="button" wire:click="closeDetails"
                        style="background: none; border: none; font-size: 16px; color: #FF0000; cursor: pointer;">X</button>
                </div>

                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Resumen</legend>
                    <div style="font-size: 12px; text-align: justify;">{{ $selectedProject->resumen }}</div>
                </fieldset>

                <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                    <tr>
                        <td width="48%" valign="top">
                            <fieldset style="border: 1px solid #CCC; padding: 10px;">
                                <legend style="font-weight: bold; font-size: 12px;">Ficha técnica</legend>
                                <b>Publicación:</b> {{ $selectedProject->tipo_publicacion?->nombre ?? 'N/D' }}<br>
                                <b>Investigación:</b> {{ $selectedProject->tipo_investigacion?->nombre ?? 'N/D' }}<br>
                                <b>Metodología:</b> {{ $selectedProject->metodologia?->nombre ?? 'N/D' }}<br>
                                <b>Línea:</b>
                                {{ $selectedProject->linea_investigacion?->nombre_investigacion ?? 'N/D' }}
                            </fieldset>
                        </td>
                        <td width="4%"></td>
                        <td width="48%" valign="top">
                            <fieldset style="border: 1px solid #CCC; padding: 10px;">
                                <legend style="font-weight: bold; font-size: 12px;">Comunidad</legend>
                                <b>Nombre:</b> {{ $selectedProject->comunidad->nombre ?? 'N/A' }}<br>
                                <b>RIF:</b> {{ $selectedProject->comunidad->rif ?? 'N/A' }}<br>
                                <b>Dirección:</b> {{ $selectedProject->comunidad->direccion?->dir_calle ?? 'N/A' }}
                            </fieldset>
                        </td>
                    </tr>
                </table>

                @php $detSrchDocs = $selectedProject->documentos; @endphp
                @if ($detSrchDocs->isNotEmpty())
                    <fieldset style="border: 1px solid #CCC; padding: 10px; margin-top: 15px;">
                        <legend style="font-weight: bold; font-size: 12px;">Documentos</legend>
                        @foreach ($detSrchDocs as $doc)
                            <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank"
                                class="cm-btn cm-btn-secondary cm-btn-sm"
                                style="display: inline-block; margin: 4px; text-decoration: none;">
                                {{ $doc->componente?->nombre ?? 'Documento' }}
                            </a>
                        @endforeach
                    </fieldset>
                @endif

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="closeDetails" class="cm-btn cm-btn-secondary cm-btn-sm">Cerrar
                        detalles</button>
                </div>
            </div>
        </div>
    @endif
</div>
