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
                <div class="validation-error">{{ $message }}</div>
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

                {{-- == SECCIÓN DATOS DEL PROYECTO (PRIMERO) == --}}
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Datos del proyecto</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Comunidad:</b></td>
                            <td colspan="3">
                                @if($comunidadNombreGrupo)
                                    <span style="background:#f9f2f2; border:1px solid #8b0000; padding:4px 10px; border-radius:4px; font-weight:bold; color:#8b0000;">{{ $comunidadNombreGrupo }}</span>
                                @else
                                    <span style="color:#999;">(asignada autom&aacute;ticamente del grupo)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>T&iacute;tulo:</b></td>
                            <td colspan="3">
                                <div style="padding: 4px 0; font-weight: bold; font-size: 14px;">
                                    {{ $titulo ?: '(seleccione un equipo para auto-llenar el t&iacute;tulo)' }}
                                </div>
                                @error('titulo')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Resumen:</b></td>
                            <td colspan="3">
                                <textarea wire:model="resumen" rows="3" style="width: 95%;"></textarea><span class="obligatorio">*</span>
                                @error('resumen')
                                    <br><span class="validation-error">{{ $message }}</span>
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
                </fieldset>

                {{-- == SECCIÓN EQUIPO / GRUPO == --}}
                @if (!$modoActualizacion)
                    @if(!$esProfesor && !$esGestionador)
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

                @if($esProfesor || $esGestionador)
                <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
                    <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Equipo</legend>
                    @if(!empty($miembrosGrupo))
                    <div style="margin-top: 8px; padding: 0; background: #fff; border: 1px solid #e9aaad; border-radius: 6px; font-size: 12px; overflow: hidden;">
                        <div style="background: linear-gradient(135deg, #8b0000, #a52a2a); color: #fff; padding: 6px 12px; font-weight: bold; font-size: 13px; letter-spacing: 0.3px;">
                            👥 Integrantes del equipo ({{ count($miembrosGrupo) }})
                        </div>
                        <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 12px;">
                            @foreach($miembrosGrupo as $idx => $miembro)
                            @php
                                $esLider = in_array($miembro['cedula'], $selectedLeaders);
                            @endphp
                            <tr style="background-color: {{ $idx % 2 == 0 ? '#fafafa' : '#FFFFFF' }}; border-bottom: 1px solid #f0e6e6;">
                                <td width="36" style="padding: 6px 4px 6px 12px; text-align:center;">
                                    <div style="width:28px; height:28px; border-radius:50%; background:{{ $esLider ? '#8b0000' : '#d4c5c5' }}; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold;">
                                        {{ $idx + 1 }}
                                    </div>
                                </td>
                                <td width="40" style="padding: 6px 2px;">
                                    @if($esLider)
                                        <span style="display:inline-block; background:#8b0000; color:#fff; padding:2px 8px; border-radius:10px; font-size:9px; font-weight:bold; letter-spacing:0.5px;">LÍDER</span>
                                    @else
                                        <span style="display:inline-block; background:#e8e0e0; color:#666; padding:2px 8px; border-radius:10px; font-size:9px; font-weight:bold;">AUTOR</span>
                                    @endif
                                </td>
                                <td style="padding: 6px 4px; font-weight:{{ $esLider ? 'bold' : 'normal' }}; color:{{ $esLider ? '#8b0000' : '#333' }};">
                                    {{ $miembro['nombre'] }} {{ $miembro['apellido'] }}
                                    <span style="color:#999; font-size:10px; font-weight:normal;"> ({{ $miembro['cedula'] }})</span>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                        @if(count($selectedLeaders) > 0)
                        <div style="padding: 6px 12px; background: #f9f2f2; border-top: 1px solid #e9aaad; font-size: 10px; color: #8b0000;">
                            @php $lideresNombres = array_filter($miembrosGrupo, fn($m) => in_array($m['cedula'], $selectedLeaders)); @endphp
                            <b>Líder{{ count($lideresNombres) > 1 ? 'es' : '' }}:</b>
                            {{ implode(', ', array_map(fn($m) => $m['nombre'] . ' ' . $m['apellido'], $lideresNombres)) }}
                        </div>
                        @endif
                    </div>
                    @endif
                </fieldset>
                @endif

                {{-- == SECCIÓN DOCUMENTOS POR COMPONENTE == --}}
                @php
                    $tieneComponentes = isset($componentes_disp) && $componentes_disp->isNotEmpty();
                @endphp
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Documentos del proyecto por componente
                        @if($tieneComponentes)
                            <span style="font-weight:normal;font-size:10px;color:#666;"> ({{ $componentes_disp->count() }} componente(s))</span>
                        @endif
                    </legend>

                    @if($tieneComponentes)
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        @foreach($componentes_disp as $comp)
                            @php
                                $docActual = $archivos_actuales[$comp->id] ?? null;
                                $acceptStr = $comp->accept ?? '.pdf';
                                $maxMb = $comp->tamano_maximo_mb ?? 10;
                                $maxKb = $maxMb * 1024;
                            @endphp
                            <tr>
                                <td width="25%" valign="middle">
                                    <b>{{ $comp->nombre }}</b>
                                    @if($comp->es_obligatorio)<span class="obligatorio">*</span>@endif
                                    <br><span style="font-size:9px;color:#666;">{{ strtoupper($comp->tipo_archivo ?? 'PDF') }} &middot; M&aacute;x {{ $maxMb }}MB</span>
                                </td>
                                <td width="45%">
                                    <input type="file" wire:model="archivosComponente.{{ $comp->id }}" accept="{{ $acceptStr }}" style="width: 100%;">
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
                                        <div style="display:flex;flex-direction:column;gap:2px;">
                                            <span style="color:#999; font-size:10px;">Sin documento</span>
                                            <span style="font-size:9px;color:#bbb;">({{ $comp->tipo_archivo ?? 'pdf' }} &middot; max {{ $maxMb }}MB)</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    @else
                        <div style="padding: 12px; background: #fff8e1; border: 1px solid #ffe082; border-radius: 4px; font-size: 11px; color: #6d4c00;">
                            <b>⚠ No hay componentes configurados para este programa.</b><br>
                            Un administrador debe ir a <b>Configuración &gt; Componentes</b> y crear los
                            componentes documentales (ej. "Informe Final", "Plan de Trabajo", etc.)
                            asociados al programa correspondiente.
                        </div>
                    @endif
                </fieldset>

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
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <select wire:model="metodologia_id" style="flex:1;">
                                        <option value="">Seleccione...</option>
                                        @foreach ($metodologias ?? [] as $m)
                                            <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="abrirModalMetodologia" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space: nowrap; padding: 4px 8px; font-size: 11px;" title="Buscar o crear nueva metodología">+</button>
                                </div>
                                @error('metodologia_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Tipo de Publicaci&oacute;n:</b></td>
                            <td>
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <select wire:model="tipo_publicacion_id" style="flex:1;">
                                        <option value="">Seleccione...</option>
                                        @foreach ($tipos_publicacion ?? [] as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="abrirModalTipoPublicacion" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space: nowrap; padding: 4px 8px; font-size: 11px;" title="Buscar o crear nuevo tipo de publicación">+</button>
                                </div>
                                @error('tipo_publicacion_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                            <td><b>Objetivo:</b></td>
                            <td>
                                <select wire:model="objetivo_id" style="width: 100%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($objetivos ?? [] as $obj)
                                        <option value="{{ $obj->id }}">{{ $obj->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('objetivo_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                            <td><b>Tipo de Investigaci&oacute;n:</b></td>
                            <td>
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <select wire:model="tipo_investigacion_id" style="flex:1;">
                                        <option value="">Seleccione...</option>
                                        @foreach ($tipos_investigacion ?? [] as $ti)
                                            <option value="{{ $ti->id }}">{{ $ti->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="abrirModalTipoInvestigacion" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space: nowrap; padding: 4px 8px; font-size: 11px;" title="Buscar o crear nuevo tipo de investigación">+</button>
                                </div>
                                @error('tipo_investigacion_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Objetivo de Investigaci&oacute;n:</b></td>
                            <td colspan="3">
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <select wire:model="objetivo_investigacion_id" style="flex:1;">
                                        <option value="">Seleccione...</option>
                                        @foreach ($objetivos_investigacion ?? [] as $oi)
                                            <option value="{{ $oi->id }}">{{ $oi->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="abrirModalObjetivo" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space: nowrap; padding: 4px 8px; font-size: 11px;" title="Buscar o crear nuevo objetivo de investigación">+</button>
                                </div>
                                @error('objetivo_investigacion_id')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- == MODAL LÍNEA DE INVESTIGACIÓN == --}}
                @if ($mostrarModalLinea)
                    <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
                        <div style="background:#fff;border-radius:10px;padding:24px;max-width:520px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                                <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;">🔬</div>
                                <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Línea de Investigación</h3>
                            </div>

                            {{-- Buscar línea existente --}}
                            <div style="margin-bottom: 14px;">
                                <b style="font-size:12px;color:#555;">Buscar línea existente:</b>
                                <input wire:model.live="buscarLinea" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o descripción...">
                                @if($lineasEncontradas->isNotEmpty())
                                    <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                                        @foreach($lineasEncontradas as $l)
                                            <div wire:click="seleccionarLinea({{ $l->id }})" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;transition:background 0.15s;"
                                                 onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                                <b style="color:#8b0000;">{{ $l->nombre_investigacion }}</b>
                                                @if($l->descripcion)<br><small style="color:#888;">{{ Str::limit($l->descripcion, 80) }}</small>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if($buscarLinea && $lineasEncontradas->isEmpty())
                                    <div style="margin-top:4px;font-size:11px;color:#999;padding:4px 0;">No se encontraron líneas. Cree una nueva abajo.</div>
                                @endif
                            </div>

                            <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                            {{-- Crear nueva --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                                <b style="font-size:13px;color:#333;">O crear nueva línea</b>
                            </div>
                            <table width="100%" style="font-size:12px;margin-top:4px;border-collapse:separate;border-spacing:0 6px;">
                                <tr>
                                    <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                    <td><input wire:model="modalLineaNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></td>
                                </tr>
                                @error('modalLineaNombre') <tr><td></td><td class="validation-error">⚠ {{ $message }}</td></tr> @enderror
                                <tr>
                                    <td valign="top"><b>Descripción:</b></td>
                                    <td><textarea wire:model="modalLineaDescripcion" rows="2" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></textarea></td>
                                </tr>
                                <tr>
                                    <td><b>Área:</b></td>
                                    <td><input wire:model="modalLineaArea" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></td>
                                </tr>
                            </table>

                            <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                                <button type="button" class="cm-btn cm-btn-success" wire:click="guardarLineaModal" style="padding:8px 20px;font-size:13px;">Guardar línea</button>
                                <button type="button" class="cm-btn cm-btn-danger" wire:click="cerrarModalLinea" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- == MODAL METODOLOGÍA == --}}
                @if ($mostrarModalMetodologia)
                    <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
                        <div style="background:#fff;border-radius:10px;padding:24px;max-width:520px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                                <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;">📋</div>
                                <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Metodología de Investigación</h3>
                            </div>

                            {{-- Buscar metodología existente --}}
                            <div style="margin-bottom: 14px;">
                                <b style="font-size:12px;color:#555;">Buscar metodología existente:</b>
                                <input wire:model.live="buscarMetodologia" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o descripción...">
                                @if($metodologiasEncontradas->isNotEmpty())
                                    <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                                        @foreach($metodologiasEncontradas as $m)
                                            <div wire:click="seleccionarMetodologia({{ $m->id }})" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;transition:background 0.15s;"
                                                 onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                                <b style="color:#8b0000;">{{ $m->nombre }}</b>
                                                @if($m->descripcion)<br><small style="color:#888;">{{ Str::limit($m->descripcion, 80) }}</small>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if($buscarMetodologia && $metodologiasEncontradas->isEmpty())
                                    <div style="margin-top:4px;font-size:11px;color:#999;padding:4px 0;">No se encontraron metodologías. Cree una nueva abajo.</div>
                                @endif
                            </div>

                            <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                            {{-- Crear nueva --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                                <b style="font-size:13px;color:#333;">O crear nueva metodología</b>
                            </div>
                            <table width="100%" style="font-size:12px;margin-top:4px;border-collapse:separate;border-spacing:0 6px;">
                                <tr>
                                    <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                    <td><input wire:model="modalMetodologiaNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></td>
                                </tr>
                                @error('modalMetodologiaNombre') <tr><td></td><td class="validation-error">⚠ {{ $message }}</td></tr> @enderror
                                <tr>
                                    <td valign="top"><b>Descripción:</b></td>
                                    <td><textarea wire:model="modalMetodologiaDescripcion" rows="2" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></textarea></td>
                                </tr>
                            </table>

                            <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                                <button type="button" class="cm-btn cm-btn-success" wire:click="guardarMetodologiaModal" style="padding:8px 20px;font-size:13px;">Guardar metodología</button>
                                <button type="button" class="cm-btn cm-btn-danger" wire:click="cerrarModalMetodologia" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- == MODAL TIPO DE INVESTIGACIÓN == --}}
                @if ($mostrarModalTipoInvestigacion)
                    <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
                        <div style="background:#fff;border-radius:10px;padding:24px;max-width:520px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                                <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;">🔬</div>
                                <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Tipo de Investigación</h3>
                            </div>

                            {{-- Buscar existente --}}
                            <div style="margin-bottom: 14px;">
                                <b style="font-size:12px;color:#555;">Buscar tipo existente:</b>
                                <input wire:model.live="buscarTipoInvestigacion" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o descripción...">
                                @if($tiposInvestigacionEncontradas->isNotEmpty())
                                    <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                                        @foreach($tiposInvestigacionEncontradas as $ti)
                                            <div wire:click="seleccionarTipoInvestigacion({{ $ti->id }})" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;transition:background 0.15s;"
                                                 onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                                <b style="color:#8b0000;">{{ $ti->nombre }}</b>
                                                @if($ti->descripcion)<br><small style="color:#888;">{{ Str::limit($ti->descripcion, 80) }}</small>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if($buscarTipoInvestigacion && $tiposInvestigacionEncontradas->isEmpty())
                                    <div style="margin-top:4px;font-size:11px;color:#999;padding:4px 0;">No se encontraron tipos. Cree uno nuevo abajo.</div>
                                @endif
                            </div>

                            <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                            {{-- Crear nuevo --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                                <b style="font-size:13px;color:#333;">O crear nuevo tipo</b>
                            </div>
                            <table width="100%" style="font-size:12px;margin-top:4px;border-collapse:separate;border-spacing:0 6px;">
                                <tr>
                                    <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                    <td><input wire:model="modalTipoInvNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></td>
                                </tr>
                                @error('modalTipoInvNombre') <tr><td></td><td class="validation-error">⚠ {{ $message }}</td></tr> @enderror
                                <tr>
                                    <td valign="top"><b>Descripción:</b></td>
                                    <td><textarea wire:model="modalTipoInvDescripcion" rows="2" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></textarea></td>
                                </tr>
                            </table>

                            <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                                <button type="button" class="cm-btn cm-btn-success" wire:click="guardarTipoInvestigacionModal" style="padding:8px 20px;font-size:13px;">Guardar tipo</button>
                                <button type="button" class="cm-btn cm-btn-danger" wire:click="cerrarModalTipoInvestigacion" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- == MODAL TIPO DE PUBLICACIÓN == --}}
                @if ($mostrarModalTipoPublicacion)
                    <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
                        <div style="background:#fff;border-radius:10px;padding:24px;max-width:520px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                                <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;">📄</div>
                                <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Tipo de Publicación</h3>
                            </div>

                            {{-- Buscar existente --}}
                            <div style="margin-bottom: 14px;">
                                <b style="font-size:12px;color:#555;">Buscar tipo existente:</b>
                                <input wire:model.live="buscarTipoPublicacion" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre...">
                                @if($tiposPublicacionEncontradas->isNotEmpty())
                                    <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                                        @foreach($tiposPublicacionEncontradas as $tp)
                                            <div wire:click="seleccionarTipoPublicacion({{ $tp->id }})" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;transition:background 0.15s;"
                                                 onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                                <b style="color:#8b0000;">{{ $tp->nombre }}</b>
                                                @if($tp->mencion_honorifica)<br><small style="color:#888;">(Mención honorífica)</small>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if($buscarTipoPublicacion && $tiposPublicacionEncontradas->isEmpty())
                                    <div style="margin-top:4px;font-size:11px;color:#999;padding:4px 0;">No se encontraron tipos. Cree uno nuevo abajo.</div>
                                @endif
                            </div>

                            <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                            {{-- Crear nuevo --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                                <b style="font-size:13px;color:#333;">O crear nuevo tipo</b>
                            </div>
                            <table width="100%" style="font-size:12px;margin-top:4px;border-collapse:separate;border-spacing:0 6px;">
                                <tr>
                                    <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                    <td><input wire:model="modalTipoPubNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></td>
                                </tr>
                                @error('modalTipoPubNombre') <tr><td></td><td class="validation-error">⚠ {{ $message }}</td></tr> @enderror
                                <tr>
                                    <td><b>Men. honorífica:</b></td>
                                    <td>
                                        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                                            <input wire:model="modalTipoPubMencionHonorifica" type="checkbox" style="width:16px;height:16px;cursor:pointer;">
                                            ¿Tiene mención honorífica?
                                        </label>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                                <button type="button" class="cm-btn cm-btn-success" wire:click="guardarTipoPublicacionModal" style="padding:8px 20px;font-size:13px;">Guardar tipo</button>
                                <button type="button" class="cm-btn cm-btn-danger" wire:click="cerrarModalTipoPublicacion" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- == MODAL OBJETIVO DE INVESTIGACIÓN == --}}
                @if ($mostrarModalObjetivo)
                    <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;">
                        <div style="background:#fff;border-radius:10px;padding:24px;max-width:520px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                                <div style="width:36px;height:36px;border-radius:50%;background:#8b0000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;">🎯</div>
                                <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Objetivo de Investigación</h3>
                            </div>

                            {{-- Buscar existente --}}
                            <div style="margin-bottom: 14px;">
                                <b style="font-size:12px;color:#555;">Buscar objetivo existente:</b>
                                <input wire:model.live="buscarObjetivo" type="text" style="width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box;margin-top:4px;font-size:13px;" placeholder="Escriba nombre o descripción...">
                                @if($objetivosEncontrados->isNotEmpty())
                                    <div style="margin-top:6px;border:1px solid #e0e0e0;border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                                        @foreach($objetivosEncontrados as $oi)
                                            <div wire:click="seleccionarObjetivo({{ $oi->id }})" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:12px;transition:background 0.15s;"
                                                 onmouseover="this.style.background='#f5f0f0';this.style.borderLeft='3px solid #8b0000'" onmouseout="this.style.background='';this.style.borderLeft=''">
                                                <b style="color:#8b0000;">{{ $oi->nombre }}</b>
                                                @if($oi->descripcion)<br><small style="color:#888;">{{ Str::limit($oi->descripcion, 80) }}</small>@endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if($buscarObjetivo && $objetivosEncontrados->isEmpty())
                                    <div style="margin-top:4px;font-size:11px;color:#999;padding:4px 0;">No se encontraron objetivos. Cree uno nuevo abajo.</div>
                                @endif
                            </div>

                            <hr style="border:none;border-top:1px solid #e8e8e8;margin:14px 0;">

                            {{-- Crear nuevo --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:24px;height:24px;border-radius:50%;background:#198754;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;">+</div>
                                <b style="font-size:13px;color:#333;">O crear nuevo objetivo</b>
                            </div>
                            <table width="100%" style="font-size:12px;margin-top:4px;border-collapse:separate;border-spacing:0 6px;">
                                <tr>
                                    <td width="30%"><b>Nombre:</b> <span style="color:red;">*</span></td>
                                    <td><input wire:model="modalObjetivoNombre" type="text" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></td>
                                </tr>
                                @error('modalObjetivoNombre') <tr><td></td><td style="color:#dc3545;font-size:11px;">⚠ {{ $message }}</td></tr> @enderror
                                <tr>
                                    <td valign="top"><b>Descripción:</b></td>
                                    <td><textarea wire:model="modalObjetivoDescripcion" rows="2" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></textarea></td>
                                </tr>
                            </table>

                            <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                                <button type="button" class="cm-btn cm-btn-success" wire:click="guardarObjetivoModal" style="padding:8px 20px;font-size:13px;">Guardar objetivo</button>
                                <button type="button" class="cm-btn cm-btn-danger" wire:click="cerrarModalObjetivo" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                            </div>
                        </div>
                    </div>
                @endif

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="pgm-btn-cancel" style="margin-right: 10px;">Cancelar</button>
                    @if($esProfesor && !$esGestionador)
                        <button type="button" wire:click="cerrarFormulario" class="pgm-btn-save">Cerrar formulario</button>
                    @else
                        <button type="submit" class="pgm-btn-save">{{ $modoActualizacion ? 'Subir documentos' : ($editingId ? 'Guardar cambios' : 'Registrar proyecto') }}</button>
                    @endif
                </div>
            </form>
        </fieldset>
    @endif
</div>
