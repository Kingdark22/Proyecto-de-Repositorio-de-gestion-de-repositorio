@extends('layouts.app')
@section('title', 'Registrar proyecto')
@section('header', 'Registrar proyecto')

@push('styles')
<style>
    .acc-item { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; transition:box-shadow .2s; }
    .acc-item:hover { box-shadow:0 2px 8px rgba(0,0,0,.06); }
    .acc-head { display:flex; align-items:center; padding:14px 18px; cursor:pointer; background:#fff; transition:all .25s; gap:12px; user-select:none; }
    .acc-head:hover { background:#fef2f2; }
    .acc-head.open { background:linear-gradient(135deg,#7f1d1d,#991b1b); color:#fff; }
    .acc-head.open .acc-count { background:rgba(255,255,255,.2); color:#fff; }
    .acc-arrow { font-size:11px; color:#9ca3af; transition:transform .3s,opacity .2s; opacity:.6; flex-shrink:0; }
    .acc-arrow.open { transform:rotate(90deg); }
    .acc-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; background:#fef2f2; flex-shrink:0; }
    .acc-body { display:none; }
    .acc-head.open .acc-icon { background:rgba(255,255,255,.15); }
    .acc-info { flex:1; }
    .acc-title { font-weight:700; font-size:14px; line-height:1.2; }
    .acc-count { font-size:11px; background:#f3f4f6; color:#6b7280; padding:3px 10px; border-radius:20px; font-weight:600; transition:all .25s; flex-shrink:0; }
    .acc-body.open { display:block; }
    .acc-toolbar { display:flex; align-items:center; gap:10px; padding:12px 18px; background:#f9fafb; border-top:1px solid #f3f4f6; border-bottom:1px solid #f3f4f6; }
    .acc-toolbar input { height:32px; padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; width:320px; background:#fff; transition:border-color .2s; flex:1; max-width:420px; }
    .acc-toolbar input:focus { border-color:#991b1b; outline:none; box-shadow:0 0 0 3px rgba(153,27,27,.08); }
    .acc-panel { background:#fff; }
    .acc-panel .field-group { margin-bottom:14px; }
    .acc-panel label { display:block; font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; margin-bottom:4px; }
    .acc-panel input[type="text"], .acc-panel input[type="number"], .acc-panel input[type="email"], .acc-panel textarea, .acc-panel select { width:100%; padding:7px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box; background:#fff; transition:border-color .2s; }
    .acc-panel input:focus, .acc-panel textarea:focus, .acc-panel select:focus { border-color:#991b1b; outline:none; box-shadow:0 0 0 3px rgba(153,27,27,.08); }
    .acc-panel select { cursor:pointer; height:34px; }
    .acc-editbar { display:none; align-items:center; gap:8px; padding:8px 18px; font-size:12px; font-weight:700; color:#1e40af; background:#eff6ff; border-bottom:1px solid #bfdbfe; }
    .acc-editbar.show { display:flex; }
    .acc-content { max-height:320px; overflow-y:auto; }
    .acc-content table { width:100%; border-collapse:collapse; font-size:12px; }
    .acc-content th { background:#9ca3af; padding:8px 12px; text-align:left; font-weight:600; color:#fff; position:sticky; top:0; z-index:1; }
    .acc-content td { padding:8px 12px; border-bottom:1px solid #f3f4f6; }
    .acc-content tr:nth-child(even) { background:#f9fafb; }
    .acc-content tr:hover { background:#fef2f2; }
    .acc-empty { text-align:center; padding:24px; color:#9ca3af; font-size:13px; }
    .btn-actions { display:inline-flex; gap:4px; align-items:center; flex-wrap:nowrap; }
    .btn-edit { display:inline-flex; align-items:center; background:#f59e0b; color:#fff; border:none; border-radius:4px; padding:3px 8px; font-size:10px; font-weight:600; text-decoration:none; cursor:pointer; transition:background .2s; white-space:nowrap; }
    .btn-edit:hover { background:#d97706; color:#fff; }
    .btn-delete { display:inline-flex; align-items:center; background:#dc2626; color:#fff; border:none; border-radius:4px; padding:3px 8px; font-size:10px; font-weight:600; cursor:pointer; transition:background .2s; white-space:nowrap; }
    .btn-delete:hover { background:#b91c1c; }
    .acc-toolbar-btns { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
    .tb-btn { display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #d1d5db; border-radius:8px; padding:9px 14px; font-size:12.5px; font-weight:600; color:#374151; cursor:pointer; transition:all .2s; box-shadow:0 1px 2px rgba(0,0,0,.05); }
    .tb-btn:hover { border-color:#991b1b; color:#991b1b; background:#fef2f2; }
    .tb-reg { background:#f0fdf4; border-color:#86efac; color:#166534; }
    .tb-reg:hover { background:#dcfce7; border-color:#22c55e; color:#14532d; }
    .tb-list { background:#eff6ff; border-color:#93c5fd; color:#1e40af; }
    .tb-list:hover { background:#dbeafe; border-color:#3b82f6; color:#1e3a8a; }
    .btn-save { background:#059669; color:#fff; border-color:#047857; border:1px solid transparent; border-radius:6px; padding:8px 20px; font-size:12px; font-weight:600; cursor:pointer; transition:all .2s; }
    .btn-save:hover { background:#047857; }
    .btn-cancel { background:#f3f4f6; color:#374151; border-color:#d1d5db; border:1px solid transparent; border-radius:6px; padding:8px 20px; font-size:12px; font-weight:600; cursor:pointer; transition:all .2s; }
    .btn-cancel:hover { background:#e5e7eb; }
    .field-error { color:#dc2626; font-size:10px; margin-top:3px; }
    .field-ok { color:#16a34a; font-size:10px; margin-top:3px; }
    .field-dup { color:#d97706; font-size:10px; margin-top:3px; display:none; }
    .grupo-banner { display:flex; align-items:center; gap:10px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:12px 16px; margin-bottom:16px; font-size:13px; }
    .grupo-banner b { color:#065f46; }
    .cm-btn { display:inline-flex; align-items:center; justify-content:center; border-radius:6px; padding:0.5rem 0.9rem; font-size:0.9rem; font-weight:600; border:1px solid transparent; cursor:pointer; transition:background-color 0.2s ease, transform 0.2s ease; text-decoration:none; }
    .cm-btn:hover { transform:translateY(-1px); }
    .cm-btn-success { background:#198754; border-color:#166f43; color:#fff; }
    .pgm-btn-save { background-color:#28a745; color:#fff; border:1px solid #218838; border-radius:4px; padding:8px 18px; font-size:13px; font-weight:bold; cursor:pointer; }
    .pgm-btn-cancel { background-color:#dc3545; color:#fff; border:0 none; border-radius:4px; padding:8px 18px; font-size:13px; font-weight:bold; cursor:pointer; text-decoration:none; display:inline-block; }
    .validation-error { color:#dc3545; font-size:11px; }

    /* ===== Acordeones de creación (estilo rojo del sistema) ===== */
    .prj-acc { border:1px solid #e5e7eb; border-radius:12px; margin-bottom:14px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); transition:border-color .25s, box-shadow .25s; }
    .prj-acc:hover { box-shadow:0 3px 10px rgba(0,0,0,.08); }
    .prj-acc-head { display:flex; align-items:center; gap:12px; padding:14px 16px; cursor:pointer; background:#fff; transition:background .25s; user-select:none; }
    .prj-acc-head:hover { background:#fef2f2; }
    .prj-acc-head.open { background:linear-gradient(135deg,#7f1d1d,#991b1b); color:#fff; }
    .prj-acc-num { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; background:#fee2e2; color:#8b0000; flex-shrink:0; transition:all .25s; }
    .prj-acc-head.open .prj-acc-num { background:#fff; color:#8b0000; }
    .prj-acc-title { font-weight:700; font-size:14px; line-height:1.2; }
    .prj-acc-sub { font-size:11px; color:#64748b; margin-top:1px; }
    .prj-acc-head.open .prj-acc-sub { color:#fecaca; }
    .prj-acc-info { flex:1; }
    .prj-acc-chev { font-size:12px; color:#94a3b8; transition:transform .3s; flex-shrink:0; }
    .prj-acc-head.open .prj-acc-chev { color:#fff; transform:rotate(180deg); }
    .prj-acc-body { display:none; border-top:1px solid #e2e8f0; background:#f8fafc; padding:16px 18px; }
    .prj-acc-body.open { display:block; }
    .prj-acc-body label.prj-label { display:block; font-size:11px; font-weight:700; color:#8b0000; text-transform:uppercase; margin-bottom:4px; }
    .prj-acc-body .prj-field { margin-bottom:14px; }
    .prj-acc-body input[type="text"], .prj-acc-body input[type="number"], .prj-acc-body textarea, .prj-acc-body select { width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; box-sizing:border-box; background:#fff; transition:border-color .2s, box-shadow .2s; }
    .prj-acc-body input:focus, .prj-acc-body textarea:focus, .prj-acc-body select:focus { border-color:#991b1b; outline:none; box-shadow:0 0 0 3px rgba(153,27,27,.12); }
    .prj-acc-body select { height:36px; cursor:pointer; }
    .prj-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .prj-check-chip { display:flex; align-items:center; gap:8px; padding:8px 12px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; }
    @media (max-width: 700px){ .prj-grid-2 { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
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
            Un administrador debe cargarlos (o regístrelos con los acordeones de abajo) antes de poder guardar el expediente.
        </div>
    @endif

    {{-- ===== FORMULARIO DE CREACIÓN DE PROYECTO (acordeones teal) ===== --}}
    <form method="POST" action="{{ route('proyectos.crear.store') }}" enctype="multipart/form-data" style="border:2px solid #8b0000;border-radius:10px;padding:18px;background:#FFF;">
        @csrf

        {{-- Campos ocultos derivados del grupo --}}
        <input type="hidden" name="equipo_seccion_clave" value="{{ $datosForm['equipo_seccion_clave'] ?? '' }}">
        <input type="hidden" name="filterLapsoEquipo" value="{{ $datosForm['filterLapsoEquipo'] ?? '' }}">
        <input type="hidden" name="filterProgramaEquipo" value="{{ $datosForm['filterProgramaEquipo'] ?? '' }}">
        <input type="hidden" name="filterSeccionEquipo" value="{{ $datosForm['filterSeccionEquipo'] ?? '' }}">
        <input type="hidden" name="programa_id_derived" value="{{ $datosForm['programa_id_derived'] ?? '' }}">
        <input type="hidden" name="trayecto_derived" value="{{ $datosForm['trayecto_derived'] ?? '' }}">
        <input type="hidden" name="trayecto_derived_codigo" value="{{ $datosForm['trayecto_derived_codigo'] ?? '' }}">

        {{-- Paso 1: Datos del proyecto --}}
        <div class="prj-acc open" id="prjAcc-1">
            <div class="prj-acc-head" onclick="prjToggle(1)">
                <span class="prj-acc-num">1</span>
                <div class="prj-acc-info">
                    <div class="prj-acc-title">Datos del proyecto</div>
                    <div class="prj-acc-sub">Título, resumen y cantidad de beneficiados del proyecto.</div>
                </div>
                <span class="prj-acc-chev">&#9660;</span>
            </div>
            <div class="prj-acc-body open" id="prjBody-1">
                <div class="prj-field">
                    <label class="prj-label">Título del proyecto <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="titulo" value="{{ old('titulo', $datosForm['titulo'] ?? '') }}" placeholder="Título del proyecto...">
                    @error('titulo')<span class="validation-error" style="color:#dc3545;">{{ $message }}</span>@enderror
                </div>
                <div class="prj-field">
                    <label class="prj-label">Resumen <span style="color:#dc2626;">*</span></label>
                    <textarea name="resumen" rows="4" placeholder="Resumen del proyecto (no más de 150 palabras)...">{{ old('resumen', $datosForm['resumen'] ?? '') }}</textarea>
                    @error('resumen')<span class="validation-error" style="color:#dc3545;">{{ $message }}</span>@enderror
                </div>
                <div class="prj-field">
                    <label class="prj-label">Cantidad de beneficiados</label>
                    <input type="number" name="cantidad_beneficiados" value="{{ old('cantidad_beneficiados', $datosForm['cantidad_beneficiados'] ?? '') }}" placeholder="0" style="width:160px;">
                </div>
            </div>
        </div>

        {{-- Paso 2: Clasificación del proyecto --}}
        <div class="prj-acc" id="prjAcc-2">
            <div class="prj-acc-head" onclick="prjToggle(2)">
                <span class="prj-acc-num">2</span>
                <div class="prj-acc-info">
                    <div class="prj-acc-title">Clasificación del proyecto</div>
                    <div class="prj-acc-sub">Comunidad, línea, metodología, tipo y objetivo de investigación.</div>
                </div>
                <span class="prj-acc-chev">&#9660;</span>
            </div>
            <div class="prj-acc-body" id="prjBody-2">
                <div class="prj-grid-2">
                    {{-- Comunidad --}}
                    <div class="prj-field">
                        <label class="prj-label">Comunidad <span style="color:#dc2626;">*</span></label>
                        <select name="comunidad_id" required>
                            <option value="">Seleccione...</option>
                            @foreach(($catalogosForm['comunidades'] ?? collect()) as $c)
                                <option value="{{ $c->id }}" {{ old('comunidad_id', $datosForm['comunidad_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        @error('comunidad_id')<div style="color:#dc3545;font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Línea --}}
                    <div class="prj-field">
                        <label class="prj-label">Línea de Investigación <span style="color:#dc2626;">*</span></label>
                        <select name="linea_investigacion_id" required>
                            <option value="">Seleccione...</option>
                            @foreach(($catalogosForm['lineas'] ?? collect()) as $l)
                                <option value="{{ $l->id }}" {{ old('linea_investigacion_id', $datosForm['linea_investigacion_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->nombre_investigacion }}</option>
                            @endforeach
                        </select>
                        @error('linea_investigacion_id')<div style="color:#dc3545;font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Metodología --}}
                    <div class="prj-field">
                        <label class="prj-label">Metodología <span style="color:#dc2626;">*</span></label>
                        <select name="metodologia_id" required>
                            <option value="">Seleccione...</option>
                            @foreach(($catalogosForm['metodologias'] ?? collect()) as $m)
                                <option value="{{ $m->id }}" {{ old('metodologia_id', $datosForm['metodologia_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                        @error('metodologia_id')<div style="color:#dc3545;font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tipo --}}
                    <div class="prj-field">
                        <label class="prj-label">Tipo de Investigación <span style="color:#dc2626;">*</span></label>
                        <select name="tipo_investigacion_id" required>
                            <option value="">Seleccione...</option>
                            @foreach(($catalogosForm['tipos_investigacion'] ?? collect()) as $ti)
                                <option value="{{ $ti->id }}" {{ old('tipo_investigacion_id', $datosForm['tipo_investigacion_id'] ?? '') == $ti->id ? 'selected' : '' }}>{{ $ti->nombre }}</option>
                            @endforeach
                        </select>
                        @error('tipo_investigacion_id')<div style="color:#dc3545;font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Objetivo --}}
                    <div class="prj-field">
                        <label class="prj-label">Objetivo de Investigación <span style="color:#dc2626;">*</span></label>
                        <select name="objetivo_investigacion_id" required>
                            <option value="">Seleccione...</option>
                            @foreach(($catalogosForm['objetivos_investigacion'] ?? collect()) as $oi)
                                <option value="{{ $oi->id }}" {{ old('objetivo_investigacion_id', $datosForm['objetivo_investigacion_id'] ?? '') == $oi->id ? 'selected' : '' }}>{{ $oi->nombre }}</option>
                            @endforeach
                        </select>
                        @error('objetivo_investigacion_id')<div style="color:#dc3545;font-size:10px;">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:center;margin-top:20px;">
            <a href="{{ route('proyectos.gestion') }}" class="pgm-btn-cancel" style="margin-right:10px;">Cancelar</a>
            <button type="submit" class="pgm-btn-save">Crear proyecto</button>
        </div>
    </form>

    {{-- ===== ACRDEONES DE CATÁLOGOS (patrón clasificación) ===== --}}
    <div style="margin-top:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;">
            <h3 style="margin:0;font-size:16px;font-weight:700;color:#1f2937;">Catálogos de clasificación</h3>
            <span style="font-size:11px;color:#6b7280;">Registre o edite los catálogos usados por el proyecto antes de guardar.</span>
        </div>

        <div id="accWrap" class="flex flex-col gap-3">
            @foreach([
                'comunidades' => ['Comunidades','🏘️'],
                'lineas'      => ['Líneas de Investigación','🔬'],
                'tipos'       => ['Tipos de Investigación','📂'],
                'metodologias'=> ['Metodologías','📊'],
                'objetivos'   => ['Objetivos de Investigación','🎯'],
            ] as $tipo => [$nombre, $icono])
            <div class="acc-item" id="acc-{{ $tipo }}">
                <div class="acc-head" onclick="toggleAcc('{{ $tipo }}')">
                    <div class="acc-icon">{{ $icono }}</div>
                    <div class="acc-info"><div class="acc-title">{{ $nombre }}</div></div>
                    <span class="acc-count" id="cnt-{{ $tipo }}">--</span>
                    <span class="acc-arrow" id="arr-{{ $tipo }}">&#9654;</span>
                </div>
                <div class="acc-body" id="body-{{ $tipo }}">
                    <div class="acc-toolbar">
                        <input type="text" placeholder="Buscar en la lista..." oninput="searchAcc('{{ $tipo }}',this)">
                        @if($puedeGestionar)
                        <button type="button" class="tb-btn tb-reg" onclick="mostrarPanelListado('{{ $tipo }}','form')">＋ Registrar</button>
                        @endif
                        <button type="button" class="tb-btn tb-list" onclick="mostrarPanelListado('{{ $tipo }}','lista')">📋 Ver lista</button>
                    </div>
                    @if($puedeGestionar)
                    <div class="acc-panel" id="form-{{ $tipo }}">
                        <div class="acc-editbar" id="editbar-{{ $tipo }}">✏️ <span id="editbarTitle-{{ $tipo }}">Editando registro</span> <button type="button" onclick="cancelarEdicionInline('{{ $tipo }}')" class="btn-cancel" style="margin-left:auto;padding:3px 12px;font-size:11px;">Cancelar</button></div>
                        <div style="padding:14px 18px 0;" id="formFields-{{ $tipo }}"></div>
                        <div style="padding:0 18px 14px;text-align:right;">
                            <button class="btn-cancel" onclick="limpiarInline('{{ $tipo }}')">Limpiar</button>
                            <button class="btn-save" onclick="guardarInline('{{ $tipo }}')">Guardar</button>
                        </div>
                    </div>
                    @endif
                    <div class="acc-content" id="tbl-{{ $tipo }}" style="display:none;">
                        <div class="acc-empty">Haga clic en «Ver lista» para cargar los datos...</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
var _timers={};
var _dupTimers={};
var _csrf='{{ csrf_token() }}';
var _editandoPorTipo={};
var _titles={comunidades:'Comunidades',lineas:'Líneas de Investigación',tipos:'Tipos de Investigación',metodologias:'Metodologías',objetivos:'Objetivos de Investigación'};
var _fieldLabels={
    nombre:'Nombre',nombre_investigacion:'Nombre de la línea',area_de_investigacion:'Área académica',
    programa_id:'Programa',descripcion:'Descripción',estado_id:'Estado',municipio_id:'Municipio',
    dir_nombre:'Dirección',rif_letra:'Letra del RIF',rif_numero:'Número del RIF',correo:'Correo',
    prefijo_telefono:'Prefijo telefónico',numero_telefono:'Número de teléfono'
};
var _nameFields={comunidades:'nombre',lineas:'nombre_investigacion',tipos:'nombre',metodologias:'nombre',objetivos:'nombre'};

function prjToggle(num){
    var acc=document.getElementById('prjAcc-'+num);
    var head=acc.querySelector('.prj-acc-head');
    var body=document.getElementById('prjBody-'+num);
    var isOpen=head.classList.contains('open');
    document.querySelectorAll('.prj-acc-head').forEach(function(h){h.classList.remove('open')});
    document.querySelectorAll('.prj-acc-body').forEach(function(b){b.classList.remove('open')});
    if(!isOpen){
        head.classList.add('open');
        body.classList.add('open');
    }
}

function toggleAcc(tipo){
    var item=document.getElementById('acc-'+tipo);
    var head=item.querySelector('.acc-head');
    var body=document.getElementById('body-'+tipo);
    var arrow=document.getElementById('arr-'+tipo);
    var isOpen=head.classList.contains('open');

    document.querySelectorAll('.acc-head').forEach(function(h){h.classList.remove('open')});
    document.querySelectorAll('.acc-body').forEach(function(b){b.classList.remove('open')});
    document.querySelectorAll('.acc-arrow').forEach(function(a){a.classList.remove('open')});

    if(!isOpen){
        head.classList.add('open');
        body.classList.add('open');
        arrow.classList.add('open');
        mostrarPanelListado(tipo,'form');
        loadAcc(tipo,document.getElementById('tbl-'+tipo));
    }
}

function mostrarPanelListado(tipo,panel){
    var form=document.getElementById('form-'+tipo);
    var tbl=document.getElementById('tbl-'+tipo);
    var bus=document.querySelector('#acc-'+tipo+' .acc-toolbar input');
    if(panel==='form'){
        if(form)form.style.display='block';
        if(tbl)tbl.style.display='none';
        if(bus)bus.style.display='none';
        var fields=document.getElementById('formFields-'+tipo);
        if(fields&&fields.innerHTML.trim()==='')fields.innerHTML=camposModal(tipo);
        var first=document.querySelector('#formFields-'+tipo+' input[type="text"],#formFields-'+tipo+' select');
        if(first)first.focus();
    }else{
        if(form)form.style.display='none';
        if(bus)bus.style.display='';
        if(tbl){
            tbl.style.display='block';
            if(tbl.innerHTML.indexOf('Haga clic en')>=0){loadAcc(tipo,tbl);}
        }
    }
}

function limpiarInline(tipo){
    var fields=document.getElementById('formFields-'+tipo);
    if(!fields)return;
    fields.querySelectorAll('input[type="text"],input[type="number"],input[type="email"],textarea,select').forEach(function(el){
        if(el.type==='checkbox'){return;}
        el.value='';
    });
    fields.querySelectorAll('input[type="checkbox"]').forEach(function(el){el.checked=false;});
    limpiarErroresModal(fields);
    if(_editandoPorTipo[tipo]){
        _editandoPorTipo[tipo]='';
        var bar=document.getElementById('editbar-'+tipo);
        if(bar)bar.classList.remove('show');
        var sbtn=document.querySelector('#form-'+tipo+' .btn-save');
        if(sbtn)sbtn.textContent='Guardar';
    }
}

function guardarInline(tipo){
    var fields=document.getElementById('formFields-'+tipo);
    if(!fields)return;

    var editandoId=_editandoPorTipo[tipo]||'';

    var requiredMap={
        comunidades:['nombre','estado_id','municipio_id','dir_nombre'],
        lineas:['nombre_investigacion','area_de_investigacion','programa_id','descripcion'],
        tipos:['nombre'],
        metodologias:['nombre'],
        objetivos:['nombre'],
    };
    var req=requiredMap[tipo]||[];
    var clientErrors={};
    req.forEach(function(name){
        var inp=fields.querySelector('[name="'+name+'"]');
        if(!inp)return;
        var val=inp.value.trim();
        var label=_fieldLabels[name]||name.charAt(0).toUpperCase()+name.slice(1).replace(/_/g,' ');
        if(!val){
            clientErrors[name]=[label+' es requerido'];
        }else{
            var minLen=parseInt(inp.getAttribute('minlength'))||0;
            if(minLen&&val.length<minLen){
                clientErrors[name]=[label+' debe tener al menos '+minLen+' caracteres'];
            }
        }
    });
    if(Object.keys(clientErrors).length>0){
        mostrarErroresModal(clientErrors,fields);
        return false;
    }

    var fd=new FormData();
    fd.append('_token',_csrf);
    if(editandoId){fd.append('_method','PUT');}
    fields.querySelectorAll('[name]').forEach(function(inp){
        if(inp.type==='checkbox'){
            fd.append(inp.name,inp.checked?inp.value:'0');
        }else{
            fd.append(inp.name,inp.value);
        }
    });

    var btn=fields.closest('.acc-panel').querySelector('.btn-save');
    btn.disabled=true;btn.textContent=editandoId?'Actualizando...':'Guardando...';

    var url=editandoId
        ? '{{url("clasificacion")}}/'+tipo+'/'+editandoId+'/actualizar'
        : '{{url("clasificacion")}}/'+tipo+'/guardar';

    fetch(url,{
        method:editandoId?'PUT':'POST',
        headers:{'X-CSRF-TOKEN':_csrf,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body:fd
    })
    .then(function(r){
        if(!r.ok&&r.status===422){
            return r.json().then(function(d){throw{validation:true,errors:d.errors||d.message};});
        }
        return r.json();
    })
    .then(function(data){
        btn.disabled=false;btn.textContent=editandoId?'Actualizar':'Guardar';
        if(data.success){
            showNotifyToast('success',data.message||'Guardado correctamente');
            var bar=document.getElementById('editbar-'+tipo);
            if(bar)bar.classList.remove('show');
            _editandoPorTipo[tipo]='';
            document.getElementById('formFields-'+tipo).innerHTML=camposModal(tipo);
            var sbtn=document.querySelector('#form-'+tipo+' .btn-save');
            if(sbtn)sbtn.textContent='Guardar';
            recargarTablasTipo(tipo);
        }else{
            showNotifyToast('error',data.message||'Error al guardar');
        }
    })
    .catch(function(err){
        btn.disabled=false;btn.textContent=editandoId?'Actualizar':'Guardar';
        if(err&&err.validation){
            var raw=err.errors||{};
            var pretty={};
            Object.keys(raw).forEach(function(k){
                var msgs=raw[k];
                var label=_fieldLabels[k]||k.charAt(0).toUpperCase()+k.slice(1).replace(/_/g,' ');
                var text=Array.isArray(msgs)?msgs[0]:msgs;
                text=text.replace(new RegExp('\\b'+k.replace(/_id$/,'')+'\\b','i'),label).replace(/_/g,' ');
                pretty[k]=[text];
            });
            limpiarErroresModal(fields);
            mostrarErroresModal(pretty,fields);
        }else{
            showNotifyToast('error','Error de conexión');
        }
    });
    return false;
}

function loadAcc(tipo,box,q,pg){
    q=q||'';pg=pg||1;
    box.innerHTML='<div class="acc-empty">Cargando...</div>';
    fetch('{{url("clasificacion")}}/'+tipo+'/listar?q='+encodeURIComponent(q)+'&page='+pg,{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.text()})
    .then(function(h){
        box.innerHTML=h;
        var d=document.createElement('div');d.innerHTML=h;
        var n=d.querySelectorAll('tbody tr').length;
        var s=document.getElementById('cnt-'+tipo);
        if(s)s.textContent=n+(n===1?' registro':' registros');
    })
    .catch(function(){box.innerHTML='<div class="acc-empty" style="color:#dc2626;">Error al cargar</div>'});
}

function searchAcc(tipo,input){
    var box=document.getElementById('tbl-'+tipo);if(!box)return;
    clearTimeout(_timers[tipo]);
    _timers[tipo]=setTimeout(function(){loadAcc(tipo,box,input.value)},400);
}

function abrirEditar(tipo,id,nombre){
    var head=document.querySelector('#acc-'+tipo+' .acc-head');
    document.querySelectorAll('.acc-head').forEach(function(h){h.classList.remove('open')});
    document.querySelectorAll('.acc-body').forEach(function(b){b.classList.remove('open')});
    document.querySelectorAll('.acc-arrow').forEach(function(a){a.classList.remove('open')});
    if(head)head.classList.add('open');
    document.getElementById('body-'+tipo).classList.add('open');
    document.getElementById('arr-'+tipo).classList.add('open');
    mostrarPanelListado(tipo,'form');
    loadAcc(tipo,document.getElementById('tbl-'+tipo));

    _editandoPorTipo[tipo]=id;
    var fields=document.getElementById('formFields-'+tipo);
    if(fields)fields.innerHTML=camposModal(tipo);
    limpiarErroresModal(fields);

    var bar=document.getElementById('editbar-'+tipo);
    var barTitle=document.getElementById('editbarTitle-'+tipo);
    if(bar)bar.classList.add('show');
    if(barTitle&&nombre)barTitle.textContent='Editando: '+nombre;

    var saveBtn=document.querySelector('#form-'+tipo+' .btn-save');
    if(saveBtn)saveBtn.textContent='Actualizar';

    fetch('{{url("clasificacion")}}/'+tipo+'/'+id+'/editar',{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json()})
    .then(function(data){
        if(!data.success){showNotifyToast('error',data.message||'No se pudo cargar el registro.');cancelarEdicionInline(tipo);return;}
        var d=data.data;
        Object.keys(d).forEach(function(k){
            var inp=fields?fields.querySelector('[name="'+k+'"]'):null;
            if(!inp)return;
            var val=d[k]===null?'':d[k];
            if(inp.type==='checkbox'){
                inp.checked=(val===1||val==='1'||val===true);
            }else{
                inp.value=val;
            }
        });
        if(tipo==='comunidades'&&d.estado_id){
            var estadoEl=fields.querySelector('[name="estado_id"]');
            if(estadoEl)estadoEl.dispatchEvent(new Event('change'));
            var checker=setInterval(function(){
                var munEl=fields.querySelector('[name="municipio_id"]');
                if(munEl&&munEl.options.length>1&&d.municipio_id){
                    munEl.value=d.municipio_id;clearInterval(checker);
                }
            },100);
            setTimeout(function(){clearInterval(checker);},4000);
        }
    })
    .catch(function(){showNotifyToast('error','Error de conexión al cargar el registro.');cancelarEdicionInline(tipo);});
}

function cancelarEdicionInline(tipo){
    _editandoPorTipo[tipo]='';
    var bar=document.getElementById('editbar-'+tipo);
    if(bar)bar.classList.remove('show');
    var fields=document.getElementById('formFields-'+tipo);
    if(fields)fields.innerHTML=camposModal(tipo);
    var saveBtn=document.querySelector('#form-'+tipo+' .btn-save');
    if(saveBtn)saveBtn.textContent='Guardar';
}

function camposModal(tipo){
    var nameField=_nameFields[tipo]||'nombre';
    var onNameInput=' oninput="checkDuplicate(this,\''+tipo+'\')"';
    function fe(name){return '<div class="field-error" id="err-'+name+'" style="display:none;"></div>';}
    switch(tipo){
        case 'comunidades': return ''
            +'<div class="field-group"><label>Nombre *</label><input type="text" name="nombre" required minlength="3" maxlength="100"'+onNameInput+'><div class="field-dup" id="dup-nombre" style="display:none;"></div>'+fe('nombre')+'</div>'
            +'<div class="field-group"><label>RIF</label><div style="display:flex;gap:6px;"><select name="rif_letra" style="width:auto;flex-shrink:0;"><option value="">—</option><option>V</option><option>J</option><option>G</option><option>C</option><option>P</option></select><input type="text" name="rif_numero" maxlength="9" placeholder="123456789" oninput="this.value=this.value.replace(/[^0-9]/g,\'\')" style="flex:1;">'+fe('rif_numero')+'</div></div>'
            +'<div class="field-group"><label>Correo</label><input type="email" name="correo" maxlength="40">'+fe('correo')+'</div>'
            +'<div class="field-group"><label>Teléfono</label><div style="display:flex;gap:6px;"><select name="prefijo_telefono" style="width:auto;flex-shrink:0;"><option value="">—</option><option>0424</option><option>0414</option><option>0412</option><option>0422</option><option>0416</option><option>0426</option></select><input type="text" name="numero_telefono" maxlength="7" placeholder="1234567" oninput="this.value=this.value.replace(/[^0-9]/g,\'\')" style="flex:1;"></div>'+fe('numero_telefono')+'</div>'
            +'<div class="field-group"><label>Estado *</label><select name="estado_id" required id="modalEstado"><option value="">Seleccione...</option>@foreach($estados ?? [] as $e)<option value="{{ $e->est_codigo }}">{{ $e->est_nombre }}</option>@endforeach</select>'+fe('estado_id')+'</div>'
            +'<div class="field-group"><label>Municipio *</label><select name="municipio_id" required id="modalMunicipio"><option value="">Seleccione estado primero...</option></select>'+fe('municipio_id')+'</div>'
            +'<div class="field-group"><label>Dirección *</label><input type="text" name="dir_nombre" required minlength="3" maxlength="200">'+fe('dir_nombre')+'</div>';
        case 'lineas': return ''
            +'<div class="field-group"><label>Nombre *</label><input type="text" name="nombre_investigacion" required minlength="3" maxlength="100"'+onNameInput+'><div class="field-dup" id="dup-nombre_investigacion" style="display:none;"></div>'+fe('nombre_investigacion')+'</div>'
            +'<div class="field-group"><label>Área Académica *</label><input type="text" name="area_de_investigacion" required minlength="3" maxlength="100">'+fe('area_de_investigacion')+'</div>'
            +'<div class="field-group"><label>Programa *</label><select name="programa_id" required><option value="">Seleccione...</option>@foreach(($programas ?? []) as $p)<option value="{{ $p->pro_codigo }}">{{ $p->pro_siglas }}</option>@endforeach</select>'+fe('programa_id')+'</div>'
            +'<div class="field-group"><label>Descripción *</label><textarea name="descripcion" rows="3" required minlength="10" maxlength="500"></textarea>'+fe('descripcion')+'</div>';
        case 'tipos': return ''
            +'<div class="field-group"><label>Nombre *</label><input type="text" name="nombre" required minlength="3" maxlength="200"'+onNameInput+'><div class="field-dup" id="dup-nombre" style="display:none;"></div>'+fe('nombre')+'</div>'
            +'<div class="field-group"><label>Descripción</label><textarea name="descripcion" rows="3"></textarea>'+fe('descripcion')+'</div>';
        case 'metodologias': return ''
            +'<div class="field-group"><label>Nombre *</label><input type="text" name="nombre" required minlength="3" maxlength="200"'+onNameInput+'><div class="field-dup" id="dup-nombre" style="display:none;"></div>'+fe('nombre')+'</div>'
            +'<div class="field-group"><label>Descripción</label><textarea name="descripcion" rows="3" maxlength="500"></textarea>'+fe('descripcion')+'</div>';
        case 'objetivos': return ''
            +'<div class="field-group"><label>Nombre *</label><input type="text" name="nombre" required minlength="3" maxlength="200"'+onNameInput+'><div class="field-dup" id="dup-nombre" style="display:none;"></div>'+fe('nombre')+'</div>'
            +'<div class="field-group"><label>Descripción</label><textarea name="descripcion" rows="3"></textarea>'+fe('descripcion')+'</div>';
        default: return '<div class="acc-empty">Formulario no disponible</div>';
    }
}

function limpiarErroresModal(scope){
    var c=scope||document.getElementById('modalFields');
    if(!c)return;
    c.querySelectorAll('.field-error').forEach(function(el){el.style.display='none';el.textContent='';});
    c.querySelectorAll('.field-ok').forEach(function(el){el.style.display='none';el.textContent='';});
    c.querySelectorAll('.field-dup').forEach(function(el){el.style.display='none';el.textContent='';});
}

function mostrarErroresModal(errors,scope){
    var c=scope||document.getElementById('modalFields');
    if(!c)return;
    limpiarErroresModal(c);
    var first=null;
    c.querySelectorAll('[name]').forEach(function(inp){
        var name=inp.name;
        var errDiv=inp.parentNode.querySelector('.field-error');
        if(errors[name]){
            var msg=Array.isArray(errors[name])?errors[name][0]:errors[name];
            if(errDiv){errDiv.textContent=msg;errDiv.style.display='block';}
            if(!first)first=inp;
        }else{
            if(errDiv){errDiv.style.display='none';errDiv.textContent='';}
        }
    });
    if(first)first.focus();
}

function checkDuplicate(input,tipo){
    if(_editandoPorTipo[tipo]){return;}
    var val=input.value.trim();
    var minLen=parseInt(input.getAttribute('minlength'))||3;
    var dupDiv=input.parentNode.querySelector('.field-dup');
    if(!val||val.length<minLen){if(dupDiv){dupDiv.style.display='none';dupDiv.textContent='';}return;}
    clearTimeout(_dupTimers[input.name]);
    _dupTimers[input.name]=setTimeout(function(){
        fetch('{{url("clasificacion")}}/'+tipo+'/verificar?nombre='+encodeURIComponent(val),{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.json()})
        .then(function(data){
            if(data.existe){
                if(dupDiv){dupDiv.textContent='Ya existe un registro con este nombre.';dupDiv.style.display='block';}
            }else{
                if(dupDiv){dupDiv.style.display='none';dupDiv.textContent='';}
            }
        })
        .catch(function(){if(dupDiv){dupDiv.style.display='none';}});
    },400);
}

function eliminarRegistro(tipo,id,nombre){
    mostrarModalAccion({
        icon:'🗑️',
        title:'Eliminar registro',
        message:'¿Está seguro de eliminar <strong>'+nombre+'</strong>?',
        hint:'Esta acción no se puede deshacer.',
        detailValue:nombre,
        confirmText:'Sí, eliminar',
        confirmClass:'cm-btn-danger',
        onConfirm:function(){
            fetch('{{url("clasificacion")}}/'+tipo+'/'+id+'/eliminar',{
                method:'DELETE',
                headers:{'X-CSRF-TOKEN':_csrf,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
            })
            .then(function(r){return r.json()})
            .then(function(data){
                if(data.success){
                    showNotifyToast('success',data.message||'Eliminado correctamente');
                    var box=document.getElementById('tbl-'+tipo);
                    if(box)loadAcc(tipo,box);
                }else{
                    showNotifyToast('error',data.message||'No se pudo eliminar');
                }
            })
            .catch(function(){showNotifyToast('error','Error al eliminar')});
        }
    });
}

function recargarTablasTipo(tipo){
    var box=document.getElementById('tbl-'+tipo);
    if(box&&document.querySelector('#acc-'+tipo+' .acc-head.open'))loadAcc(tipo,box);
    fetch('{{url("clasificacion")}}/'+tipo+'/listar?q=&page=1',{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.text()})
    .then(function(h){
        var d=document.createElement('div');d.innerHTML=h;
        var n=d.querySelectorAll('tbody tr').length;
        var s=document.getElementById('cnt-'+tipo);
        if(s)s.textContent=n+(n===1?' registro':' registros');
    })
    .catch(function(){});
}

document.addEventListener('DOMContentLoaded',function(){
    ['comunidades','lineas','tipos','metodologias','objetivos'].forEach(function(t){
        fetch('{{url("clasificacion")}}/'+t+'/listar?q=&page=1',{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.text()})
        .then(function(h){
            var d=document.createElement('div');d.innerHTML=h;
            var n=d.querySelectorAll('tbody tr').length;
            var s=document.getElementById('cnt-'+t);
            if(s)s.textContent=n+(n===1?' registro':' registros');
        })
        .catch(function(){});
    });

    document.addEventListener('change',function(e){
        if(e.target&&e.target.name==='estado_id'){
            var scope=e.target.closest('#modalFields')||e.target.closest('[id^="formFields-"]');
            if(!scope)return;
            var estadoId=e.target.value;
            var munSelect=scope.querySelector('[name="municipio_id"]');
            if(!munSelect)return;
            munSelect.innerHTML='<option value="">Cargando...</option>';
            if(!estadoId){munSelect.innerHTML='<option value="">Seleccione estado primero...</option>';return;}
            fetch('{{url("comunidades/municipios")}}/'+estadoId)
            .then(function(r){return r.json()})
            .then(function(data){
                munSelect.innerHTML='<option value="">Seleccione...</option>';
                data.forEach(function(m){munSelect.innerHTML+='<option value="'+m.mun_codigo+'">'+m.mun_nombre+'</option>';});
            })
            .catch(function(){munSelect.innerHTML='<option value="">Error al cargar</option>';});
        }
    });
});
</script>
@endpush