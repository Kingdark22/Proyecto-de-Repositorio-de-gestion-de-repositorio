@extends('layouts.app')
@section('title', 'Clasificación de Proyectos')
@section('header', 'Clasificación de Proyectos')

@push('styles')
<style>
    .acc-item { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; transition:box-shadow .2s; }
    .acc-item:hover { box-shadow:0 2px 8px rgba(0,0,0,.06); }
    .acc-head { display:flex; align-items:center; padding:14px 18px; cursor:pointer; background:#fff; transition:all .25s; gap:12px; user-select:none; }
    .acc-head:hover { background:#fef2f2; }
    .acc-head.open { background:linear-gradient(135deg,#7f1d1d,#991b1b); color:#fff; }
    .acc-head.open .acc-count { background:rgba(255,255,255,.2); color:#fff; }
    .acc-head.open .acc-arrow { color:#fff; opacity:1; }
    .acc-arrow { font-size:11px; color:#9ca3af; transition:transform .3s,opacity .2s; opacity:.6; flex-shrink:0; }
    .acc-arrow.open { transform:rotate(90deg); }
    .acc-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; background:#fef2f2; flex-shrink:0; }
    .acc-body { display:none; }
    .acc-head.open .acc-icon { background:rgba(255,255,255,.15); }
    .acc-info { flex:1; }
    .acc-title { font-weight:700; font-size:14px; line-height:1.2; }
    .acc-count { font-size:11px; background:#f3f4f6; color:#6b7280; padding:3px 10px; border-radius:20px; font-weight:600; transition:all .25s; flex-shrink:0; }
    .acc-arrow { font-size:11px; color:#9ca3af; transition:transform .3s,opacity .2s; opacity:.6; flex-shrink:0; }
    .acc-arrow.open { transform:rotate(90deg); }
    .acc-body { display:none; }
    .acc-body.open { display:block; }
    .acc-toolbar { display:flex; align-items:center; gap:10px; padding:12px 18px; background:#f9fafb; border-top:1px solid #f3f4f6; border-bottom:1px solid #f3f4f6; }
    .acc-toolbar input { height:32px; padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; width:320px; background:#fff; transition:border-color .2s; flex:1; max-width:420px; }
    .acc-toolbar input:focus { border-color:#991b1b; outline:none; box-shadow:0 0 0 3px rgba(153,27,27,.08); }
    .acc-panel { background:#fff; }
    .acc-panel .field-group { margin-bottom:14px; }
    .acc-panel label { display:block; font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; margin-bottom:4px; }
    .acc-panel input[type="text"], .acc-panel input[type="number"], .acc-panel input[type="email"], .acc-panel textarea, .acc-panel select { width:100%; padding:7px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box; background:#fff; transition:border-color .2s; }
    .acc-panel input:focus, .acc-panel textarea:focus, .acc-panel select:focus { border-color:#991b1b; outline:none; box-shadow:0 0 0 3px rgba(153,27,27,.08); }
    .acc-editbar { display:none; align-items:center; gap:8px; padding:8px 18px; font-size:12px; font-weight:700; color:#1e40af; background:#eff6ff; border-bottom:1px solid #bfdbfe; }
    .acc-editbar.show { display:flex; }
    .acc-panel select { cursor:pointer; }
    .acc-panel select:not([style*="width:auto"]) { height:34px; }
    .acc-panel select[style*="width:auto"] { height:34px; }
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
    .btn-vincular { display:inline-flex; align-items:center; background:#2563eb; color:#fff; border:none; border-radius:4px; padding:3px 8px; font-size:10px; font-weight:600; cursor:pointer; transition:background .2s; white-space:nowrap; text-decoration:none; }
    .btn-vincular:hover { background:#1d4ed8; color:#fff; }

    .acc-toolbar-btns { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
    .tb-btn { display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #d1d5db; border-radius:8px; padding:9px 14px; font-size:12.5px; font-weight:600; color:#374151; cursor:pointer; transition:all .2s; box-shadow:0 1px 2px rgba(0,0,0,.05); }
    .tb-btn:hover { border-color:#991b1b; color:#991b1b; background:#fef2f2; }
    .tb-reg { background:#f0fdf4; border-color:#86efac; color:#166534; }
    .tb-reg:hover { background:#dcfce7; border-color:#22c55e; color:#14532d; }
    .tb-list { background:#eff6ff; border-color:#93c5fd; color:#1e40af; }
    .tb-list:hover { background:#dbeafe; border-color:#3b82f6; color:#1e3a8a; }

    .listados-fil { display:flex; gap:6px; align-items:center; padding:6px 12px; font-weight:700; font-size:12.5px; color:#fff; }
    .listados-cb { width:16px; height:16px; cursor:pointer; accent-color:#991b1b; }
    .listados-row-check { width:30px; text-align:center; }
    .listados-title { flex:1; }
    .listados-count-chip { background:rgba(255,255,255,.25); color:#fff; padding:2px 10px; border-radius:20px; font-size:10.5px; font-weight:600; }

    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
    .modal-overlay.show { display:flex; }
    .modal-box { background:#fff; border-radius:12px; max-width:500px; width:92%; box-shadow:0 20px 60px rgba(0,0,0,.25); overflow:hidden; animation:modalIn .25s ease; }
    @keyframes modalIn { from{opacity:0;transform:translateY(-16px) scale(.97)} to{opacity:1;transform:none} }
    .modal-header { display:flex; align-items:center; gap:10px; padding:18px 24px 14px; border-bottom:2px solid #991b1b; }
    .modal-header h3 { margin:0; font-size:16px; font-weight:700; color:#1f2937; flex:1; }
    .modal-header button { background:none; border:none; font-size:20px; color:#9ca3af; cursor:pointer; line-height:1; }
    .modal-header button:hover { color:#374151; }
    .modal-body { padding:18px 24px; }
    .modal-body label { display:block; font-size:11px; font-weight:600; color:#374151; text-transform:uppercase; margin-bottom:4px; }
    .modal-body input, .modal-body textarea, .modal-body select { width:100%; padding:7px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; box-sizing:border-box; transition:border-color .2s; }
    .modal-body input:focus, .modal-body textarea:focus, .modal-body select:focus { border-color:#991b1b; outline:none; box-shadow:0 0 0 3px rgba(153,27,27,.08); }
    .modal-body .field-group { margin-bottom:12px; }
    .modal-footer { display:flex; gap:8px; justify-content:center; padding:14px 24px 18px; border-top:1px solid #f3f4f6; }
    .modal-footer button { padding:8px 20px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:1px solid transparent; transition:all .2s; }
    .btn-save { background:#059669; color:#fff; border-color:#047857; }
    .btn-save:hover { background:#047857; }
    .btn-cancel { background:#f3f4f6; color:#374151; border-color:#d1d5db; }
    .btn-cancel:hover { background:#e5e7eb; }
    .field-error { color:#dc2626; font-size:10px; margin-top:3px; }
    .field-ok { color:#16a34a; font-size:10px; margin-top:3px; }
    .field-dup { color:#d97706; font-size:10px; margin-top:3px; display:none; }
</style>
@endpush

@section('content')
@php
    $puedeGestionar = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador', 'gestionador');
    $esAdminCoord = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador');
@endphp

<div id="accWrap" class="flex flex-col gap-3">
    @foreach([
        'comunidades' => ['Comunidades','🏘️'],
        'lineas'      => ['Líneas de Investigación','🔬'],
        'tipos'       => ['Tipos de Investigación','📂'],
        'metodologias'=> ['Metodologías','📊'],
        'objetivos'   => ['Objetivos de Investigación','🎯'],
        'componentes' => ['Componentes','🧩'],
    ] as $tipo => [$nombre, $icono])
    <div class="acc-item" id="acc-{{ $tipo }}">
        <div class="acc-head" onclick="toggleAcc('{{ $tipo }}')">
            <div class="acc-icon">{{ $icono }}</div>
            <div class="acc-info">
                <div class="acc-title">{{ $nombre }}</div>
            </div>
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

{{-- Modal Crear --}}
<div id="modalCrear" class="modal-overlay" onclick="if(event.target===this)cerrarModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Nuevo registro</h3>
            <button onclick="cerrarModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="modalForm" onsubmit="return guardarModal(event)">
                @csrf
                <input type="hidden" id="modalTipo" value="">
                <input type="hidden" id="modalId" value="">
                <div id="modalFields"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-save" onclick="guardarModal(event)">Guardar</button>
        </div>
    </div>
</div>

{{-- Modal Vinculacion --}}
<div id="modalVincular" class="modal-overlay" onclick="if(event.target===this)cerrarVinculacion()">
    <div class="modal-box" style="max-width:700px;">
        <div class="modal-header" style="border-bottom-color:#2563eb;">
            <h3>Vincular Componentes a PNF/Trayectos</h3>
            <button onclick="cerrarVinculacion()">&times;</button>
        </div>
        <div class="modal-body" id="vinculacionBody" style="max-height:60vh;overflow-y:auto;">
            <div class="acc-empty">Cargando datos...</div>
        </div>
        <div class="modal-footer" style="border-top:1px solid #f3f4f6;">
            <button class="btn-cancel" onclick="cerrarVinculacion()">Cancelar</button>
            <button class="btn-save" id="btnGuardarVinculacion" onclick="guardarVinculacion()" style="background:#2563eb;border-color:#1d4ed8;">Guardar Vinculación</button>
        </div>
    </div>
</div>

{{-- Modal Listados Completo --}}
<div id="modalListados" class="modal-overlay" onclick="if(event.target===this)cerrarListados()">
    <div class="modal-box" style="max-width:820px;">
        <div class="modal-header" style="border-bottom-color:#2563eb;">
            <h3>Listado de Registros</h3>
            <button onclick="cerrarListados()">&times;</button>
        </div>
        <div class="modal-body" id="listadosBody" style="max-height:60vh;overflow-y:auto;padding:14px 18px;">
            <div class="acc-empty">Cargando datos...</div>
        </div>
        <div class="modal-footer" style="flex-direction:column;align-items:stretch;padding:12px 18px 16px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:11px;color:#6b7280;font-weight:600;" id="listadosSeleccion">0 seleccionado(s)</span>
                <div style="flex:1;"></div>
                <button class="btn-cancel" onclick="toggleTodoListados(true)">Seleccionar todo</button>
                <button class="btn-cancel" onclick="toggleTodoListados(false)">Quitar selección</button>
                @if($esAdminCoord)
                <button class="btn-save" style="background:#dc2626;border-color:#b91c1c;" id="btnEliminarListados" onclick="eliminarListadoMasivo()">Eliminar seleccionados</button>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var _timers={};
var _dupTimers={};
var _csrf='{{ csrf_token() }}';
var _tipoActual='';
var _editandoId='';
var _editandoPorTipo={};
var _titles={comunidades:'Comunidades',lineas:'Líneas de Investigación',tipos:'Tipos de Investigación',metodologias:'Metodologías',objetivos:'Objetivos de Investigación',componentes:'Componentes'};
var _fieldLabels={
    nombre:'Nombre',nombre_investigacion:'Nombre de la línea',area_de_investigacion:'Área académica',
    programa_id:'Programa',descripcion:'Descripción',estado_id:'Estado',municipio_id:'Municipio',
    dir_nombre:'Dirección',tipo_archivo:'Tipo de archivo',tamano_maximo_mb:'Tamaño máximo',
    rif_letra:'Letra del RIF',rif_numero:'Número del RIF',correo:'Correo',
    prefijo_telefono:'Prefijo telefónico',numero_telefono:'Número de teléfono'
};
var _nameFields={
    comunidades:'nombre',lineas:'nombre_investigacion',tipos:'nombre',
    metodologias:'nombre',objetivos:'nombre',componentes:'nombre'
};

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

function registrarDesplegar(tipo){
    var head=document.querySelector('#acc-'+tipo+' .acc-head');
    document.querySelectorAll('.acc-head').forEach(function(h){h.classList.remove('open')});
    document.querySelectorAll('.acc-body').forEach(function(b){b.classList.remove('open')});
    document.querySelectorAll('.acc-arrow').forEach(function(a){a.classList.remove('open')});
    head.classList.add('open');
    document.getElementById('body-'+tipo).classList.add('open');
    document.getElementById('arr-'+tipo).classList.add('open');
    mostrarPanelListado(tipo,'form');
    loadAcc(tipo,document.getElementById('tbl-'+tipo));
    var firstInput=document.querySelector('#formFields-'+tipo+' input[type="text"]');
    if(firstInput)firstInput.focus();
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
        componentes:['nombre','tipo_archivo','tamano_maximo_mb']
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

function abrirModal(tipo){
    _tipoActual=tipo;
    _editandoId='';
    document.getElementById('modalTipo').value=tipo;
    document.getElementById('modalId').value='';
    document.getElementById('modalTitle').textContent='Nuevo — '+_titles[tipo];
    document.getElementById('modalFields').innerHTML=camposModal(tipo);
    limpiarErroresModal();
    document.getElementById('modalCrear').classList.add('show');
    var firstInput=document.querySelector('#modalFields input[type="text"]');
    if(firstInput)firstInput.focus();
}

function cerrarModal(){
    document.getElementById('modalCrear').classList.remove('show');
    _tipoActual='';
    _editandoId='';
    document.getElementById('modalId').value='';
}

function abrirEditar(tipo,id,nombre){
    // Abre el acordeón, muestra el formulario inline y lo rellena con el registro a editar
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
        case 'componentes': return ''
            +'<div class="field-group"><label>Nombre *</label><input type="text" name="nombre" required minlength="3" maxlength="200"'+onNameInput+'><div class="field-dup" id="dup-nombre" style="display:none;"></div>'+fe('nombre')+'</div>'
            +'<div class="field-group"><label>Tipo de Archivo *</label><select name="tipo_archivo" required><option value="">Seleccione...</option><option>PDF</option><option>DOC</option><option>DOCX</option><option>XLS</option><option>XLSX</option><option>IMG</option><option>RAR</option></select>'+fe('tipo_archivo')+'</div>'
            +'<div class="field-group"><label>Tamaño Máximo (MB) *</label><input type="number" name="tamano_maximo_mb" required min="1" max="200" value="10">'+fe('tamano_maximo_mb')+'</div>'
            +'<div class="field-group"><label style="text-transform:none;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="es_obligatorio" value="1" checked style="width:auto;height:auto;margin:0;"> Obligatorio</label></div>';
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
    if(_editandoId){return;}
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

function guardarModal(e){
    e.preventDefault();
    limpiarErroresModal();

    var tipo=document.getElementById('modalTipo').value;
    var editandoId=document.getElementById('modalId').value;
    var form=document.getElementById('modalForm');

    var requiredMap={
        comunidades:['nombre','estado_id','municipio_id','dir_nombre'],
        lineas:['nombre_investigacion','area_de_investigacion','programa_id','descripcion'],
        tipos:['nombre'],
        metodologias:['nombre'],
        objetivos:['nombre'],
        componentes:['nombre','tipo_archivo','tamano_maximo_mb']
    };
    var req=requiredMap[tipo]||[];
    var clientErrors={};
    req.forEach(function(name){
        var inp=form.querySelector('[name="'+name+'"]');
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
        mostrarErroresModal(clientErrors);
        return false;
    }

    var dupVisible=document.querySelector('#modalFields .field-dup[style*="block"]');
    if(dupVisible){showNotifyToast('error','Ya existe un registro con ese nombre.');return false;}

    var fd=new FormData(form);
    fd.append('_token',_csrf);
    if(editandoId){fd.append('_method','PUT');}

    var btn=document.querySelector('#modalCrear .btn-save');
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
            cerrarModal();
            showNotifyToast('success',data.message||'Guardado correctamente');
            var box=document.getElementById('tbl-'+tipo);
            if(box&&document.querySelector('#acc-'+tipo+' .acc-head.open')){
                loadAcc(tipo,box);
            }
            var cnt=document.getElementById('cnt-'+tipo);
            if(cnt&&cnt.textContent!=='--'){
                var n=parseInt(cnt.textContent)||0;
                cnt.textContent=(n+1)+((n+1)===1?' registro':' registros');
            }
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
            mostrarErroresModal(pretty);
        }else{
            showNotifyToast('error','Error de conexión');
        }
    });
    return false;
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

document.addEventListener('DOMContentLoaded',function(){
    ['comunidades','lineas','tipos','metodologias','objetivos','componentes'].forEach(function(t){
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

    // Cascada estado -> municipio (por contenedor: modal o inline)
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

function abrirVinculacion(){
    var body=document.getElementById('vinculacionBody');
    body.innerHTML='<div class="acc-empty">Cargando datos...</div>';
    document.getElementById('modalVincular').classList.add('show');

    fetch('{{route("clasificacion.vinculacion.data")}}',{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json()})
    .then(function(data){
        var html='<fieldset style="border:2px solid #2563eb;border-radius:8px;padding:14px;margin-bottom:16px;background:#f0f7ff;">'
            +'<legend style="color:#1e40af;font-weight:700;font-style:italic;padding:0 8px;font-size:13px;">Seleccionar Componentes</legend>'
            +'<div style="font-size:11px;color:#666;margin-bottom:8px;">Seleccione uno o más componentes para vincular.</div>';

        if(data.componentes.length===0){
            html+='<div class="acc-empty">No hay componentes activos.</div>';
        }else{
            html+='<div style="display:flex;flex-wrap:wrap;gap:6px;">';
            data.componentes.forEach(function(c){
                html+='<label style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;font-size:11px;transition:all .15s;">'
                    +'<input type="checkbox" name="componente_ids[]" value="'+c.id+'" class="vinc-comp-cb" onchange="toggleVincCompLabel(this)" style="width:15px;height:15px;">'
                    +'<b>'+c.nombre+'</b></label>';
            });
            html+='</div>'
                +'<div style="margin-top:8px;font-size:10px;color:#666;"><span id="vincCompCount">0</span> seleccionado(s)'
                +' <button type="button" onclick="vincSeleccionarTodos(true)" style="font-size:10px;background:none;border:1px solid #ccc;border-radius:4px;padding:2px 8px;cursor:pointer;margin-left:6px;">Todos</button>'
                +' <button type="button" onclick="vincSeleccionarTodos(false)" style="font-size:10px;background:none;border:1px solid #ccc;border-radius:4px;padding:2px 8px;cursor:pointer;">Ninguno</button></div>';
        }
        html+='</fieldset>';

        html+='<fieldset style="border:2px solid #2563eb;border-radius:8px;padding:14px;background:#f0f7ff;">'
            +'<legend style="color:#1e40af;font-weight:700;font-style:italic;padding:0 8px;font-size:13px;">Asignar PNF y Trayectos</legend>';

        if(data.pnfRows.length===0){
            html+='<div class="acc-empty">No hay PNF disponibles.</div>';
        }else{
            html+='<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;border-color:#ccc;font-size:11px;">'
                +'<thead><tr style="background:#93c5fd;color:#1e3a5f;font-weight:700;">'
                +'<th width="5%">N°</th><th width="18%">PNF</th><th width="8%">Activo</th><th width="55%">Trayectos</th>'
                +'</tr></thead><tbody>';
            data.pnfRows.forEach(function(row,i){
                html+='<tr style="background:'+(i%2===0?'#fff':'#f0f7ff')+';" valign="top">'
                    +'<td align="center">'+(i+1)+'</td>'
                    +'<td style="font-weight:700;padding:6px 8px;font-size:11px;">'+row.pro_siglas+'</td>'
                    +'<td align="center"><input type="hidden" name="pnf_activo['+row.pro_codigo+']" value="0">'
                    +'<input type="checkbox" name="pnf_activo['+row.pro_codigo+']" value="1" onchange="toggleVincPnfTrayectos(this,'+row.pro_codigo+')" style="width:16px;height:16px;cursor:pointer;"></td>'
                    +'<td style="padding:6px;"><div style="display:flex;flex-wrap:wrap;gap:4px;">';
                var trayectos=row.trayectos||{};
                Object.keys(trayectos).forEach(function(traCodigo){
                    var t=trayectos[traCodigo];
                    html+='<label style="display:flex;align-items:center;gap:3px;background:#fff;border:1px solid #ddd;border-radius:4px;padding:3px 7px;cursor:pointer;font-size:10px;">'
                        +'<input type="checkbox" name="tra_selected['+row.pro_codigo+']['+traCodigo+']" value="1" class="vinc-tra-'+row.pro_codigo+'" onchange="vincActualizarActivoPnf('+row.pro_codigo+')" style="cursor:pointer;">'
                        +'<span>'+t.nombre+'</span></label>';
                });
                html+='</div></td></tr>';
            });
            html+='</tbody></table>';
        }
        html+='</fieldset>';

        body.innerHTML=html;
    })
    .catch(function(){body.innerHTML='<div class="acc-empty" style="color:#dc2626;">Error al cargar datos de vinculación</div>';});
}

function cerrarVinculacion(){
    document.getElementById('modalVincular').classList.remove('show');
}

function toggleVincCompLabel(cb){
    var label=cb.closest('label');
    if(cb.checked){label.style.background='#dbeafe';label.style.borderColor='#2563eb';}
    else{label.style.background='#fff';label.style.borderColor='#d1d5db';}
    var count=document.querySelectorAll('.vinc-comp-cb:checked').length;
    document.getElementById('vincCompCount').textContent=count;
}

function vincSeleccionarTodos(sel){
    document.querySelectorAll('.vinc-comp-cb').forEach(function(cb){
        cb.checked=sel;toggleVincCompLabel(cb);
    });
}

function toggleVincPnfTrayectos(checkbox,proCodigo){
    document.querySelectorAll('.vinc-tra-'+proCodigo).forEach(function(cb){cb.checked=checkbox.checked;});
}

function vincActualizarActivoPnf(proCodigo){
    var anyChecked=false;
    document.querySelectorAll('.vinc-tra-'+proCodigo).forEach(function(cb){if(cb.checked)anyChecked=true;});
    var pnfCb=document.querySelector('input[name="pnf_activo['+proCodigo+']"]');
    if(pnfCb)pnfCb.checked=anyChecked;
}

function guardarVinculacion(){
    var comps=document.querySelectorAll('.vinc-comp-cb:checked');
    if(comps.length===0){showNotifyToast('warning','Seleccione al menos un componente.');return;}
    var pnfs=document.querySelectorAll('input[name^="pnf_activo["]:checked');
    if(pnfs.length===0){showNotifyToast('warning','Seleccione al menos un PNF con trayectos.');return;}

    var fd=new FormData();
    comps.forEach(function(cb){fd.append('componente_ids[]',cb.value);});
    var inputs=document.querySelectorAll('#vinculacionBody input[type="hidden"],#vinculacionBody input[type="checkbox"]');
    inputs.forEach(function(inp){
        if(inp.name)fd.append(inp.name,inp.checked?(inp.type==='checkbox'?'1':inp.value):'0');
    });

    var btn=document.getElementById('btnGuardarVinculacion');
    btn.disabled=true;btn.textContent='Guardando...';

    fetch('{{route("clasificacion.vinculacion.guardar")}}',{
        method:'POST',
        headers:{'X-CSRF-TOKEN':_csrf,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body:fd
    })
    .then(function(r){return r.json()})
    .then(function(data){
        btn.disabled=false;btn.textContent='Guardar Vinculación';
        if(data.success){
            cerrarVinculacion();
            showNotifyToast('success',data.message||'Vinculación guardada');
            var box=document.getElementById('tbl-componentes');
            if(box&&document.querySelector('#acc-componentes .acc-head.open'))loadAcc('componentes',box);
        }else{
            showNotifyToast('error',data.message||'Error al guardar vinculación');
        }
    })
    .catch(function(){
        btn.disabled=false;btn.textContent='Guardar Vinculación';
        showNotifyToast('error','Error de conexión');
    });
}

var _listadosMaxCheck=500;

function abrirListados(){
    var body=document.getElementById('listadosBody');
    body.innerHTML='<div class="acc-empty">Cargando datos...</div>';
    document.getElementById('listadosSeleccion').textContent='0 seleccionado(s)';
    document.getElementById('modalListados').classList.add('show');

    fetch('{{route("clasificacion.listados")}}',{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json()})
    .then(function(d){
        var grupos=[
            ['comunidades','🏘️ Comuni­dades','#7f1d1d'],
            ['lineas','🔬 Líneas de Investigación','#92400e'],
            ['tipos','📂 Tipos de Investigación','#065f46'],
            ['metodologias','📊 Metodologías','#1e40af'],
            ['objetivos','🎯 Objetivos','#6d28d9'],
            ['componentes','🧩 Componentes','#0f766e'],
        ];
        var html='';
        var total=0;
        grupos.forEach(function(g){
            var arr=d[g[0]]||[
            ];
            total+=arr.length;
            html+='<div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:12px;">'
                +'<div class="listados-fil" style="background:linear-gradient(135deg,'+g[2]+','+g[2]+'99);">'
                +'<input type="checkbox" class="listados-cb" onchange="toggleGrupoListado(\''+g[0]+'\',this)" data-grupo="'+g[0]+'">'
                +'<span class="listados-title">'+g[1]+'</span>'
                +'<span class="listados-count-chip">'+arr.length+' registro(s)</span>'
                +'</div>';
            if(arr.length===0){
                html+='<div style="padding:12px;font-size:11.5px;color:#9ca3af;text-align:center;">Sin registros.</div>';
            }else{
                html+='<table style="width:100%;border-collapse:collapse;font-size:12px;">';
                arr.forEach(function(it){
                    var nombre=String(it.nombre||'').replace(/"/g,'&quot;');
                    var detalle=it.detalle?'<br><span style="font-size:10.5px;color:#6b7280;">'+String(it.detalle)+'</span>':'';
                    html+='<tr style="border-bottom:1px solid #f3f4f6;">'
                        +'<td class="listados-row-check"><input type="checkbox" class="listados-cb" data-grupo="'+g[0]+'" data-id="'+it.id+'" data-nombre="'+nombre+'" data-tipo="'+g[0]+'" onchange="actualizarSeleccionListados()"></td>'
                        +'<td style="padding:7px 8px;"><span style="font-weight:600;">'+nombre+'</span>'+detalle+'</td>'
                        +'<td style="text-align:right;padding:7px 8px;white-space:nowrap;">'
                        +'<div class="btn-actions" style="justify-content:flex-end;">'
                        +'<button type="button" class="btn-edit" style="font-size:10px;" onclick="cerrarListados();abrirEditar(\''+g[0]+'\','+it.id+')">Editar</button>'
                        +'@if($esAdminCoord)<button type="button" class="btn-delete" style="font-size:10px;" onclick="eliminarRegistro(\''+g[0]+'\','+it.id+',\''+nombre.replace(/'/g,'\\\'')+'\')">Eliminar</button>@endif'
                        +'</div></td>'
                        +'</tr>';
                });
                html+='</table>';
            }
            html+='</div>';
        });
        if(total===0){
            body.innerHTML='<div class="acc-empty">No hay registros.</div>';
        }else{
            body.innerHTML=html;
        }
        actualizarSeleccionListados();
    })
    .catch(function(){body.innerHTML='<div class="acc-empty" style="color:#dc2626;">Error al cargar los listados</div>';});
}

function cerrarListados(){
    document.getElementById('modalListados').classList.remove('show');
}

function toggleGrupoListado(grupo,cb){
    document.querySelectorAll('#listadosBody .listados-cb[data-grupo="'+grupo+'"]').forEach(function(c){
        c.checked=cb.checked;
    });
    actualizarSeleccionListados();
}

function toggleTodoListados(sel){
    document.querySelectorAll('#listadosBody .listados-cb[data-id]').forEach(function(c){
        c.checked=sel;
    });
    actualizarSeleccionListados();
}

function actualizarSeleccionListados(){
    var n=document.querySelectorAll('#listadosBody .listados-cb[data-id]:checked').length;
    var el=document.getElementById('listadosSeleccion');
    if(el)el.textContent=n+' seleccionado(s)';
    var btn=document.getElementById('btnEliminarListados');
    if(btn)btn.style.opacity=n>0?1:.4;
}

function eliminarListadoMasivo(){
    var checks=document.querySelectorAll('#listadosBody .listados-cb[data-id]:checked');
    if(checks.length===0){showNotifyToast('warning','Seleccione al menos un registro.');return;}

    var lista=[];
    checks.forEach(function(c){lista.push({tipo:c.getAttribute('data-tipo'),id:c.getAttribute('data-id'),nombre:c.getAttribute('data-nombre')});});

    if(lista.length>_listadosMaxCheck){
        showNotifyToast('warning','Máximo '+_listadosMaxCheck+' registros a la vez.');
        return;
    }

    mostrarModalAccion({
        icon:'🗑️',
        title:'Eliminar seleccionados',
        message:'¿Eliminar <strong>'+lista.length+'</strong> registro(s)?',
        hint:'Esta acción no se puede deshacer.',
        confirmText:'Sí, eliminar',
        confirmClass:'cm-btn-danger',
        onConfirm:function(){
            var ok=0,fail=0;
            var p=Promise.resolve();
            lista.forEach(function(it){
                p=p.then(function(){
                    return fetch('{{url("clasificacion")}}/'+it.tipo+'/'+it.id+'/eliminar',{
                        method:'DELETE',
                        headers:{'X-CSRF-TOKEN':_csrf,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
                    })
                    .then(function(r){return r.json()})
                    .then(function(d){
                        if(d.success)ok++;else fail++;
                        recargarTablasTipo(it.tipo);
                    })
                    .catch(function(){fail++;});
                });
            });
            p.then(function(){
                showNotifyToast(ok>0?'success':'error',ok+' eliminado(s) · '+(fail>0?fail+' con error':''));
                abrirListados();
            });
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
</script>
@endpush
