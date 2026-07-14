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
                    <input wire:model.live.debounce.200ms="search" type="text" style="width: 98%;"
                        placeholder="Buscar por: título, resumen, comunidad, estudiantes...">
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
                            

                        </td>
                        <td style="padding: 5px; font-size: 10px;">{{ Str::limit($p->resumen, 100) }}</td>
                        <td align="center" style="padding: 5px;">
                            <a href="#" wire:click.prevent="openDetails({{ $p->id }})"
                                style="display:inline-block;background:#8b0000;color:#fff;padding:3px 14px;border-radius:4px;font-size:11px;font-weight:600;text-decoration:none;">Ver detalles</a>
                            @php $srchDocs = $p->documentos; @endphp
                            @if ($srchDocs->isNotEmpty())
                                <div style="margin-top:6px;border-top:1px solid #ddd;padding-top:5px;">
                                    <div style="font-size:9px;color:#666;margin-bottom:3px;">Documentos:</div>
                                    @foreach ($srchDocs as $doc)
                                        <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank"
                                            style="display:inline-block;background:#f5f5f5;border:1px solid #ddd;border-radius:3px;padding:1px 7px;margin:1px;font-size:10px;color:#333;text-decoration:none;">{{ $doc->componente?->nombre ?? 'Doc' }}</a>
                                    @endforeach
                                </div>
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
        @php
            $grupoDetalle = $selectedProject->getRelation('grupoDetalle');
            $miembros = $grupoDetalle ? ($grupoDetalle->grp_miembros ?? []) : [];
            $profesorCed = $grupoDetalle ? trim((string) ($grupoDetalle->grp_creador_cedula ?? '')) : '';
            $profesorNombre = '';
            if ($profesorCed !== '') {
                $profesorUser = \App\Models\User::where('usu_cedula', $profesorCed)->first();
                if ($profesorUser) {
                    $profesorNombre = $profesorUser->nombre;
                }
            }
            if ($profesorNombre === '' && $grupoDetalle) {
                $profesorNombre = trim((string) ($grupoDetalle->grp_contexto['creador_usuario'] ?? ''));
            }
        @endphp
        <div
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; display: flex; justify-content: center; align-items: center; overflow-y: auto;">
            <div
                style="background-color: #FFF; border: 2px solid #8b0000; border-radius: 8px; width: 880px; max-height: 92vh; overflow-y: auto; box-shadow: 0 6px 24px rgba(0,0,0,0.25);">

                {{-- Header --}}
                <div style="background:#8b0000;color:#fff;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:15px;font-weight:bold;">{{ $selectedProject->titulo }}</div>
                    </div>
                    <button type="button" wire:click="closeDetails"
                        style="background:rgba(255,255,255,0.15);border:none;font-size:20px;color:#fff;cursor:pointer;padding:0 10px;border-radius:4px;">&times;</button>
                </div>

                <div style="padding:14px 18px;">

                    {{-- Resumen + Beneficiados --}}
                    <div style="margin-bottom:12px;">
                        <div style="font-weight:bold;font-size:13px;color:#8b0000;margin-bottom:4px;">RESUMEN</div>
                        <div style="font-size:12px;color:#333;line-height:1.5;text-align:justify;">{{ $selectedProject->resumen ?: 'Sin resumen disponible.' }}</div>
                        @if($selectedProject->pry_cantidad_beneficiados)
                        <div style="margin-top:6px;padding:6px 10px;background:#f0faf0;border:1px solid #c8e6c9;border-radius:4px;display:inline-block;">
                            <b>Beneficiados:</b> <span style="font-size:18px;font-weight:bold;color:#198754;">{{ $selectedProject->pry_cantidad_beneficiados }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Dos columnas: Datos del proyecto | Clasificacion --}}
                    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:12px;">
                        <tr valign="top">
                            {{-- Columna izquierda: Datos del proyecto --}}
                            <td width="50%" style="padding-right:10px;">
                                <div style="font-weight:bold;font-size:13px;color:#555;margin-bottom:6px;padding-bottom:4px;border-bottom:1px solid #ddd;">DATOS DEL PROYECTO</div>
                                <table width="100%" cellpadding="4" cellspacing="0" style="border-collapse:collapse;">
                                    @if($profesorNombre)
                                    <tr>
                                        <td width="35%" style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">Profesor</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;color:#8b0000;font-weight:bold;">{{ $profesorNombre }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">Equipo</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->equipo_resumen }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">Comunidad</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->comunidad->nombre ?? 'N/A' }}</td>
                                    </tr>
                                    @if($selectedProject->comunidad?->rif)
                                    <tr>
                                        <td style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">RIF</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->comunidad->rif }}</td>
                                    </tr>
                                    @endif
                                    @if($selectedProject->comunidad?->direccion?->dir_calle)
                                    <tr>
                                        <td style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">Direcci&oacute;n</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->comunidad->direccion->dir_calle }}</td>
                                    </tr>
                                    @endif
                                </table>

                                <div style="font-weight:bold;font-size:13px;color:#555;margin:10px 0 6px;padding-bottom:4px;border-bottom:1px solid #ddd;">INTEGRANTES ({{ count($miembros) }})</div>
                                @forelse($miembros as $i => $m)
                                <div style="padding:3px 6px;{{ $i % 2 == 0 ? 'background:#fff;' : 'background:#f5f5f5;' }}border:1px solid #ddd;{{ $loop->first ? '' : 'border-top:none;' }}">
                                    {{ $i+1 }}. {{ $m['nombre'] ?? '' }} {{ $m['apellido'] ?? '' }}
                                </div>
                                @empty
                                <div style="padding:6px;color:#999;font-style:italic;">Sin integrantes registrados</div>
                                @endforelse
                            </td>
                            {{-- Columna derecha: Clasificacion --}}
                            <td width="50%" style="padding-left:10px;">
                                <div style="font-weight:bold;font-size:13px;color:#555;margin-bottom:6px;padding-bottom:4px;border-bottom:1px solid #ddd;">CLASIFICACI&Oacute;N</div>
                                <table width="100%" cellpadding="4" cellspacing="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td width="35%" style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">Investigaci&oacute;n</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->tipo_investigacion?->nombre ?? 'N/D' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">Metodolog&iacute;a</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->metodologia?->nombre ?? 'N/D' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">L&iacute;nea</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->linea_investigacion?->nombre_investigacion ?? 'N/D' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold;border:1px solid #ddd;background:#f5f5f5;padding:4px 6px;">Objetivo</td>
                                        <td style="border:1px solid #ddd;padding:4px 6px;">{{ $selectedProject->objetivo_investigacion?->nombre ?? 'N/D' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    {{-- Documentos --}}
                    @php $detSrchDocs = $selectedProject->documentos; @endphp
                    @if ($detSrchDocs->isNotEmpty())
                    <div style="margin-top:12px;">
                        <div style="font-weight:bold;font-size:13px;color:#555;margin-bottom:5px;">DOCUMENTOS</div>
                        <table width="100%" cellpadding="4" cellspacing="0" style="font-size:12px;border-collapse:collapse;">
                            @foreach ($detSrchDocs as $doc)
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:5px 4px;">{{ $doc->componente?->nombre ?? 'Documento' }}</td>
                                <td width="15%" align="center" style="padding:5px 4px;">
                                    @if ($doc->pd_estado === 1)
                                        <span style="color:#008000;font-weight:bold;">Aprobado</span>
                                    @elseif ($doc->pd_estado === 2)
                                        <span style="color:#c82333;font-weight:bold;">Rechazado</span>
                                    @else
                                        <span style="color:#888;">Pendiente</span>
                                    @endif
                                </td>
                                <td width="15%" align="center" style="padding:5px 4px;">
                                    <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank"
                                        style="display:inline-block;background:#8b0000;color:#fff;padding:3px 14px;border-radius:4px;font-size:11px;text-decoration:none;font-weight:600;">Ver</a>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                    @endif

                    <div style="text-align:center;margin-top:14px;">
                        <button type="button" wire:click="closeDetails" style="background:#8b0000;border:none;color:#fff;font-weight:bold;padding:6px 26px;border-radius:4px;cursor:pointer;font-size:12px;">Cerrar detalles</button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
