<div class="pgm-wrap">
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
        .cm-btn-success {
            background: #198754;
            border-color: #166f43;
            color: #fff;
        }
        .cm-btn-success:hover {
            background: #146c43;
        }
        .pgm-btn-cancel {
            background-color: #dc3545;
            color: #fff;
            border: 0 none;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        .pgm-btn-save {
            background-color: #28a745;
            color: #fff;
            border: 1px solid #218838;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Proyectos</h2>

    @if ($viewMode === 'list')
        @if (!empty($gruposDocente))
            <fieldset style="border: 2px solid #2e7d32; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
                <legend style="color: #2e7d32; font-weight: bold; font-style: italic; padding: 0 5px;">Equipos disponibles para registrar proyecto</legend>
                @if ($puedeFiltrarGrupos)
                <div style="margin-bottom: 8px; font-size: 11px;">
                    <table width="100%" border="0" cellpadding="4" cellspacing="0">
                        <tr>
                            <td width="33%"><b>Lapso:</b><br>
                                <select wire:model.live="filterGruposLapso" style="width: 95%;">
                                    <option value="">- Todos -</option>
                                    @foreach ($lapsosFiltro as $l)
                                        <option value="{{ $l->lap_codigo }}">{{ $l->lap_nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td width="33%"><b>PNF / Programa:</b><br>
                                <select wire:model.live="filterGruposPrograma" style="width: 95%;">
                                    <option value="">- Todos -</option>
                                    @foreach ($programasFiltro as $p)
                                        <option value="{{ $p->pro_codigo }}">{{ $p->pro_siglas ?? $p->pro_nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td width="34%"><b>Trayecto:</b><br>
                                <select wire:model.live="filterGruposTrayecto" style="width: 95%;">
                                    <option value="">- Todos -</option>
                                    @foreach ($trayectosFiltro as $t)
                                        <option value="{{ $t->tra_codigo }}">{{ $t->tra_nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                @endif
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #a5d6a7; color: #000; text-align: center; font-weight: bold;">
                            <th width="25%">Nombre del equipo</th>
                            <th width="15%">PNF / Sección</th>
                            <th width="10%">Integrantes</th>
                            <th width="25%">Proyecto</th>
                            <th width="25%">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach ($gruposDocente as $g)
                            @php $g = (object) $g; @endphp
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E8F5E9' : '#FFFFFF' }};"
                                valign="top">
                                <td style="padding: 5px; font-weight: bold;">{{ $g->nombre }}</td>
                                <td style="padding: 5px; font-size: 10px;">
                                    {{ $g->pro_siglas ?? '' }}@if($g->sec_nombre) · Secc. {{ $g->sec_nombre }}@endif
                                </td>
                                <td align="center" style="padding: 5px;">{{ $g->integrantes }}</td>
                                <td align="center" style="padding: 5px;">
                                    @if ($g->tiene_proyecto)
                                        @if ($g->proyecto_estado_validacion === 'aprobado')
                                            <span style="color: #008000; font-weight: bold;">Aprobado</span>
                                        @elseif($g->proyecto_estado_validacion === 'rechazado')
                                            <span style="color: #FF0000; font-weight: bold;">Rechazado</span>
                                        @else
                                            <span style="color: #d4a017; font-weight: bold;">Pendiente</span>
                                        @endif
                                    @else
                                        <span style="color: #999;">Sin proyecto</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if ($g->tiene_proyecto)
                                        <button type="button" wire:click="edit({{ $g->proyecto_id }})"
                                            class="pgm-btn-action pgm-btn-action--edit">
                                            Actualizar
                                        </button>
                                    @else
                                        <button type="button" wire:click="registrarProyectoGrupo({{ $g->grp_codigo }})"
                                            class="pgm-btn-action pgm-btn-action--approve">
                                            Actualizar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </fieldset>
        @endif
            @if($esEstudianteLider)
            <fieldset style="border: 2px solid #2e7d32; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
                <legend style="color: #2e7d32; font-weight: bold; font-style: italic; padding: 0 5px;">Mis proyectos</legend>
                @if($proyectosLider->isNotEmpty())
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #a5d6a7; color: #000; text-align: center; font-weight: bold;">
                            <th width="30%">Proyecto</th>
                            <th width="20%">Comunidad</th>
                            <th width="20%">Validación</th>
                            <th width="30%">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach ($proyectosLider as $p)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E8F5E9' : '#FFFFFF' }};"
                                valign="top">
                                <td style="padding: 5px; font-weight: bold;">
                                    {{ $p->titulo }}
                                    <br><span style="font-size: 9px; font-weight: normal;">Subido:
                                        {{ $p->fecha_subida?->format('d/m/Y') ?? '-' }}</span>
                                    @if ($p->documentos->isNotEmpty())
                                        <div style="margin-top: 3px;">
                                            @foreach ($p->documentos as $doc)
                                                <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}"
                                                    target="_blank"
                                                    style="color: #0000EE; font-size: 10px; display:block;">[{{ $doc->componente?->nombre ?? 'Documento' }}]</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 5px;">
                                    <span style="font-size: 10px;">{{ $p->comunidad->nombre ?? 'N/A' }}</span>
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if ($p->estado_validacion === 'pendiente')
                                        <span style="color: #d4a017; font-weight: bold;">Pendiente</span>
                                    @elseif($p->estado_validacion === 'completado')
                                        <span style="color: #2e7d32; font-weight: bold;">Completado</span>
                                    @elseif($p->estado_validacion === 'aprobado')
                                        <span style="color: #008000; font-weight: bold;">Aprobado</span>
                                    @elseif($p->estado_validacion === 'rechazado')
                                        <span style="color: #FF0000; font-weight: bold;"
                                            title="{{ $p->motivo_rechazo }}">Rechazado</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px;">
                                    <button type="button" wire:click="edit({{ $p->id }})"
                                        class="cm-btn cm-btn-success cm-btn-sm">
                                        Actualizar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <p style="font-size: 11px; color: #666; padding: 10px;">No tienes proyectos asignados como líder.</p>
                @endif
            </fieldset>
            @endif

            @if(!$esProfesor && !$esEstudianteLider)
            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de proyectos
                    institucionales</legend>
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 8px;">
                    <tr>
                        <td width="33%"><b>Título:</b><br>
                            <input wire:model.live.debounce.300ms="search" type="text" style="width: 95%;" placeholder="Buscar...">
                        </td>
                        <td width="33%"><b>Estado:</b><br>
                            <select wire:model.live="filterEstadoList" style="width: 95%;">
                                <option value="">- Todos -</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="completado">Completado</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </td>
                        <td width="34%"><b>Comunidad:</b><br>
                            <select wire:model.live="filterComunidadList" style="width: 95%;">
                                <option value="">- Todas -</option>
                                @foreach ($comunidades as $com)
                                    <option value="{{ $com->id }}">{{ $com->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </table>
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                            <th width="25%">Título del proyecto</th>
                            <th width="20%">Comunidad / equipo</th>
                            <th width="15%">Validación</th>
                            <th width="10%">Estado</th>
                            <th width="30%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach ($proyectos as $p)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$p->estado_logico ? 'color: #888;' : 'color: #000;' }}"
                                valign="top">
                                <td style="padding: 5px; font-weight: bold;">
                                    {{ $p->titulo }}
                                    <br><span style="font-size: 9px; font-weight: normal;">Subido:
                                        {{ $p->fecha_subida?->format('d/m/Y') ?? '-' }}</span>
                                    @php $gestionDocs = $p->documentos; @endphp
                                    @if ($gestionDocs->isNotEmpty())
                                        <div style="margin-top: 5px;">
                                            @foreach ($gestionDocs as $doc)
                                                <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}"
                                                    target="_blank"
                                                    style="color: #0000EE; font-size: 10px; display:block;">[{{ $doc->componente?->nombre ?? 'Documento' }}]</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 5px;">
                                    <span style="font-size: 11px; font-weight: bold; color: #8b0000;">Equipo:
                                        {{ $p->equipo_resumen }}</span><br>
                                    <span style="font-size: 10px;">Comunidad:
                                        {{ $p->comunidad->nombre ?? 'N/A' }}</span>
                                    @if ($p->actualizado_por_estudiante)
                                        <br><span style="background:#ffc107; padding:1px 6px; border-radius:3px; font-size:9px; font-weight:bold; color:#000;">Actualizado por líder</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if ($p->estado_validacion === 'pendiente')
                                        <span style="color: #d4a017; font-weight: bold;">Pendiente</span>
                                    @elseif($p->estado_validacion === 'completado')
                                        <span style="color: #2e7d32; font-weight: bold;">Completado</span>
                                    @elseif($p->estado_validacion === 'rechazado')
                                        <span style="color: #FF0000; font-weight: bold;"
                                            title="{{ $p->motivo_rechazo }}">Rechazado</span>
                                    @else
                                        <span style="color: #008000; font-weight: bold;">Aprobado</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if ($p->estado_logico)
                                        <span style="color: #008000; font-weight: bold;">Activo</span>
                                    @else
                                        <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px;">
                                    <div class="pgm-actions">
                                        @if (!empty($canValidate) && in_array($p->estado_validacion, ['pendiente', 'completado']))
                                            <button type="button" wire:click="approve({{ $p->id }})"
                                                onclick="return confirm('¿Aprueba este proyecto?')"
                                                class="pgm-btn-action pgm-btn-action--approve">
                                                Aprobar
                                            </button>
                                            <button type="button" wire:click="openReject({{ $p->id }})"
                                                class="pgm-btn-action pgm-btn-action--reject">
                                                Rechazar
                                            </button>
                                            <button type="button" wire:click="openDetails({{ $p->id }})"
                                                class="pgm-btn-action pgm-btn-action--details">
                                                Ficha
                                            </button>
                                        @endif
                                        @if (in_array($p->id, $proyectosLiderIds))
                                            <button type="button" wire:click="edit({{ $p->id }})"
                                                class="pgm-btn-action pgm-btn-action--edit">
                                                Actualizar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($proyectos->isEmpty())
                            <tr>
                                <td colspan="5" align="center" style="padding: 20px; font-weight: bold;">No hay
                                    expedientes registrados</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                 <div style="margin-top: 10px;">{{ $proyectos->links() }}</div>
                  </fieldset>
                  @endif
                  @elseif($viewMode === 'reject')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Motivo de rechazo
            </legend>
            <div style="margin-bottom: 15px; font-size: 12px;">Indique la justificación para no aprobar el expediente:
            </div>
            <textarea wire:model="motivo_rechazo" rows="6" style="width: 100%; max-width: 600px; padding: 5px;"></textarea>
            @error('motivo_rechazo')
                <div class="obligatorio" style="font-size: 11px; margin-top: 5px;">{{ $message }}</div>
            @enderror
            <div style="margin-top: 20px;">
                <button type="button" wire:click="irAListado()" class="pgm-btn-cancel" style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="confirmReject" class="pgm-btn-cancel" style="background-color: #f8d7da; color: #721c24; font-weight: bold;">Confirmar rechazo</button>
                </div>
                </fieldset>
                @elseif($viewMode === 'details' && $selectedProject)

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Ficha técnica del
                proyecto</legend>
            <h3 style="margin: 5px 0; font-size: 16px; font-weight: bold;">{{ $selectedProject->titulo }}</h3>
            <p style="font-size: 13px;"><b>Equipo:</b> {{ $selectedProject->equipo_resumen }}</p>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin: 15px 0;">
                <legend style="font-weight: bold; font-size: 12px;">Resumen</legend>
                <div style="font-size: 14px; text-align: justify;">{{ $selectedProject->resumen }}</div>
            </fieldset>
            <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 13px;">
                <tr>
                    <td><b>Comunidad:</b></td>
                    <td>{{ $selectedProject->comunidad->nombre ?? '-' }}</td>
                </tr>
            </table>
            @php $detDocs = $selectedProject->documentos; @endphp
            @if ($detDocs->isNotEmpty())
                <div style="margin-top: 10px; font-size: 13px;">
                    <b>Documentos:</b><br>
                    @foreach ($detDocs as $doc)
                        <a href="{{ route('documentos.serve', ['path' => $doc->pd_archivo_path]) }}" target="_blank"
                            style="color: #0000EE;">[{{ $doc->componente?->nombre ?? 'Documento' }}]</a><br>
                    @endforeach
                </div>
            @endif
            <div style="text-align: center; margin-top: 20px; border-top: 1px solid #CCC; padding-top: 15px;">
                @if ($selectedProject->estado_validacion === 'pendiente')
                    <button type="button" wire:click="approveFromDetails({{ $selectedProject->id }})"
                        onclick="return confirm('¿Aprueba este proyecto?')"
                        class="pgm-btn-action pgm-btn-action--approve">
                        Aprobar
                    </button>
                    <button type="button" wire:click="rejectFromDetails({{ $selectedProject->id }})"
                        class="pgm-btn-action pgm-btn-action--reject">
                        Rechazar
                    </button>
                @endif
                <button type="button" wire:click="irAListado()"
                    class="pgm-btn-action pgm-btn-action--edit">Regresar al listado</button>
                </div>
                </fieldset>
                @elseif($viewMode === 'form')

        <button type="button" wire:click="cancel" class="pgm-btn-volver">&laquo; Volver al listado</button>

        @if (!empty($catalogosVacios))
            <div
                style="background-color: #fff3cd; color: #856404; padding: 10px; margin: 12px 0; border: 1px solid #ffeeba; border-radius: 4px; font-size: 11px;">
                <b>Catálogos sin datos en repositorio:</b> {{ implode(', ', $catalogosVacios) }}.
                Un administrador debe cargarlos antes de poder guardar el expediente (los desplegables quedarán vacíos).
            </div>
        @endif

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $esProfesor ? 'Registro de proyecto (docente)' : ($modoActualizacion ? 'Subir documentos del proyecto' : 'Actualizar expediente') }}
            </legend>
            <form wire:submit="save">

                {{-- == SECCIÓN EQUIPO Y COMUNIDAD (arriba) == --}}
                @if (!$modoActualizacion)
                    @if(!$esProfesor)
                    <div style="margin-bottom: 15px; border: 1px solid #CCC; border-radius: 4px;">
                        <button type="button" wire:click="toggleTeamFilters"
                            style="width:100%; background:#f5f5f5; border:none; padding:8px 12px; text-align:left; font-weight:bold; font-size:12px; cursor:pointer;">
                            {{ $showTeamFilters ? '▼ Ocultar selección de equipo' : '▶ Seleccionar equipo / grupo de proyecto' }}
                        </button>
                        @if ($showTeamFilters)
                            <div style="padding:10px;">
                                <div style="padding:4px 0; margin-bottom:8px;">
                                    <select wire:model.live="filterLapsoEquipo" style="width: 32%;">
                                        <option value="">- Lapso -</option>
                                        @foreach ($lapsos as $lap)
                                            <option value="{{ $lap->id }}">{{ $lap->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model.live="filterProgramaEquipo" style="width: 32%;">
                                        <option value="">- Programa -</option>
                                        @foreach ($programasEquipo as $pro)
                                            <option value="{{ $pro->pro_codigo }}">{{ trim($pro->pro_siglas) }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model.live="filterSeccionEquipo" style="width: 32%;">
                                        <option value="">- Sección -</option>
                                        @foreach ($seccionesEquipo as $sec)
                                            <option value="{{ $sec->sec_codigo }}">{{ trim($sec->sec_nombre) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div style="margin-bottom: 8px;">
                                    <b>Seleccione el grupo de proyecto:</b><span class="obligatorio">*</span>
                                    <select wire:model.live="equipo_seccion_clave" style="width: 100%;">
                                        <option value="">Seleccione grupo de proyecto…</option>
                                        @foreach ($equipos_disp ?? [] as $eq)
                                            <option value="{{ $eq->clave }}">
                                                {{ $eq->nombre ?? $eq->clave }}
                                                @if (!empty($eq->lapso_nombre))
                                                    - {{ $eq->lapso_nombre }}
                                                @endif
                                                ({{ $eq->integrantes ?? '?' }} int.)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('equipo_seccion_clave')
                                        <span class="obligatorio">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if (!empty($equipoValidado))
                                    <div style="margin: 6px 0; padding: 6px; background: #d4edda; font-size: 10px;">
                                        <b>Validado:</b> {{ $equipoValidado->nombre }}
                                        | Lapso: {{ $equipoValidado->lap_nombre ?? '?' }}
                                        | Sección: {{ $equipoValidado->sec_nombre ?? '?' }}
                                        @if (!empty($equipoValidado->pro_siglas))
                                            | PNF: {{ $equipoValidado->pro_siglas }}
                                        @endif
                                        @if (!empty($trayecto_derived))
                                            | Trayecto: {{ $trayecto_derived }}
                                        @endif
                                        ({{ ($integrantesEquipo ?? collect())->count() }} integrantes)
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    @endif
                @endif

                @if($esProfesor)
                <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
                    <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Equipo y comunidad</legend>
                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Comunidad:</b></td>
                            <td>
                                @if($comunidadNombreGrupo)
                                    <span style="background:#f9f2f2; border:1px solid #8b0000; padding:4px 10px; border-radius:4px; font-weight:bold; color:#8b0000;">{{ $comunidadNombreGrupo }}</span>
                                @else
                                    <span style="color:#999;">(asignada automáticamente del grupo)</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    @if(!empty($miembrosGrupo))
                    <div style="margin-top: 8px; padding: 8px; background: #fcf8f8; border: 1px solid #e9aaad; border-radius: 4px; font-size: 12px;">
                        <b style="display:block; margin-bottom:4px; color:#8b0000;">Integrantes del equipo:</b>
                        <table width="100%" cellpadding="3" cellspacing="0" style="font-size: 11px; border-collapse: collapse; border: 1px solid #e9aaad;">
                            @foreach($miembrosGrupo as $miembro)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#f9f2f2' : '#FFFFFF' }}; border-bottom: 1px solid #e9aaad;">
                                <td width="5%" style="padding:4px; color:#8b0000; font-weight:bold;">{{ $loop->iteration }}.</td>
                                <td width="60%" style="padding:4px;">{{ $miembro['nombre'] }} {{ $miembro['apellido'] }}</td>
                                <td width="35%" style="padding:4px;">
                                    @if(in_array($miembro['cedula'], $selectedLeaders))
                                        <span style="color:#8b0000; font-weight:bold;">Líder</span>
                                    @else
                                        <span style="color:#666;">Integrante</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                    @endif
                </fieldset>
                @endif

                {{-- == SECCIÓN PRINCIPAL == --}}
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Datos del proyecto</legend>
                    @if ($modoActualizacion)
                        <div style="font-size: 12px; padding: 4px 0;">
                            <b>Título:</b> {{ $titulo }}<br>
                            <b>Resumen:</b> {{ $resumen }}<br>
                            <b>Fecha subida:</b> {{ $fecha_subida }}
                        </div>
                    @else
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Comunidad:</b></td>
                            <td colspan="3">
                                @if($esProfesor || $esGrupoRegistrado)
                                    <div style="padding: 4px 0; font-weight: bold; font-size: 14px;">
                                        @if($comunidadNombreGrupo)
                                            <span style="background:#f9f2f2; border:1px solid #8b0000; padding:4px 10px; border-radius:4px; font-weight:bold; color:#8b0000;">{{ $comunidadNombreGrupo }}</span>
                                        @else
                                            <span style="color:#999;">(no asignada)</span>
                                        @endif
                                    </div>
                                @else
                                    <div style="display: flex; gap: 4px; align-items: center;" x-data="{ open: false }">
                                        <div style="flex: 1; position: relative;">
                                            <input wire:model.live="buscarComunidad" type="text" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Buscar comunidad por nombre o RIF..." @focus="open = true" @click.away="open = false">
                                            <div x-show="open" style="position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ccc; border-radius: 4px; z-index: 1000; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                                @if($comunidadesEncontradas->isNotEmpty())
                                                    @foreach($comunidadesEncontradas as $com)
                                                        <div wire:click="seleccionarComunidad({{ $com->id }})" style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee; font-size: 11px;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background=''">
                                                            <b>{{ $com->nombre }}</b> @if($com->rif) <small style="color: #666;">({{ $com->rif }})</small> @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div style="padding: 8px 12px; font-size: 11px; color: #666;">No se encontraron comunidades.</div>
                                                @endif
                                            </div>
                                        </div>
                                        @if($comunidad_id)
                                            <span style="background:#e8f5e9; border:1px solid #2e7d32; padding:4px 10px; border-radius:4px; font-weight:bold; color:#2e7d32; font-size:12px; white-space:nowrap;">
                                                {{ $comunidadNombreGrupo ?? 'Seleccionada' }}
                                            </span>
                                        @endif
                                    </div>
                                     @error('comunidad_id')
                                         <span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                                     @enderror
                                 @endif
                             </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Título:</b></td>
                            <td colspan="3">
                                <div style="padding: 4px 0; font-weight: bold; font-size: 14px;">
                                    {{ $titulo ?: '(seleccione un equipo para auto-llenar el título)' }}
                                </div>
                                @error('titulo')
                                    <span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Resumen:</b></td>
                            <td colspan="3">
                                <textarea wire:model="resumen" rows="3" style="width: 95%;"></textarea><span class="obligatorio">*</span>
                                @error('resumen')
                                    <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Fecha subida:</b></td>
                            <td colspan="3"><input wire:model="fecha_subida" type="date"><span
                                    class="obligatorio">*</span> @error('fecha_subida')
                                    <span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                    </table>
                    @endif
                </fieldset>

                {{-- == SECCIÓN DOCUMENTOS POR COMPONENTE == --}}
                @if(isset($componentes_disp) && $componentes_disp->isNotEmpty())
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Documentos del proyecto por componente</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        @foreach($componentes_disp as $comp)
                            @php
                                $docActual = $archivos_actuales[$comp->id] ?? null;
                            @endphp
                            <tr>
                                <td width="25%" valign="middle"><b>{{ $comp->nombre }}</b>
                                    @if($comp->es_obligatorio)<span class="obligatorio">*</span>@endif
                                </td>
                                <td width="45%">
                                    <input type="file" wire:model="archivosComponente.{{ $comp->id }}" accept=".pdf,application/pdf" style="width: 100%;">
                                    @error('archivosComponente.' . $comp->id)
                                        <br><span class="obligatorio">{{ $message }}</span>
                                    @enderror
                                    <div wire:loading wire:target="archivosComponente.{{ $comp->id }}"
                                        style="font-size:10px;color:#0000EE;">Cargando archivo...</div>
                                </td>
                                <td width="30%">
                                    @if($docActual)
                                        <a href="{{ route('documentos.serve', ['path' => $docActual['path']]) }}" target="_blank"
                                            style="color:#0000EE; font-size:11px; font-weight:bold;">[VER {{ $comp->nombre }}]</a>
                                    @else
                                        <span style="color:#999; font-size:10px;">Sin documento</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </fieldset>
                @endif

                {{-- == SECCIÓN CLASIFICACIÓN == --}}
                <div style="margin-bottom: 15px; border: 1px solid #CCC; border-radius: 4px; padding: 10px;">
                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>L&iacute;nea de Investigaci&oacute;n:</b></td>
                            <td width="30%">
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <select wire:model="linea_investigacion_id" style="flex:1;">
                                        <option value="">Seleccione...</option>
                                        @foreach ($lineas ?? [] as $l)
                                            <option value="{{ $l->id }}">{{ $l->nombre_investigacion }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="abrirModalLinea" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space: nowrap; padding: 4px 8px; font-size: 11px;" title="Buscar o crear nueva línea">+</button>
                                </div>
                                @error('linea_investigacion_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                            <td width="20%"><b>Metodolog&iacute;a:</b></td>
                            <td width="30%">
                                <select wire:model="metodologia_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($metodologias ?? [] as $m)
                                        <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('metodologia_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Tipo de Publicaci&oacute;n:</b></td>
                            <td>
                                <select wire:model="tipo_publicacion_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($tipos_publicacion ?? [] as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_publicacion_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                            <td><b>Tipo de Investigaci&oacute;n:</b></td>
                            <td>
                                <select wire:model="tipo_investigacion_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($tipos_investigacion ?? [] as $ti)
                                        <option value="{{ $ti->id }}">{{ $ti->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_investigacion_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- == MODAL LÍNEA DE INVESTIGACIÓN == --}}
                @if ($mostrarModalLinea)
                    <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;">
                        <div style="background:#fff;border-radius:8px;padding:20px;max-width:500px;width:90%;max-height:90vh;overflow-y:auto;">
                            <h3 style="margin-top:0;font-size:16px;">Línea de Investigación</h3>

                            {{-- Buscar línea existente --}}
                            <div style="margin-bottom: 12px;">
                                <b style="font-size:12px;">Buscar línea existente:</b>
                                <input wire:model.live="buscarLinea" type="text" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;margin-top:4px;" placeholder="Escriba nombre o descripción...">
                                @if($lineasEncontradas->isNotEmpty())
                                    <div style="margin-top:4px;border:1px solid #ccc;border-radius:4px;max-height:150px;overflow-y:auto;">
                                        @foreach($lineasEncontradas as $l)
                                            <div wire:click="seleccionarLinea({{ $l->id }})" style="padding:6px 8px;cursor:pointer;border-bottom:1px solid #eee;font-size:11px;"
                                                 onmouseover="this.style.background='#e8f5e9'" onmouseout="this.style.background=''">
                                                <b>{{ $l->nombre_investigacion }}</b>
                                                @if($l->descripcion)<br><small style="color:#666;">{{ $l->descripcion }}</small>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <hr style="border:none;border-top:1px solid #ddd;margin:12px 0;">

                            {{-- Crear nueva --}}
                            <b style="font-size:12px;">O crear nueva:</b>
                            <table width="100%" style="font-size:11px;margin-top:6px;">
                                <tr>
                                    <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                    <td><input wire:model="modalLineaNombre" type="text" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;"></td>
                                </tr>
                                @error('modalLineaNombre') <tr><td></td><td style="color:red;font-size:10px;">{{ $message }}</td></tr> @enderror
                                <tr>
                                    <td valign="top"><b>Descripción:</b></td>
                                    <td><textarea wire:model="modalLineaDescripcion" rows="2" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;"></textarea></td>
                                </tr>
                                <tr>
                                    <td><b>Área:</b></td>
                                    <td><input wire:model="modalLineaArea" type="text" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;"></td>
                                </tr>
                            </table>

                            <div style="margin-top:15px;text-align:center;display:flex;gap:10px;justify-content:center;">
                                <button type="button" class="cm-btn cm-btn-success" wire:click="guardarLineaModal">Guardar línea</button>
                                <button type="button" class="cm-btn cm-btn-danger" wire:click="cerrarModalLinea">Cancelar</button>
                            </div>
                        </div>
                    </div>
                @endif

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="pgm-btn-cancel" style="margin-right: 10px;">Cancelar</button>
                    @if($esProfesor)
                        <button type="button" wire:click="cerrarFormulario" class="pgm-btn-save">Cerrar formulario</button>
                    @else
                        <button type="submit" class="pgm-btn-save">{{ $modoActualizacion ? 'Subir documentos' : ($editingId ? 'Guardar cambios' : 'Registrar proyecto') }}</button>
                    @endif
                </div>
            </form>
        </fieldset>
    @endif
</div>
