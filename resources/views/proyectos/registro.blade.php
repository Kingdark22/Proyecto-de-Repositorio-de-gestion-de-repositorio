@extends('layouts.app')

@section('title', $proyecto->exists ? 'Editar: ' . $proyecto->titulo : 'Nuevo proyecto')
@section('header', $proyecto->exists ? 'Actualizar proyecto' : 'Registrar proyecto')

@push('styles')
<style>
    .cm-btn {
        display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;
        padding: 0.5rem 0.9rem; font-size: 0.9rem; font-weight: 600;
        border: 1px solid transparent; cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
    }
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
    .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
    .pgm-btn-cancel { background-color: #dc3545; color: #fff; border: 0 none; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
    .pgm-btn-save { background-color: #28a745; color: #fff; border: 1px solid #218838; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-weight: bold; cursor: pointer; }
    .validation-error { color: #dc3545; font-size: 11px; }
    .obligatorio { color: red; font-weight: bold; }
    .filter-input, .filter-select { height: 30px; padding: 3px 6px; font-size: 11px; border: 1px solid #ccc; border-radius: 4px; }
</style>
@endpush

@section('content')
    @if (session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold;">
            <ul style="margin:0;padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <a href="{{ route('proyectos.gestion') }}" class="pgm-btn-cancel" style="margin-bottom:15px;display:inline-block;">&laquo; Volver al listado</a>

    @php
        $catalogosVacios = $catalogosForm['catalogosVacios'] ?? [];
    @endphp
    @if (!empty($catalogosVacios))
        <div style="background-color: #fff3cd; color: #856404; padding: 10px; margin: 12px 0; border: 1px solid #ffeeba; border-radius: 4px; font-size: 11px;">
            <b>Catálogos sin datos en repositorio:</b> {{ implode(', ', $catalogosVacios) }}.
            Un administrador debe cargarlos antes de poder guardar el expediente.
        </div>
    @endif

    <form method="POST" action="{{ route('proyectos.gestion.update', $proyecto->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Campos ocultos --}}
        <input type="hidden" name="equipo_seccion_clave" value="{{ $clave }}">
        <input type="hidden" name="filterLapsoEquipo" value="{{ $datosForm['filterLapsoEquipo'] ?? '' }}">
        <input type="hidden" name="filterProgramaEquipo" value="{{ $datosForm['filterProgramaEquipo'] ?? '' }}">
        <input type="hidden" name="filterSeccionEquipo" value="{{ $datosForm['filterSeccionEquipo'] ?? '' }}">
        <input type="hidden" name="programa_id_derived" value="{{ $datosForm['programa_id_derived'] ?? '' }}">
        <input type="hidden" name="trayecto_derived" value="{{ $datosForm['trayecto_derived'] ?? '' }}">
        <input type="hidden" name="comunidad_id" value="{{ $datosForm['comunidad_id'] ?? '' }}">

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $esProfesor ? 'Actualizar expediente (docente)' : ($modoActualizacion ? 'Subir documentos del proyecto' : 'Actualizar expediente') }}
            </legend>

            {{-- == DATOS DEL PROYECTO == --}}
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Datos del proyecto</legend>
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    {{-- TÍTULO: más grande para profesor --}}
                    <tr>
                        <td width="20%"><b>T&iacute;tulo:</b></td>
                        <td colspan="3">
                            <div style="padding:4px 0;font-weight:bold;{{ $esProfesor ? 'font-size:20px;color:#000;' : 'font-size:14px;' }}">
                                {{ $datosForm['titulo'] ?? '(seleccione un equipo para auto-llenar el t&iacute;tulo)' }}
                            </div>
                            <input type="hidden" name="titulo" value="{{ $datosForm['titulo'] ?? '' }}">
                        </td>
                    </tr>
                    <tr>
                        <td width="20%"><b>Comunidad:</b></td>
                        <td colspan="3">
                            @php
                                $comNombre = optional($catalogosForm['comunidades'] ?? collect())->firstWhere('id', $datosForm['comunidad_id'] ?? 0);
                            @endphp
                            @if($comNombre)
                                <span style="background:#f9f2f2;border:1px solid #8b0000;padding:4px 10px;border-radius:4px;font-weight:bold;color:#8b0000;">{{ $comNombre->nombre }}</span>
                            @else
                                <span style="color:#999;">(asignada autom&aacute;ticamente del grupo)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><b>Resumen:</b></td>
                        <td colspan="3">
                            <textarea name="resumen" rows="3" style="width:95%;font-size:12px;">{{ old('resumen', $datosForm['resumen'] ?? '') }}</textarea>
                            @error('resumen')<br><span class="validation-error">{{ $message }}</span>@enderror
                        </td>
                    </tr>

                    {{-- Fechas/Calificación eliminados --}}
                </table>
            </fieldset>

            {{-- == CLASIFICACIÓN == --}}
            @if(!$modoActualizacion)
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Clasificaci&oacute;n del proyecto</legend>
                <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    <tr>
                        <td width="20%"><b>L&iacute;nea de Investigaci&oacute;n:</b></td>
                        <td width="30%">
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="linea_investigacion_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    @foreach(($catalogosForm['lineas'] ?? []) as $l)
                                        <option value="{{ $l->id }}" {{ old('linea_investigacion_id', $datosForm['linea_investigacion_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->nombre_investigacion }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('linea')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nueva línea de investigación">+</button>
                            </div>
                        </td>
                        <td width="20%"><b>Metodolog&iacute;a:</b></td>
                        <td width="30%">
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="metodologia_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    @foreach(($catalogosForm['metodologias'] ?? []) as $m)
                                        <option value="{{ $m->id }}" {{ old('metodologia_id', $datosForm['metodologia_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('metodologia')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nueva metodología">+</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Tipo de Publicaci&oacute;n:</b></td>
                        <td colspan="3">
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="tipo_publicacion_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    @foreach(($catalogosForm['tipos_publicacion'] ?? []) as $tp)
                                        <option value="{{ $tp->id }}" {{ old('tipo_publicacion_id', $datosForm['tipo_publicacion_id'] ?? '') == $tp->id ? 'selected' : '' }}>{{ $tp->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('tipo_publicacion')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nuevo tipo de publicación">+</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Tipo de Investigaci&oacute;n:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="tipo_investigacion_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    @foreach(($catalogosForm['tipos_investigacion'] ?? []) as $ti)
                                        <option value="{{ $ti->id }}" {{ old('tipo_investigacion_id', $datosForm['tipo_investigacion_id'] ?? '') == $ti->id ? 'selected' : '' }}>{{ $ti->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('tipo_investigacion')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nuevo tipo de investigación">+</button>
                            </div>
                        </td>
                        <td><b>Objetivo de Investigaci&oacute;n:</b></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <select name="objetivo_investigacion_id" style="flex:1;font-size:11px;">
                                    <option value="">Seleccione...</option>
                                    @foreach(($catalogosForm['objetivos_investigacion'] ?? []) as $oi)
                                        <option value="{{ $oi->id }}" {{ old('objetivo_investigacion_id', $datosForm['objetivo_investigacion_id'] ?? '') == $oi->id ? 'selected' : '' }}>{{ $oi->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalCatalogo('objetivo_investigacion')" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;padding:4px 8px;font-size:11px;" title="Nuevo objetivo de investigación">+</button>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
            @endif

{{-- == INVOLUCRADOS (solo profesor/admin/coordinador/gestionador) == --}}
            @php
                $puedeGestionarInvolucrados = $esProfesor || $esGestionador;
                $puedeGestionarInvolucrados = $puedeGestionarInvolucrados || !empty($canValidate);
                $proyectoId = $proyecto->id;
            @endphp
            @if($puedeGestionarInvolucrados)
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px; background: #fafafa;">
                <legend style="font-weight: bold; font-size: 12px; color: #8b0000;">Involucrados del proyecto</legend>

                {{-- Involucrados actuales --}}
                <div id="involucrados-list">
                    @if(!empty($involucradosProyecto))
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 10px;">
                        <thead>
                            <tr style="background: #e8e0e0; font-weight: bold;">
                                <th width="12%" style="padding:4px 8px;">C&eacute;dula</th>
                                <th width="23%" style="padding:4px 8px;">Nombre</th>
                                <th width="35%" style="padding:4px 8px;">Roles</th>
                                <th width="30%" style="padding:4px 8px;">Acci&oacute;n</th>
                            </tr>
                        </thead>
                        <tbody id="involucrados-tbody">
                            @foreach($involucradosProyecto as $inv)
                            <tr id="inv-row-{{ $inv['id'] }}" style="border-bottom:1px solid #e0e0e0;">
                                <td style="padding:4px 8px;font-weight:bold;">{{ $inv['cedula'] }}</td>
                                <td style="padding:4px 8px;">{{ $inv['nombre'] }} {{ $inv['apellido'] }}</td>
                                <td style="padding:4px 8px;">
                                    <div class="inv-roles" style="display:flex;flex-wrap:wrap;gap:2px;">
                                        @if(!empty($inv['roles']))
                                            @foreach($inv['roles'] as $rol)
                                                <span style="display:inline-flex;align-items:center;background:#8b0000;color:#fff;padding:1px 8px;border-radius:10px;font-size:9px;margin:1px;">
                                                    {{ $rol['nombre'] }}
                                                    <button type="button" onclick="quitarRol({{ $proyectoId }}, {{ $inv['pivot_id'] }}, {{ $rol['id'] }})" style="background:none;border:none;color:#ffcccc;cursor:pointer;font-size:11px;padding:0 2px;margin-left:3px;line-height:1;" title="Quitar rol">&times;</button>
                                                </span>
                                            @endforeach
                                        @else
                                            <span style="color:#999;">Sin roles</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding:4px 8px;">
                                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                        <button type="button" onclick="abrirRolesModal({{ $proyectoId }}, {{ $inv['id'] }}, '{{ addslashes($inv['nombre']) }} {{ addslashes($inv['apellido']) }}')" style="background:#8b0000;color:#fff;border:none;border-radius:3px;padding:2px 8px;font-size:9px;cursor:pointer;">+ Roles</button>
                                        <button type="button" onclick="quitarInvolucrado({{ $proyectoId }}, {{ $inv['id'] }})" style="background:#dc3545;color:#fff;border:none;border-radius:3px;padding:2px 8px;font-size:9px;cursor:pointer;">Quitar</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div id="inv-empty" style="font-size:11px;color:#999;margin-bottom:10px;">No hay involucrados registrados en este proyecto.</div>
                    @endif
                </div>

                <hr style="border:none;border-top:1px solid #e0e0e0;margin:10px 0;">

                {{-- Formulario rápido: cédula primero (busca en intranet persona -> involucrados -> crear) --}}
                <div style="margin-bottom:8px;">
                    <label style="font-weight:bold;font-size:12px;display:block;margin-bottom:4px;">Agregar involucrado al proyecto:</label>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                        <tr>
                            <td width="18%"><b>C&eacute;dula:</b> <span style="color:red;">*</span></td>
                            <td width="32%">
                                <input type="text" id="inv-cedula" onkeyup="buscarPersonaPorCedula()" style="width:95%;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="V-12345678">
                                <div id="inv-cedula-msg" style="font-size:10px;color:#666;margin-top:2px;"></div>
                            </td>
                            <td width="15%"><b>Nombre:</b></td>
                            <td width="35%">
                                <input type="text" id="inv-nombre" style="width:95%;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="Se auto-completa">
                            </td>
                        </tr>
                        <tr>
                            <td><b>Apellido:</b></td>
                            <td>
                                <input type="text" id="inv-apellido" style="width:95%;padding:5px 6px;border:1px solid #ccc;border-radius:3px;font-size:12px;" placeholder="Se auto-completa">
                            </td>
                            <td colspan="2">
                                <button type="button" onclick="agregarInvolucradoAlProyecto({{ $proyectoId }})" style="background:#198754;color:#fff;border:none;border-radius:4px;padding:6px 16px;font-size:12px;cursor:pointer;font-weight:bold;">+ Agregar</button>
                                <span id="inv-source" style="font-size:10px;color:#999;margin-left:8px;"></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>
            @endif

            {{-- == INTEGRANTES DEL EQUIPO == --}}
            @if(!empty($miembrosGrupo))
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Integrantes del equipo</legend>
                <table width="100%" border="1" cellpadding="4" cellspacing="0" style="font-size: 11px; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#ddd;">
                            <th style="padding:4px 8px;">#</th>
                            <th style="padding:4px 8px;">C&eacute;dula</th>
                            <th style="padding:4px 8px;">Nombre</th>
                            <th style="padding:4px 8px;">Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($miembrosGrupo as $idx => $miembro)
                            <tr style="background: {{ $idx % 2 == 0 ? '#fafafa' : '#fff' }};">
                                <td align="center" style="padding:4px 8px;">{{ $idx + 1 }}</td>
                                <td style="padding:4px 8px;">{{ $miembro['cedula'] }}</td>
                                <td style="padding:4px 8px;">{{ $miembro['nombre'] }} {{ $miembro['apellido'] }}</td>
                                <td style="padding:4px 8px;">
                                    @if(($miembro['rol_id'] ?? 0) == 1)
                                        <span style="color:#8b0000;font-weight:bold;">L&iacute;der</span>
                                    @else
                                        <span style="color:#666;">Autor</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </fieldset>
            @endif

            {{-- == DOCUMENTOS POR COMPONENTE == --}}
            @php
                $componentesDisp = $catalogosForm['componentes_disp'] ?? collect();
                $docsExistentes = $datosForm['documentos'] ?? [];
                $tieneDocumentosSubidos = !empty($docsExistentes);
            @endphp

            {{-- Para profesor: solo muestra documentos si el estudiante ya subió alguno --}}
            {{-- Para otros: siempre muestra la sección --}}
            @if(!$esProfesor || ($esProfesor && $tieneDocumentosSubidos))
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">
                    Documentos del proyecto por componente
                    @if($componentesDisp->isNotEmpty())
                        <span style="font-weight:normal;font-size:10px;color:#666;"> ({{ $componentesDisp->count() }} componente(s))</span>
                    @endif
                </legend>

                @if($componentesDisp->isNotEmpty())
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    @foreach($componentesDisp as $comp)
                        @php
                            $docActual = $docsExistentes[$comp->id] ?? null;
                            $acceptStr = $comp->accept ?? '.pdf,.doc,.docx';
                            $maxMb = $comp->tamano_maximo_mb ?? 10;
                            $acceptTypes = $comp->tipo_archivo ?? 'PDF';
                        @endphp
                        <tr>
                            <td width="25%" valign="middle">
                                <b>{{ $comp->nombre }}</b>
                                @if($comp->es_obligatorio)<span class="obligatorio">*</span>@endif
                                <br><span style="font-size:9px;color:#666;">{{ strtoupper($acceptTypes) }} &middot; M&aacute;x {{ $maxMb }}MB</span>
                            </td>
                            <td width="45%">
                                @if(!$esProfesor)
                                    {{-- File upload para no-profesores (estudiante) --}}
                                    <input type="file" name="documentos[{{ $comp->id }}]" accept="{{ $acceptStr }}" style="width:100%;font-size:11px;">
                                    @error('documentos.' . $comp->id)<br><span class="validation-error">{{ $message }}</span>@enderror
                                @else
                                    {{-- Para profesor: mostrar mensaje según si hay documento o no --}}
                                    @if($docActual)
                                        <span style="font-size:11px;color:#666;">Documento subido por el estudiante</span>
                                    @else
                                        <span style="font-size:11px;color:#999;">Pendiente de carga por el estudiante</span>
                                    @endif
                                @endif
                            </td>
                            <td width="30%">
                                @if($docActual)
                                    <a href="{{ route('documentos.serve', ['path' => $docActual['path']]) }}" target="_blank" style="color:#0000EE;font-size:11px;font-weight:bold;">[VER {{ $comp->nombre }}]</a>
                                @else
                                    <span style="color:#999;font-size:10px;">Sin documento</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
                @else
                    <div style="padding:12px;background:#fff8e1;border:1px solid #ffe082;border-radius:4px;font-size:11px;color:#6d4c00;">
                        <b>⚠ No hay componentes configurados para este programa.</b><br>
                        Un administrador debe ir a <b>Configuración &gt; Componentes</b> y crear los
                        componentes documentales asociados al programa correspondiente.
                    </div>
                @endif
            </fieldset>
            @endif



            {{-- MODAL CREAR CATÁLOGO --}}
            <div id="modal-catalogo" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarModalCatalogo()">
                <div style="background:#fff;border-radius:10px;padding:24px;max-width:480px;width:92%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #8b0000;">
                        <h3 id="modal-catalogo-titulo" style="margin:0;font-size:16px;font-weight:bold;color:#333;"></h3>
                    </div>
                    <input type="hidden" id="modal-catalogo-tipo" value="">
                    <input type="hidden" id="modal-catalogo-ruta" value="">

                    {{-- Nombre --}}
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:bold;font-size:12px;display:block;margin-bottom:4px;">Nombre: <span style="color:red;">*</span></label>
                        <input type="text" id="modal-catalogo-nombre" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:13px;" placeholder="Nombre...">
                    </div>

                    {{-- Descripción (opcional) --}}
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:bold;font-size:12px;display:block;margin-bottom:4px;">Descripci&oacute;n:</label>
                        <textarea id="modal-catalogo-descripcion" rows="2" style="width:100%;padding:7px 8px;border:1px solid #ccc;border-radius:5px;box-sizing:border-box;font-size:12px;"></textarea>
                    </div>

                    {{-- Mención honorífica (solo para tipo publicación) --}}
                    <div id="modal-catalogo-mencion" style="margin-bottom:12px;display:none;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                            <input type="checkbox" id="modal-catalogo-mencion-check" style="width:16px;height:16px;cursor:pointer;">
                            ¿Tiene menci&oacute;n honor&iacute;fica?
                        </label>
                    </div>

                    <div id="modal-catalogo-error" style="color:#dc3545;font-size:11px;margin-bottom:8px;display:none;"></div>

                    <div style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center;">
                        <button type="button" onclick="guardarCatalogo()" class="cm-btn cm-btn-success" style="padding:8px 20px;font-size:13px;">Guardar</button>
                        <button type="button" onclick="cerrarModalCatalogo()" class="cm-btn cm-btn-danger" style="padding:8px 20px;font-size:13px;">Cancelar</button>
                    </div>
                </div>
            </div>

            {{-- MODAL ASIGNAR ROLES --}}
            <div id="modal-roles" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarRolesModal()">
                <div style="background:#fff;border-radius:8px;padding:20px;max-width:450px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
                    <h3 style="margin:0 0 10px;font-size:14px;color:#8b0000;">Asignar roles a: <span id="rol-modal-nombre" style="color:#333;"></span></h3>
                    <input type="hidden" id="rol-modal-proyecto-id" value="">
                    <input type="hidden" id="rol-modal-inv-id" value="">
                    
                    <div style="margin-bottom:8px;">
                        <input type="text" id="buscar-rol" onkeyup="buscarRoles()" style="width:100%;padding:6px 8px;border:1px solid #ccc;border-radius:4px;font-size:11px;box-sizing:border-box;" placeholder="Buscar rol existente...">
                        <div id="resultados-roles" style="margin-top:4px;border:1px solid #e0e0e0;border-radius:4px;max-height:150px;overflow-y:auto;background:#fff;display:none;"></div>
                    </div>

                    <div style="margin-top:4px;">
                        <button type="button" onclick="toggleFormNuevoRol()" style="background:none;border:none;color:#198754;font-size:11px;cursor:pointer;padding:2px 0;">+ Crear nuevo rol</button>
                    </div>

                    <div id="form-nuevo-rol" style="display:none;margin-top:6px;">
                        <div style="display:flex;gap:6px;align-items:center;">
                            <input type="text" id="nuevo-rol-nombre" style="flex:1;padding:4px 6px;border:1px solid #ccc;border-radius:3px;font-size:11px;" placeholder="Nombre del nuevo rol">
                            <button type="button" onclick="crearRol()" style="background:#198754;color:#fff;border:none;border-radius:3px;padding:4px 10px;font-size:10px;cursor:pointer;">Crear</button>
                        </div>
                    </div>

                    <div style="margin-top:12px;text-align:center;">
                        <button type="button" onclick="cerrarRolesModal()" class="cm-btn cm-btn-secondary cm-btn-sm">Cerrar</button>
                    </div>
                </div>
            </div>

            {{-- == BOTONES == --}}
            <div style="text-align:center;margin-top:20px;">
                <a href="{{ route('proyectos.gestion') }}" class="pgm-btn-cancel" style="margin-right:10px;">Cancelar</a>
                <button type="submit" class="pgm-btn-save">{{ $modoActualizacion ? 'Subir documentos' : 'Guardar cambios' }}</button>
                @if (!empty($canValidate) && in_array($proyecto->estado_validacion, ['En proceso', 'completado']))
                    <button type="button" class="cm-btn cm-btn-success cm-btn-sm" style="margin-left:10px;" onclick="if(confirm('¿Aprueba este proyecto?'))window.location='{{ route('proyectos.gestion.approve', $proyecto->id) }}'">Aprobar</button>
                    <button type="button" class="cm-btn cm-btn-warning cm-btn-sm" style="margin-left:5px;" onclick="abrirRechazar({{ $proyecto->id }})">Rechazar</button>
                @endif
            </div>
        </fieldset>
    </form>

    {{-- MODAL RECHAZO --}}
    <div id="rejectModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarRechazar()">
        <div style="background:#fff;border-radius:8px;padding:20px;max-width:520px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
            <h3 style="margin:0 0 15px;font-size:16px;color:#8b0000;">Motivo de rechazo</h3>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <textarea name="motivo" rows="4" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;font-size:12px;" placeholder="Indique la justificación del rechazo..."></textarea>
                <div style="margin-top:15px;text-align:center;display:flex;gap:10px;justify-content:center;">
                    <button type="submit" class="cm-btn cm-btn-danger">Confirmar rechazo</button>
                    <button type="button" class="cm-btn cm-btn-secondary" onclick="cerrarRechazar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function abrirRechazar(id) {
        document.getElementById('rejectForm').action = '{{ route("proyectos.gestion.reject", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
        document.getElementById('rejectModal').style.display = 'flex';
    }
    function cerrarRechazar() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    </script>
@push('scripts')
<script>
// ─── Variables globales ───────────────────────────────────────────
let personaTimer = null;
let rolesTimer = null;

// ─── Buscar persona por cédula (intranet -> involucrados -> nuevo) ─
function buscarPersonaPorCedula() {
    clearTimeout(personaTimer);
    const cedula = document.getElementById('inv-cedula').value.trim();
    const msgDiv = document.getElementById('inv-cedula-msg');
    const nombreInput = document.getElementById('inv-nombre');
    const apellidoInput = document.getElementById('inv-apellido');
    const sourceSpan = document.getElementById('inv-source');

    if (cedula.length < 3) {
        msgDiv.textContent = '';
        return;
    }

    personaTimer = setTimeout(() => {
        msgDiv.textContent = 'Buscando...';
        msgDiv.style.color = '#666';

        fetch('{{ route("proyectos.gestion.involucrados.buscar-persona") }}?cedula=' + encodeURIComponent(cedula))
            .then(r => r.json())
            .then(data => {
                if (!data) {
                    msgDiv.textContent = 'C&eacute;dula muy corta';
                    return;
                }
                if (data.found) {
                    nombreInput.value = data.data.nombre || '';
                    apellidoInput.value = data.data.apellido || '';
                    if (data.source === 'intranet') {
                        msgDiv.innerHTML = '✅ Encontrado en <b>persona</b> (intranet)';
                        msgDiv.style.color = '#198754';
                    } else {
                        msgDiv.innerHTML = '✅ Ya registrado en <b>involucrados</b>';
                        msgDiv.style.color = '#198754';
                    }
                } else {
                    nombreInput.value = '';
                    apellidoInput.value = '';
                    msgDiv.innerHTML = '⚠ No encontrado. Complete los datos para crear uno nuevo.';
                    msgDiv.style.color = '#856404';
                }
            })
            .catch(() => {
                msgDiv.textContent = 'Error al buscar';
                msgDiv.style.color = '#dc3545';
            });
    }, 400);
}

// ─── Agregar involucrado al proyecto (buscaOCrear + agrega) ───────
function agregarInvolucradoAlProyecto(proyectoId) {
    const cedula = document.getElementById('inv-cedula').value.trim();
    const nombre = document.getElementById('inv-nombre').value.trim();
    const apellido = document.getElementById('inv-apellido').value.trim();

    if (!cedula || cedula.length < 3) {
        alert('Ingrese una c&eacute;dula v&aacute;lida');
        return;
    }

    const btn = document.querySelector('button[onclick*="agregarInvolucradoAlProyecto"]');
    btn.disabled = true;
    btn.textContent = 'Agregando...';

    fetch('{{ route("proyectos.gestion.involucrados.crear", "PLACEHOLDER") }}'.replace('PLACEHOLDER', proyectoId), {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
        body: JSON.stringify({ nombre, apellido, cedula, roles: [] })
    }).then(r => r.json()).then(() => {
        location.reload();
    }).catch(() => {
        alert('Error al agregar el involucrado');
        btn.disabled = false;
        btn.textContent = '+ Agregar';
    });
}

// ─── Quitar involucrado ──────────────────────────────────────────
function quitarInvolucrado(proyectoId, invId) {
    if (!confirm('¿Eliminar este involucrado del proyecto?')) return;
    fetch('{{ route("proyectos.gestion.involucrados.quitar", ["PLACEHOLDER_PROY", "PLACEHOLDER_INV"]) }}'.replace('PLACEHOLDER_PROY', proyectoId).replace('PLACEHOLDER_INV', invId), {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
    }).then(r => r.json()).then(() => location.reload());
}

// ─── Modal roles ─────────────────────────────────────────────────
function abrirRolesModal(proyectoId, invId, nombre) {
    document.getElementById('rol-modal-proyecto-id').value = proyectoId;
    document.getElementById('rol-modal-inv-id').value = invId;
    document.getElementById('rol-modal-nombre').textContent = nombre;
    document.getElementById('resultados-roles').style.display = 'none';
    document.getElementById('buscar-rol').value = '';
    document.getElementById('form-nuevo-rol').style.display = 'none';
    document.getElementById('modal-roles').style.display = 'flex';
}

function cerrarRolesModal() {
    document.getElementById('modal-roles').style.display = 'none';
}

function buscarRoles() {
    clearTimeout(rolesTimer);
    const q = document.getElementById('buscar-rol').value.trim();
    const proyectoId = document.getElementById('rol-modal-proyecto-id').value;
    const invId = document.getElementById('rol-modal-inv-id').value;
    const container = document.getElementById('resultados-roles');
    if (q.length < 1) {
        container.style.display = 'none';
        return;
    }
    rolesTimer = setTimeout(() => {
        fetch('{{ route("proyectos.gestion.involucrados.roles", "PLACEHOLDER") }}'.replace('PLACEHOLDER', proyectoId) + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = '<div style="padding:6px 8px;font-size:10px;color:#999;">No se encontraron roles</div>';
                } else {
                    container.innerHTML = data.map(rol =>
                        '<div onclick="asignarRol(' + proyectoId + ',' + invId + ',' + rol.id + ')" style="padding:6px 8px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:11px;" onmouseover="this.style.background=\'#f5f0f0\'" onmouseout="this.style.background=\'\'">' +
                            '<b>' + rol.nombre + '</b>' +
                        '</div>'
                    ).join('');
                }
                container.style.display = 'block';
            });
    }, 300);
}

function asignarRol(proyectoId, invId, rolId) {
    fetch('{{ route("proyectos.gestion.involucrados.roles.asignar", ["PLACEHOLDER_PROY", "PLACEHOLDER_INV"]) }}'.replace('PLACEHOLDER_PROY', proyectoId).replace('PLACEHOLDER_INV', invId), {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
        body: JSON.stringify({ rol_id: rolId })
    }).then(r => r.json()).then(() => location.reload());
}

function quitarRol(proyectoId, pivotId, rolId) {
    if (!confirm('¿Quitar este rol del involucrado?')) return;
    fetch('{{ route("proyectos.gestion.involucrados.roles.quitar", ["PLACEHOLDER_PROY", "PLACEHOLDER_PIVOT", "PLACEHOLDER_ROL"]) }}'.replace('PLACEHOLDER_PROY', proyectoId).replace('PLACEHOLDER_PIVOT', pivotId).replace('PLACEHOLDER_ROL', rolId), {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
    }).then(r => r.json()).then(() => location.reload());
}

// ─── Catálogos (modal genérico) ──────────────────────────────────
const catalogoConfig = {
    linea: {
        titulo: 'Nueva L&iacute;nea de Investigaci&oacute;n',
        ruta: '{{ route("lineas-investigacion.store") }}',
        campoNombre: 'nombre_investigacion',
        mostrarMencion: false
    },
    metodologia: {
        titulo: 'Nueva Metodolog&iacute;a de Investigaci&oacute;n',
        ruta: '{{ route("metodologia-investigacion.store") }}',
        campoNombre: 'nombre',
        mostrarMencion: false
    },
    tipo_publicacion: {
        titulo: 'Nuevo Tipo de Publicaci&oacute;n',
        ruta: '{{ route("tipos-publicacion.store") }}',
        campoNombre: 'nombre',
        mostrarMencion: true
    },
    tipo_investigacion: {
        titulo: 'Nuevo Tipo de Investigaci&oacute;n',
        ruta: '{{ route("tipos-investigacion.store") }}',
        campoNombre: 'nombre',
        mostrarMencion: false
    },
    objetivo_investigacion: {
        titulo: 'Nuevo Objetivo de Investigaci&oacute;n',
        ruta: '{{ route("objetivos-investigacion.store") }}',
        campoNombre: 'nombre',
        mostrarMencion: false
    }
};

function abrirModalCatalogo(tipo) {
    const cfg = catalogoConfig[tipo];
    if (!cfg) return;
    document.getElementById('modal-catalogo-titulo').innerHTML = cfg.titulo;
    document.getElementById('modal-catalogo-tipo').value = tipo;
    document.getElementById('modal-catalogo-ruta').value = cfg.ruta;
    document.getElementById('modal-catalogo-nombre').value = '';
    document.getElementById('modal-catalogo-descripcion').value = '';
    document.getElementById('modal-catalogo-mencion').style.display = cfg.mostrarMencion ? 'block' : 'none';
    document.getElementById('modal-catalogo-mencion-check').checked = false;
    document.getElementById('modal-catalogo-error').style.display = 'none';
    document.getElementById('modal-catalogo').style.display = 'flex';
    document.getElementById('modal-catalogo-nombre').focus();
}

function cerrarModalCatalogo() {
    document.getElementById('modal-catalogo').style.display = 'none';
}

function guardarCatalogo() {
    const tipo = document.getElementById('modal-catalogo-tipo').value;
    const cfg = catalogoConfig[tipo];
    if (!cfg) return;

    const nombre = document.getElementById('modal-catalogo-nombre').value.trim();
    if (!nombre) {
        document.getElementById('modal-catalogo-error').textContent = 'El nombre es obligatorio.';
        document.getElementById('modal-catalogo-error').style.display = 'block';
        return;
    }

    document.getElementById('modal-catalogo-error').style.display = 'none';

    const data = { _token: '{{ csrf_token() }}' };
    data[cfg.campoNombre] = nombre;
    const desc = document.getElementById('modal-catalogo-descripcion').value.trim();
    if (desc && tipo !== 'tipo_publicacion') data.descripcion = desc;
    if (cfg.mostrarMencion) {
        data.mencion_honorifica = document.getElementById('modal-catalogo-mencion-check').checked ? '1' : '0';
    }

    const btn = document.querySelector('#modal-catalogo .cm-btn-success');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch(cfg.ruta, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: new URLSearchParams(data)
    }).then(r => {
        if (r.redirected || r.ok) {
            location.reload();
        } else {
            throw new Error('Error al guardar');
        }
    }).catch(e => {
        document.getElementById('modal-catalogo-error').textContent = 'Error al guardar. Intente de nuevo.';
        document.getElementById('modal-catalogo-error').style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Guardar';
    });
}

function toggleFormNuevoRol() {
    const f = document.getElementById('form-nuevo-rol');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

function crearRol() {
    const nombre = document.getElementById('nuevo-rol-nombre').value.trim();
    if (!nombre) { alert('Escriba un nombre para el rol'); return; }
    fetch('{{ route("proyectos.gestion.involucrados.roles.crear") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
        body: JSON.stringify({ nombre })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const proyectoId = document.getElementById('rol-modal-proyecto-id').value;
            const invId = document.getElementById('rol-modal-inv-id').value;
            asignarRol(proyectoId, invId, data.id);
        }
    });
}
</script>
@endpush
@endsection
