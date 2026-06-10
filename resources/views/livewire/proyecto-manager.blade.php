<div class="pgm-wrap">
    <style>
        .pgm-action-bar {
            margin-bottom: 20px;
        }
        .pgm-btn-registrar {
            background-color: #28a745;
            color: #fff;
            border: 1px solid #218838;
            border-radius: 0;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
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
        <div class="pgm-action-bar" style="display: flex; align-items: center; gap: 12px;">
            @if (($esAdmin ?? false) || ($canRegister ?? false))
                <button type="button" wire:click="iniciarRegistro" class="pgm-btn-registrar">
                    + REGISTRAR NUEVO PROYECTO
                </button>
            @endif
        </div>

        @if (!empty($canValidate))
            <div class="pgm-tabs" style="margin-bottom: 12px; font-size: 11px;">
                <button type="button" wire:click="irAListado('gestion')"
                    style="border: 1px solid #999; border-radius: 4px; padding: 4px 12px; margin-right: 6px; {{ $listTab === 'gestion' ? 'background:#8bb2b7;font-weight:bold;' : 'background:#f0f0f0;' }}">
                    Listado general
                </button>
                <button type="button" wire:click="irAListado('validar')"
                    style="border: 1px solid #999; border-radius: 4px; padding: 4px 12px; {{ $listTab === 'validar' ? 'background:#8bb2b7;font-weight:bold;' : 'background:#f0f0f0;' }}">
                    Validar pendientes
                </button>
            </div>
        @endif

        @if ($listTab === 'validar')
            <div style="margin-bottom: 15px;">
                <b>Búsqueda (título):</b>
                <input wire:model.live.debounce.300ms="search" type="text" style="width: 400px;" placeholder="Título del proyecto...">
            </div>

            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Revisión de
                    expedientes pendientes</legend>
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                            <th width="35%">Título / resumen</th>
                            <th width="20%">Equipo / comunidad</th>
                            <th width="20%">Documentos</th>
                            <th width="25%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach ($proyectos as $p)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};"
                                valign="top">
                                <td style="padding: 5px;">
                                    <span style="font-weight: bold;">{{ $p->titulo }}</span><br>
                                    <span
                                        style="font-size: 10px; color: #555;">{{ Str::limit($p->resumen, 60) }}</span><br>
                                    <span style="font-size: 9px; color: #888;">Registrado:
                                        {{ $p->created_at ? $p->created_at->format('d/m/Y') : '-' }}</span>
                                </td>
                                <td align="center" style="padding: 5px; font-size: 10px;">
                                    {{ $p->equipo_resumen }}<br>
                                    {{ $p->comunidad->nombre ?? 'N/A' }}
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if (count($p->documentos ?? []))
                                        @foreach ($p->documentos as $doc)
                                            <a href="{{ Storage::url(data_get($doc, 'archivo_path')) }}"
                                                target="_blank"
                                                style="color: #0000EE; font-size: 10px; display:block;">[{{ data_get($doc, 'componente.nombre', data_get($doc, 'componente_nombre', 'DOC')) }}]</a>
                                        @endforeach
                                    @elseif($p->archivo_path)
                                        <a href="{{ Storage::url($p->archivo_path) }}" target="_blank"
                                            style="color: #0000EE; font-size: 10px;">[Ver PDF]</a>
                                    @else
                                        <span style="color: #999;">Sin archivos</span>
                                    @endif
                                    <br>
                                    <a href="#" wire:click.prevent="openDetails({{ $p->id }})"
                                        style="color: #0000EE; font-size: 10px;">[Ficha técnica]</a>
                                </td>
                                <td align="center" style="padding: 5px;">
                                    <div class="pgm-actions">
                                        <button type="button" wire:click="approve({{ $p->id }})"
                                            onclick="return confirm('¿Aprueba este proyecto?')"
                                            class="pgm-btn-action pgm-btn-action--approve">
                                            Aprobar
                                        </button>
                                        <button type="button" wire:click="openReject({{ $p->id }})"
                                            class="pgm-btn-action pgm-btn-action--reject">
                                            Rechazar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($proyectos->isEmpty())
                            <tr>
                                <td colspan="4" align="center" style="padding: 20px; font-weight: bold;">No hay
                                    expedientes pendientes de revisión.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div style="margin-top: 10px;">{{ $proyectos->links() }}</div>
            </fieldset>
        @else
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Filtros</legend>
                <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
                    <tr>
                        <td width="33%"><b>Título:</b><br>
                            <input wire:model.live.debounce.300ms="search" type="text" style="width: 95%;" placeholder="Buscar...">
                        </td>
                        <td width="33%"><b>Estado validación:</b><br>
                            <select wire:model.live="filterEstadoList" style="width: 95%;">
                                <option value="">- Todos -</option>
                                <option value="pendiente">Pendiente</option>
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
            </fieldset>

            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de proyectos
                    institucionales</legend>
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                            <th width="25%">Título del proyecto</th>
                            <th width="20%">Comunidad / línea inv.</th>
                            <th width="15%">Validación / C&amp;T</th>
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
                                    @if (count($p->documentos ?? []))
                                        <div style="margin-top: 5px;">
                                            @foreach ($p->documentos as $doc)
                                                <a href="{{ Storage::url(data_get($doc, 'archivo_path')) }}"
                                                    target="_blank"
                                                    style="color: #0000EE; font-size: 10px; display:block;">[{{ data_get($doc, 'componente.nombre', data_get($doc, 'componente_nombre', 'DOC')) }}]</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 5px;">
                                    <span style="font-size: 11px; font-weight: bold; color: #8b0000;">Equipo:
                                        {{ $p->equipo_resumen }}</span><br>
                                    <span style="font-size: 10px;">Comunidad:
                                        {{ $p->comunidad->nombre ?? 'N/A' }}</span><br>
                                    <span style="font-size: 10px;">Línea:
                                        {{ $p->linea_investigacion?->nombre_investigacion ?? '' }}</span>
                                    @if ($p->actualizado_por_estudiante)
                                        <br><span style="background:#ffc107; padding:1px 6px; border-radius:3px; font-size:9px; font-weight:bold; color:#000;">Actualizado por líder</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if ($p->estado_validacion === 'pendiente')
                                        <span style="color: #d4a017; font-weight: bold;">En revisión</span>
                                    @elseif($p->estado_validacion === 'rechazado')
                                        <span style="color: #FF0000; font-weight: bold;"
                                            title="{{ $p->motivo_rechazo }}">Rechazado</span>
                                    @else
                                        <span style="color: #008000; font-weight: bold;">Aprobado</span>
                                    @endif
                                    @if ($p->asignacion_ct)
                                        <br><span
                                            style="background-color: #FFFF00; padding: 2px; border: 1px solid #CCC; font-size: 9px;">Asig.
                                            C&amp;T</span>
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
                                        @if (!empty($canValidate) && $p->estado_validacion === 'pendiente')
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
                                        <button type="button" wire:click="edit({{ $p->id }})"
                                            class="pgm-btn-action pgm-btn-action--edit">
                                            {{ $esLider ? 'Actualizar' : 'Editar' }}
                                        </button>
                                        @if (!($esLider ?? false))
                                        <button type="button" wire:click="toggleStatus({{ $p->id }})"
                                            class="pgm-btn-action pgm-btn-action--toggle">
                                            {{ $p->estado_logico ? 'Inhabilitar' : 'Habilitar' }}
                                        </button>
                                        <button type="button" wire:click="delete({{ $p->id }})"
                                            wire:confirm="¿Eliminar este proyecto permanentemente?"
                                            class="pgm-btn-action pgm-btn-action--delete">
                                            Eliminar
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
                <button type="button" wire:click="irAListado('{{ $listTab }}')" class="pgm-btn-cancel" style="margin-right: 10px;">Cancelar</button>
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
            @if (count($selectedProject->documentos ?? []))
                <div style="margin-top: 10px; font-size: 13px;">
                    <b>Documentos:</b><br>
                    @foreach ($selectedProject->documentos as $doc)
                        <a href="{{ Storage::url(data_get($doc, 'archivo_path')) }}" target="_blank"
                            style="color: #0000EE;">[{{ data_get($doc, 'componente.nombre', data_get($doc, 'componente_nombre', 'Documento')) }}]</a><br>
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
                <button type="button" wire:click="irAListado('{{ $listTab }}')"
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
                {{ $modoActualizacion ? 'Subir documentos del proyecto' : ($editingId ? 'Actualizar expediente' : 'Registrar proyecto') }}
            </legend>
            <form wire:submit="save">

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

                {{-- == SECCIÓN DOCUMENTOS == --}}
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Documentos del proyecto</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        @if (($usaComponentes ?? false) && isset($componentes_requeridos) && count($componentes_requeridos) > 0)
                            @foreach ($componentes_requeridos as $comp)
                                <tr>
                                    <td width="25%" valign="middle"><b>{{ $comp->nombre }}</b>
                                        @if ($comp->es_obligatorio)
                                            <span class="obligatorio">*</span>
                                        @endif
                                    </td>
                                    <td width="45%">
                                        <input type="file" wire:model="archivos_componentes.{{ $comp->id }}"
                                            style="width: 100%;">
                                        @error('archivos_componentes.' . $comp->id)
                                            <br><span class="obligatorio">{{ $message }}</span>
                                        @enderror
                                        <div wire:loading wire:target="archivos_componentes.{{ $comp->id }}"
                                            style="font-size:10px;color:#0000EE;">Cargando archivo...</div>
                                    </td>
                                    <td width="30%">
                                        @if (isset($archivos_actuales[$comp->id]))
                                            <a href="{{ Storage::url($archivos_actuales[$comp->id]) }}"
                                                target="_blank"
                                                style="color:#0000EE; font-size:11px; font-weight:bold;">[VER DOCUMENTO SUBIDO]</a>
                                        @else
                                            <span style="color:#999; font-size:10px;">Sin documento</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        <tr>
                            <td valign="middle"><b>Documento PDF adicional:</b></td>
                            <td>
                                <input type="file" wire:model="archivo_proyecto" accept=".pdf,application/pdf"
                                    style="width: 100%;">
                                @error('archivo_proyecto')
                                    <br><span class="obligatorio">{{ $message }}</span>
                                @enderror
                                <div wire:loading wire:target="archivo_proyecto"
                                    style="font-size:10px;color:#0000EE;">Cargando archivo...</div>
                            </td>
                            <td>
                                @if ($archivo_actual)
                                    <a href="{{ Storage::url($archivo_actual) }}" target="_blank"
                                        style="color:#0000EE; font-size:11px; font-weight:bold;">[VER PDF ACTUAL]</a>
                                @else
                                    <span style="color:#999; font-size:10px;">Sin archivo</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </fieldset>

                @if (!$modoActualizacion)
                {{-- == SECCIÓN EQUIPO Y COMUNIDAD (desplegable) == --}}
                <div style="margin-bottom: 15px; border: 1px solid #CCC; border-radius: 4px;">
                    <button type="button" wire:click="toggleTeamFilters"
                        style="width:100%; background:#f5f5f5; border:none; padding:8px 12px; text-align:left; font-weight:bold; font-size:12px; cursor:pointer;">
                        {{ $showTeamFilters ? '▼ Ocultar selección de equipo' : '▶ Seleccionar equipo / grupo de proyecto' }}
                    </button>
                    @if ($showTeamFilters)
                        <div style="padding:10px;">
                            @if ($esAdmin ?? false)
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
                            @endif

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
                                    @if (!empty($programa_id_derived) || !empty($trayecto_derived))
                                        &nbsp;|&nbsp; Programa: {{ $programa_id_derived ?? '?' }}
                                        , Trayecto: {{ $trayecto_derived ?: '?' }}
                                    @endif
                                    ({{ ($integrantesEquipo ?? collect())->count() }} integrantes)
                                </div>
                            @endif

                            <div style="margin-top: 10px;">
                                <b>Comunidad:</b>
                                @if (($esGrupoRegistrado ?? false) && $comunidadNombreGrupo)
                                    <div style="padding: 6px 0; font-size: 12px;">
                                        <span style="background:#d4edda; border:1px solid #c3e6cb; padding: 4px 10px; border-radius:3px; font-weight:bold;">{{ $comunidadNombreGrupo }}</span>
                                        <small style="color:#777; display:block; margin-top:3px;">Asignada automáticamente por el grupo de proyecto.</small>
                                    </div>
                                @elseif ($equipo_seccion_clave && ($esGrupoRegistrado ?? false))
                                    <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:10px; margin:4px 0; border-radius:4px; font-size:12px; font-weight:bold;">
                                        El grupo de proyecto seleccionado no tiene una comunidad asignada. Debe asignarle una desde la gestión del grupo antes de registrar el proyecto.
                                    </div>
                                    @error('comunidad_id')
                                        <br><span class="obligatorio">{{ $message }}</span>
                                    @enderror
                                @else
                                    <span style="color:#999; font-size:11px;">(se asignará automáticamente al seleccionar un grupo)</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- == SECCIÓN CLASIFICACIÓN (visible) == --}}
                <div style="margin-bottom: 15px; border: 1px solid #CCC; border-radius: 4px; padding: 10px;">
                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>L&iacute;nea de Investigaci&oacute;n:</b></td>
                            <td width="30%">
                                <select wire:model="linea_investigacion_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($lineas ?? [] as $l)
                                        <option value="{{ $l->id }}">{{ $l->nombre_investigacion }}</option>
                                    @endforeach
                                </select>
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

                {{-- == SECCIÓN AVANZADO (colapsable) == --}}
                <div style="margin-bottom: 15px; border: 1px solid #CCC; border-radius: 4px;">
                    <button type="button" wire:click="toggleAdvanced"
                        style="width:100%; background:#f5f5f5; border:none; padding:8px 12px; text-align:left; font-weight:bold; font-size:12px; cursor:pointer;">
                        {{ $showAdvanced ? '▼ Ocultar opciones avanzadas' : '▶ Opciones avanzadas (C&T, nota, fecha aprobación)' }}
                    </button>
                    @if ($showAdvanced)
                        <div style="padding:10px;">
                            <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                                <tr>
                                    <td width="20%"><b>Asignación C&amp;T:</b></td>
                                    <td width="30%"><label><input type="checkbox" wire:model="asignacion_ct"> ¿Aplica?</label></td>
                                    @if ($editingId)
                                        <td width="20%"><b>Nota (1-20):</b></td>
                                        <td width="30%"><input wire:model="calificacion" type="number" min="1" max="20"
                                                style="width: 60px;"> @error('calificacion')
                                                <span class="obligatorio">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    @endif
                                </tr>
                                @if ($editingId)
                                    <tr>
                                        <td><b>Fecha aprobación:</b></td>
                                        <td colspan="3"><input wire:model="fecha_aprobacion" type="date">
                                            @error('fecha_aprobacion')
                                                <span class="obligatorio">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    @endif
                </div>
                @endif

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="pgm-btn-cancel" style="margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="pgm-btn-save">{{ $modoActualizacion ? 'Subir documentos' : ($editingId ? 'Guardar cambios' : 'Registrar proyecto') }}</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
