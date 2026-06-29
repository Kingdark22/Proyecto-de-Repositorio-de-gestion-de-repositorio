@extends('layouts.app')

@section('title', isset($grupo) ? 'Editar Grupo de Proyecto' : 'Registrar Grupo de Proyecto')
@section('header', isset($grupo) ? 'Editar Grupo de Proyecto' : 'Registrar Grupo de Proyecto')

@push('styles')
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
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
    .cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }

    .grp-field { margin-bottom: 8px; }
    .grp-field label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 2px; color: #333; }
    .grp-field select, .grp-field input[type="text"] {
        width: 100%; max-width: 400px;
        height: 30px; padding: 4px 8px;
        border: 1px solid #ccc; border-radius: 4px;
        font-size: 12px; background: #fff;
        box-sizing: border-box;
    }
    .grp-field select:disabled, .grp-field input:disabled { background: #f5f5f5; color: #999; }
    .grp-field input.error { border-color: #dc3545; }

    .grp-search-wrapper { position: relative; }
    .grp-dropdown {
        position: absolute; top: 100%; left: 0; right: 0;
        max-height: 220px; overflow-y: auto;
        background: #fff; border: 1px solid #ccc; border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12); z-index: 1000;
        display: none;
    }
    .grp-dropdown.show { display: block; }
    .grp-dropdown-item {
        padding: 6px 10px; cursor: pointer; font-size: 11px;
        border-bottom: 1px solid #eee;
    }
    .grp-dropdown-item:hover { background: #f0f7f0; }
    .grp-dropdown-empty { padding: 10px; text-align: center; color: #999; font-size: 11px; }

    .grp-member-row {
        display: flex; align-items: center; gap: 6px;
        padding: 6px 8px; border-bottom: 1px solid #eee;
        font-size: 12px;
    }
    .grp-member-row:hover { background: #f9f9f9; }
    .grp-member-rol-badge {
        display: inline-block; padding: 2px 6px; border-radius: 8px;
        font-size: 10px; font-weight: 600;
    }
    .grp-rol-lider { background: #8b0000; color: #fff; }
    .grp-rol-autor { background: #6c757d; color: #fff; }

    .grp-filter-select {
        height: 30px; padding: 3px 6px; font-size: 11px;
        border: 1px solid #ccc; border-radius: 4px; background: #fff;
        box-sizing: border-box;
    }
    .grp-filter-input {
        height: 30px; padding: 4px 8px; font-size: 12px;
        border: 1px solid #ccc; border-radius: 4px; background: #fff;
        box-sizing: border-box;
    }

    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 9999;
        display: none; align-items: center; justify-content: center;
    }
    .modal-overlay.show { display: flex; }
    .modal-content {
        background: #fff; border-radius: 8px; padding: 20px;
        max-width: 650px; width: 92%; max-height: 88vh; overflow-y: auto;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }

    .status-indicator { font-size: 10px; margin-left: 4px; }
    .status-ok { color: #198754; }
    .status-err { color: #dc3545; }
    .is-invalid { border-color: #dc3545 !important; }

    .com-row {
        display: flex; align-items: center; gap: 6px;
        padding: 6px 10px; border-bottom: 1px solid #eee;
        font-size: 11px; cursor: pointer;
    }
    .com-row:hover { background: #e8f0fe; }
    .com-row.selected { background: #cce5ff; font-weight: 600; }
</style>
@endpush

@section('content')
    @if (session('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 12px 18px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('error') }}
        </div>
    @endif

    @if (!$tablaOk)
        <div style="background: #fff3cd; padding: 10px; font-size: 11px; margin-bottom: 12px;">
            Falta la tabla <code>grupo_proyecto_modulo</code> en MySQL repositorio.
            Ejecute: <code>php artisan migrate --path=database/migrations/2026_05_26_100000_create_grupo_proyecto_modulo_table.php</code>
        </div>
    @endif

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 8px;">
            {{ isset($grupo) ? 'Editar grupo: ' . $grupo->nombre : 'Nuevo grupo de proyecto' }}
        </legend>

        <form method="POST" action="{{ isset($grupo) ? route('grupos-proyecto.update', $grupo->grp_codigo) : route('grupos-proyecto.store') }}" id="grupoForm">
            @csrf
            @if (isset($grupo))
                @method('PUT')
            @endif

            {{-- Hidden field for members JSON --}}
            <input type="hidden" name="miembros" id="miembrosInput" value="">

            <table width="100%" style="font-size:11px;">
                <tr>
                    <td width="50%">
                        <b>Nombre del proyecto:</b><br>
                        <input type="text" name="nombre" id="nombreInput"
                            value="{{ old('nombre', $grupo->nombre ?? '') }}"
                            class="grp-filter-input @error('nombre') is-invalid @enderror" style="width:90%;"
                            placeholder="Ej: Grupo A1" required maxlength="120">
                        <span id="nombreStatus" class="status-indicator"></span>
                        @error('nombre')<br><span style="color:#dc3545;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                    <td>
                        <b>Comunidad:</b><br>
                        <div style="display:flex;gap:4px;align-items:center;">
                            @php $comSel = old('comunidad', $grupo->com_codigo ?? ''); @endphp
                            <input type="hidden" name="comunidad" id="comunidadId" value="{{ $comSel }}">
                            <div id="comunidadBadge" style="display:{{ $comSel ? 'flex' : 'none' }};align-items:center;gap:6px;background:#f0f7f0;border:1px solid #b8d4b8;border-radius:4px;padding:4px 10px;font-size:12px;flex:1;">
                                <span style="font-weight:bold;">{{ $comSel ? ($comunidades->firstWhere('com_codigo', (int)$comSel)?->com_nombre ?? '') : '' }}</span>
                                <button type="button" onclick="document.getElementById('comunidadId').value='';document.getElementById('comunidadBadge').style.display='none';document.getElementById('comunidadSearchWrapper').style.display='flex'" style="background:none;border:none;cursor:pointer;color:#991b1b;font-size:14px;padding:0 2px;" title="Cambiar comunidad">✕</button>
                            </div>
                            <div class="grp-search-wrapper" id="comunidadSearchWrapper" style="flex:1;display:{{ $comSel ? 'none' : 'block' }};">
                                <input type="text" id="comunidadSearch" placeholder="Buscar comunidad..."
                                    value="" class="grp-filter-input" style="width:100%;" autocomplete="off">
                                <div class="grp-dropdown" id="comunidadDropdown">
                                    <div class="grp-dropdown-empty">Escriba para buscar comunidades...</div>
                                </div>
                            </div>
                            <button type="button" class="cm-btn cm-btn-primary cm-btn-sm" style="white-space:nowrap;" onclick="abrirModalComunidad()" title="Crear nueva comunidad">+</button>
                        </div>
                        @error('comunidad')<span style="color:#dc3545;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:8px;">
                        <b>Contexto acad&eacute;mico:</b>
                        <div style="display:flex;gap:16px;margin-top:4px;align-items:center;">
                            @if($isProfessor && !isset($grupo) && $lapsoPreseleccionado)
                                @php $lapsoActual = $lapsos->firstWhere('lap_codigo', (int)$lapsoPreseleccionado); @endphp
                                <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;height:32px;background:#f0f7f0;border:1px solid #b8d4b8;border-radius:4px;font-size:12px;font-weight:bold;box-sizing:border-box;">
                                    📅 {{ $lapsoActual->lap_nombre ?? 'Lapso #'.$lapsoPreseleccionado }}
                                </span>
                            @endif
                            <select name="lapso" id="lapsoSelect" class="grp-filter-select" required
                                style="{{ $isProfessor && !isset($grupo) && $lapsoPreseleccionado ? 'display:none;' : '' }}">
                                <option value="">Lapso</option>
                                @foreach ($lapsos as $l)
                                    <option value="{{ $l->lap_codigo }}"
                                        {{ $lapsoPreseleccionado == $l->lap_codigo ? 'selected' : '' }}
                                        {{ isset($grupo) && $grupo->lap_codigo == $l->lap_codigo ? 'selected' : '' }}>
                                        {{ $l->lap_nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="programa" id="programaSelect" class="grp-filter-select">
                                <option value="">PNF</option>
                            </select>
                            <select name="seccion" id="seccionSelect" class="grp-filter-select" required>
                                <option value="">Secci&oacute;n</option>
                            </select>
                        </div>
                        @error('lapso')<span style="color:#dc3545;font-size:10px;">{{ $message }}</span>@enderror
                        @error('seccion')<span style="color:#dc3545;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
            </table>

            <div style="margin-top:6px;background:#f0f7f0;border:1px solid #b8d4b8;border-radius:4px;padding:6px 10px;font-size:12px; display:none;" id="selectedSectionBadge">
                <b>Sección seleccionada:</b> <span id="selectedSectionText"></span>
            </div>
            <p style="font-size:11px;color:#856404;margin-top:4px;" id="selectSectionHint">Seleccione lapso, PNF y secci&oacute;n para buscar estudiantes.</p>

            <div style="margin-top:8px;padding:8px;background:#f5f5f5;border:1px solid #ccc;display:none;" id="studentSection">
                <b>Agregar integrante:</b>
                <span id="studentSectionStatus" style="font-size:10px;color:#666;margin-left:8px;"></span><br>
                <div style="display:flex;gap:16px;align-items:center;margin-top:4px;">
                    <div class="grp-search-wrapper" style="position:relative;flex:1;">
                        <input type="text" id="estudianteSearch"
                            placeholder="🔍 Escriba nombre, apellido o c&eacute;dula para buscar..."
                            class="grp-filter-input" style="width:100%;padding:8px 10px;font-size:12px;height:34px;"
                            autocomplete="off" disabled>
                        <div class="grp-dropdown" id="estudianteDropdown" style="width:100%;">
                            <div class="grp-dropdown-empty">Primero seleccione lapso, PNF y secci&oacute;n.</div>
                        </div>
                    </div>
                    <select id="rolSelect" class="grp-filter-select" style="width:130px;">
                        <option value="1">Autor-L&iacute;der</option>
                        <option value="2">Autor</option>
                    </select>
                    <button type="button" class="cm-btn cm-btn-success cm-btn-sm" id="agregarBtn" disabled
                        onclick="agregarIntegrante()">Agregar</button>
                </div>
            </div>

            <table width="100%" border="1" cellpadding="4"
                style="font-size:11px;margin-top:10px;border-collapse:collapse;">
                <thead>
                    <tr style="background:#ddd;">
                        <th>C&eacute;dula</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="miembrosTableBody">
                    <tr id="miembrosEmptyRow">
                        <td colspan="4" align="center" style="padding:12px;color:#999;">Agregue al menos un l&iacute;der y los autores del grupo.</td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top:14px;text-align:center;">
                <button type="submit" class="cm-btn cm-btn-success" id="guardarBtn" data-confirm-register data-entity-type="Grupo de Proyecto" {{ !$tablaOk ? 'disabled' : '' }}>
                    {{ isset($grupo) ? 'Actualizar Grupo' : 'Registrar Grupo' }}
                </button>
                <a href="{{ route('grupos-proyecto.index') }}" class="cm-btn cm-btn-danger">Cancelar</a>
            </div>
            <p style="font-size:10px;color:#555;margin-top:8px;">Nota: Para registrar el proyecto debe asignar un nombre al grupo, seleccionar la comunidad, el contexto acad&eacute;mico y agregar al menos un Autor-L&iacute;der.</p>
        </form>
    </fieldset>

    {{-- ============================================================
         MODAL CREAR COMUNIDAD
         ============================================================ --}}
    <div class="modal-overlay" id="comunidadModal">
        <div class="modal-content">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #8b0000;">
                <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Nueva Comunidad</h3>
                <button type="button" onclick="cerrarModalComunidad()" style="background:none;border:none;font-size:24px;cursor:pointer;color:#999;">&times;</button>
            </div>
            <div id="comunidadModalError" style="display:none;background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:12px;font-size:12px;font-weight:bold;"></div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="grp-field" style="grid-column:span 2;">
                    <label>Nombre de la comunidad <span style="color:#c82333;">*</span></label>
                    <input type="text" id="comNombre" placeholder="Nombre completo" style="width:100%;max-width:100%;" oninput="validarNombre(this)">
                    <span id="comNombreStatus" class="status-indicator"></span>
                </div>

                <div class="grp-field">
                    <label>RIF</label>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <select id="comRifLetra" style="width:60px;height:36px;border:1px solid #ccc;border-radius:4px;font-size:13px;" onchange="validarRif(document.getElementById('comRifNumero'))">
                            <option value="J">J</option>
                            <option value="V">V</option>
                            <option value="C">C</option>
                            <option value="G">G</option>
                            <option value="P">P</option>
                        </select>
                        <span style="font-size:16px;">-</span>
                        <input type="text" id="comRifNumero" placeholder="123456789" maxlength="9"
                            style="flex:1;height:36px;padding:6px;border:1px solid #ccc;border-radius:4px;font-size:13px;"
                            data-check-url="{{ route('comunidades.check-rif') }}"
                            data-status-span="comRifStatus"
                            data-digito-span="comRifDigito"
                            data-select-id="comRifLetra"
                            oninput="this.value=this.value.replace(/\D/g,''); validarRif(this)">
                        <span style="font-size:16px;">-</span>
                        <span id="comRifDigito" style="font-weight:bold;font-size:16px;min-width:16px;">?</span>
                    </div>
                    <span id="comRifStatus" class="status-indicator"></span>
                </div>

                <div class="grp-field">
                    <label>Correo electr&oacute;nico</label>
                    <input type="email" id="comCorreo" placeholder="comunidad@ejemplo.com"
                        style="width:100%;height:36px;padding:6px;border:1px solid #ccc;border-radius:4px;font-size:13px;" maxlength="40"
                        data-check-url="{{ route('comunidades.check-email') }}"
                        data-status-span="comCorreoStatus"
                        oninput="validarCorreoRemoto(this)">
                    <span id="comCorreoStatus" class="status-indicator"></span>
                </div>

                <div class="grp-field">
                    <label>Tel&eacute;fono</label>
                    <div style="display:flex;gap:6px;">
                        <select id="comTelPrefijo" style="width:80px;height:36px;border:1px solid #ccc;border-radius:4px;font-size:13px;">
                            <option value="0424">0424</option>
                            <option value="0414">0414</option>
                            <option value="0416">0416</option>
                            <option value="0426">0426</option>
                            <option value="0412">0412</option>
                            <option value="0251">0251</option>
                            <option value="0252">0252</option>
                            <option value="0261">0261</option>
                            <option value="0271">0271</option>
                        </select>
                        <input type="text" id="comTelefono" placeholder="5555555" maxlength="7"
                            style="flex:1;height:36px;padding:6px;border:1px solid #ccc;border-radius:4px;font-size:13px;"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,7)">
                    </div>
                </div>

                <div class="grp-field">
                    <label>Estado <span style="color:#c82333;">*</span></label>
                    <select id="comEstado" style="width:100%;height:36px;border:1px solid #ccc;border-radius:4px;font-size:13px;">
                        <option value="">— Seleccione —</option>
                        @foreach ($estados as $e)
                            <option value="{{ $e->est_codigo }}">{{ $e->est_nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grp-field">
                    <label>Municipio <span style="color:#c82333;">*</span></label>
                    <select id="comMunicipio" style="width:100%;height:36px;border:1px solid #ccc;border-radius:4px;font-size:13px;" disabled>
                        <option value="">— Primero seleccione estado —</option>
                    </select>
                </div>

                <div class="grp-field" style="grid-column:span 2;">
                    <label>Direcci&oacute;n exacta <span style="color:#c82333;">*</span></label>
                    <input type="text" id="comDireccion" placeholder="Calle, sector, n&uacute;mero..."
                        style="width:100%;height:36px;padding:6px;border:1px solid #ccc;border-radius:4px;font-size:13px;">
                </div>
            </div>

            <div style="margin-top:16px;display:flex;gap:10px;justify-content:center;">
                <button type="button" class="cm-btn cm-btn-success" onclick="guardarComunidadAjax()">Guardar Comunidad</button>
                <button type="button" class="cm-btn cm-btn-secondary" onclick="cerrarModalComunidad()">Cancelar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// ========== State ==========
let miembros = [];
let estudiantesCache = [];
let comunidadesCache = @json($comunidades->map(fn($c) => ['id' => $c->com_codigo, 'nombre' => $c->com_nombre, 'rif' => $c->com_rif ?? '']));
let isProfessor = {{ $isProfessor ? 'true' : 'false' }};
let editingMode = {{ isset($grupo) ? 'true' : 'false' }};

// ========== DOM refs ==========
const lapsoSelect = document.getElementById('lapsoSelect');
const programaSelect = document.getElementById('programaSelect');
const seccionSelect = document.getElementById('seccionSelect');
const nombreInput = document.getElementById('nombreInput');
const nombreStatus = document.getElementById('nombreStatus');
const estudianteSearch = document.getElementById('estudianteSearch');
const estudianteDropdown = document.getElementById('estudianteDropdown');
const agregarBtn = document.getElementById('agregarBtn');
const miembrosTableBody = document.getElementById('miembrosTableBody');
const miembrosEmptyRow = document.getElementById('miembrosEmptyRow');
const miembrosInput = document.getElementById('miembrosInput');
const comunidadSearch = document.getElementById('comunidadSearch');
const comunidadDropdown = document.getElementById('comunidadDropdown');
const comunidadId = document.getElementById('comunidadId');

// ========== Utility: filter estudiantes excluyendo miembros ya seleccionados ==========
function estudiantesDisponibles() {
    var cedulasSeleccionadas = miembros.map(function(m) { return m.cedula; });
    return estudiantesCache.filter(function(e) {
        return cedulasSeleccionadas.indexOf(e.cedula) === -1;
    });
}

// ========== Lapso → Programas ==========
lapsoSelect.addEventListener('change', function() {
    console.log('lapso change:', this.value);
    const lapso = this.value;
    programaSelect.disabled = !lapso;
    programaSelect.innerHTML = '<option value="">— Seleccione PNF —</option>';
    seccionSelect.disabled = true;
    seccionSelect.innerHTML = '<option value="">— Seleccione secci&oacute;n —</option>';
    estudianteSearch.disabled = true;
    estudianteSearch.value = '';
    estudiantesCache = [];
    if (!editingMode) {
        miembros = [];
        renderMiembros();
    }
    // Re-verificar nombre al cambiar lapso
    if (nombreInput.value.trim().length >= 2) {
        recheckNombreDisponible(nombreInput.value.trim());
    } else {
        nombreStatus.textContent = '';
    }

    if (!lapso) return;

    fetch('/grupos-proyecto/api/programas/' + lapso)
        .then(function(r) {
            console.log('programas fetch status:', r.status);
            return r.json();
        })
        .then(function(data) {
            console.log('programas recibidos:', data.length, JSON.stringify(data.slice(0,3)));
            data.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.pro_codigo;
                opt.textContent = p.pro_siglas;
                programaSelect.appendChild(opt);
            });
            @if (isset($grupo) && $grupo->pro_codigo)
                programaSelect.value = '{{ $grupo->pro_codigo }}';
                programaSelect.dispatchEvent(new Event('change'));
            @else
                // Auto-select when only 1 programa disponible (para todos los roles)
                if (data.length === 1) {
                    programaSelect.value = data[0].pro_codigo;
                    programaSelect.dispatchEvent(new Event('change'));
                }
                if (data.length > 1) {
                    programaSelect.style.borderColor = '#f0b606';
                }
                if (data.length === 0) {
                    programaSelect.style.borderColor = '#dc3545';
                    console.error('programas: datos vacíos del servidor');
                }
            @endif
        })
        .catch(function(err) {
            console.error('Error cargando programas:', err);
            programaSelect.innerHTML = '<option value="">Error de conexión</option>';
            programaSelect.style.borderColor = '#dc3545';
        });
});

// ========== Programa → Secciones ==========
programaSelect.addEventListener('change', function() {
    console.log('programa change:', this.value);
    const lapso = lapsoSelect.value;
    const programa = this.value;
    seccionSelect.disabled = !lapso || !programa;
    seccionSelect.innerHTML = '<option value="">— Seleccione secci&oacute;n —</option>';
    estudianteSearch.disabled = true;
    estudianteSearch.value = '';
    estudiantesCache = [];
    if (!editingMode) {
        miembros = [];
        renderMiembros();
    }

    if (!lapso || !programa) return;

    fetch('/grupos-proyecto/api/secciones/' + lapso + '/' + programa)
        .then(function(r) {
            console.log('secciones fetch status:', r.status);
            return r.json();
        })
        .then(function(data) {
            console.log('secciones recibidos:', data.length, JSON.stringify(data.slice(0,3)));
            data.forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s.sec_codigo;
                opt.textContent = s.sec_nombre + (s.tra_nombre ? ' (' + s.tra_nombre + ')' : '');
                seccionSelect.appendChild(opt);
            });
            @if (isset($grupo))
                seccionSelect.value = '{{ $grupo->sec_codigo }}';
                seccionSelect.dispatchEvent(new Event('change'));
            @endif
            if (data.length === 1 && !editingMode) {
                seccionSelect.value = data[0].sec_codigo;
                seccionSelect.dispatchEvent(new Event('change'));
            }
            if (data.length > 1) {
                seccionSelect.style.borderColor = '#f0b606';
            }
            if (data.length === 0) {
                seccionSelect.style.borderColor = '#dc3545';
                console.error('secciones: datos vacíos del servidor');
            }
        })
        .catch(function(err) {
            console.error('Error cargando secciones:', err);
            seccionSelect.innerHTML = '<option value="">Error de conexión</option>';
            seccionSelect.style.borderColor = '#dc3545';
        });
});

// ========== Sección → Estudiantes ==========
seccionSelect.addEventListener('change', function() {
    const lapso = lapsoSelect.value;
    const seccion = this.value;
    estudianteSearch.disabled = !lapso || !seccion;
    estudianteSearch.value = '';
    estudianteDropdown.classList.remove('show');
    estudiantesCache = [];

    // Show/hide section badge and student section
    var badge = document.getElementById('selectedSectionBadge');
    var badgeText = document.getElementById('selectedSectionText');
    var studentSection = document.getElementById('studentSection');
    var hint = document.getElementById('selectSectionHint');
    if (seccion) {
        badge.style.display = 'block';
        badgeText.textContent = this.options[this.selectedIndex].text;
        studentSection.style.display = 'block';
        hint.style.display = 'none';
    } else {
        badge.style.display = 'none';
        studentSection.style.display = 'none';
        hint.style.display = 'block';
    }

    if (!lapso || !seccion) return;

    var statusEl = document.getElementById('studentSectionStatus');
    statusEl.textContent = 'Cargando estudiantes...';
    estudianteSearch.placeholder = 'Cargando...';

    fetch('/grupos-proyecto/api/estudiantes/' + lapso + '/' + seccion)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            estudiantesCache = data;
            @if (isset($grupo))
                // Pre-load existing members (sin borrar los que ya estén cargados)
                var miembrosData = @json($grupo->miembros ?? []);
                miembrosData.forEach(function(m) {
                    var exists = miembros.some(function(ex) { return ex.cedula === m.cedula; });
                    if (!exists) {
                        miembros.push({
                            cedula: m.cedula,
                            nombre: m.nombre || '',
                            apellido: m.apellido || '',
                            rol_id: parseInt(m.rol_id || 2),
                            rol_name: parseInt(m.rol_id || 2) === 1 ? 'Autor-Líder' : 'Autor'
                        });
                    }
                });
                renderMiembros();
            @endif
            // Show dropdown hint
            if (data.length > 0) {
                var disponibles = estudiantesDisponibles();
                if (disponibles.length > 0) {
                    estudianteSearch.placeholder = disponibles.length + ' estudiantes disponibles. Escriba para buscar...';
                    statusEl.textContent = disponibles.length + ' estudiante(s) disponibles';
                } else {
                    statusEl.textContent = 'Todos los estudiantes ya fueron agregados';
                }
            } else {
                statusEl.textContent = 'No se encontraron estudiantes en esta secci\u00f3n';
                estudianteSearch.placeholder = 'Sin estudiantes disponibles';
            }
        })
        .catch(function() {
            statusEl.textContent = 'Error al cargar estudiantes';
            estudianteSearch.placeholder = 'Error de carga';
            console.error('Error cargando estudiantes');
        });
});

// ========== Student search (excluye miembros ya seleccionados) ==========
estudianteSearch.addEventListener('input', function() {
    var disponibles = estudiantesDisponibles();
    var q = this.value.trim().toLowerCase();
    if (!q) {
        showEstudianteDropdown(disponibles.slice(0, 30));
        return;
    }
    var filtered = disponibles.filter(function(e) {
        return (e.nombre || '').toLowerCase().indexOf(q) !== -1 ||
               (e.apellido || '').toLowerCase().indexOf(q) !== -1 ||
               (e.cedula || '').indexOf(q) !== -1;
    });
    showEstudianteDropdown(filtered);
});

estudianteSearch.addEventListener('focus', function() {
    if (!this.disabled) {
        var disponibles = estudiantesDisponibles();
        showEstudianteDropdown(disponibles.slice(0, 30));
    }
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.grp-search-wrapper')) {
        estudianteDropdown.classList.remove('show');
        comunidadDropdown.classList.remove('show');
    }
});

function showEstudianteDropdown(data) {
    estudianteDropdown.innerHTML = '';
    if (data.length === 0) {
        estudianteDropdown.innerHTML = '<div class="grp-dropdown-empty">No hay estudiantes disponibles.</div>';
    } else {
        data.forEach(function(e) {
            var div = document.createElement('div');
            div.className = 'grp-dropdown-item';
            div.textContent = (e.apellido || '') + ', ' + (e.nombre || '') + ' (' + e.cedula + ')';
            div.onclick = function() { seleccionarEstudiante(e); };
            estudianteDropdown.appendChild(div);
        });
    }
    estudianteDropdown.classList.add('show');
}

function seleccionarEstudiante(est) {
    estudianteSearch.value = (est.apellido || '') + ', ' + (est.nombre || '') + ' (' + est.cedula + ')';
    estudianteDropdown.classList.remove('show');
    agregarBtn.disabled = false;
    agregarBtn.dataset.cedula = est.cedula;
    agregarBtn.dataset.nombre = est.nombre || '';
    agregarBtn.dataset.apellido = est.apellido || '';
}

// ========== Add/Remove members ==========
function agregarIntegrante() {
    var cedula = agregarBtn.dataset.cedula;
    if (!cedula) return;

    var exists = miembros.some(function(m) { return m.cedula === cedula; });
    if (exists) {
        alert('Ese estudiante ya está en el grupo.');
        return;
    }

    var rolId = parseInt(document.getElementById('rolSelect').value);
    miembros.push({
        cedula: cedula,
        nombre: agregarBtn.dataset.nombre || '',
        apellido: agregarBtn.dataset.apellido || '',
        rol_id: rolId,
        rol_name: rolId === 1 ? 'Autor-Líder' : 'Autor'
    });

    renderMiembros();
    estudianteSearch.value = '';
    estudianteSearch.focus();
    agregarBtn.disabled = true;
    agregarBtn.dataset.cedula = '';
}

function quitarIntegrante(cedula) {
    miembros = miembros.filter(function(m) { return m.cedula !== cedula; });
    renderMiembros();
}

function renderMiembros() {
    miembrosTableBody.innerHTML = '';
    miembrosInput.value = JSON.stringify(miembros);

    if (miembros.length === 0) {
        miembrosTableBody.appendChild(miembrosEmptyRow);
        return;
    }

    miembros.forEach(function(m) {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' + escapeHtml(m.cedula) + '</td>' +
            '<td><strong>' + escapeHtml(m.apellido) + ', ' + escapeHtml(m.nombre) + '</strong></td>' +
            '<td><span class="grp-member-rol-badge ' + (m.rol_id === 1 ? 'grp-rol-lider' : 'grp-rol-autor') + '">' + m.rol_name + '</span></td>' +
            '<td><button type="button" onclick="quitarIntegrante(\'' + m.cedula + '\')" ' +
            'style="background:none;border:none;color:#c82333;cursor:pointer;font-size:14px;" title="Quitar">&times;</button></td>';
        miembrosTableBody.appendChild(tr);
    });
}

// ========== Real-time nombre availability (via AJAX) ==========
var nombreTimeout = null;

function recheckNombreDisponible(nombre) {
    @if (isset($grupo))
    var exclude = {{ $grupo->grp_codigo }};
    @else
    var exclude = 0;
    @endif

    var url = '/grupos-proyecto/api/check-nombre/' + encodeURIComponent(nombre);
    if (exclude) { url += '?exclude=' + exclude; }

    return fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.available) {
                nombreStatus.textContent = 'Disponible';
                nombreStatus.className = 'status-indicator status-ok';
                nombreInput.dataset.nombreDisponible = 'true';
            } else {
                nombreStatus.textContent = 'No disponible';
                nombreStatus.className = 'status-indicator status-err';
                nombreInput.dataset.nombreDisponible = 'false';
            }
            return data.available;
        })
        .catch(function() {
            nombreStatus.textContent = '';
            nombreInput.dataset.nombreDisponible = '';
            return false;
        });
}

nombreInput.addEventListener('input', function() {
    var nombre = this.value.trim();

    if (nombre.length < 2) {
        nombreStatus.textContent = '';
        return;
    }

    clearTimeout(nombreTimeout);
    nombreStatus.textContent = 'Verificando...';
    nombreStatus.className = 'status-indicator';
    nombreInput.dataset.nombreDisponible = 'checking';

    nombreTimeout = setTimeout(function() {
        recheckNombreDisponible(nombre);
    }, 400);
});

// ========== Community search ==========
comunidadSearch.addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    var filtered = comunidadesCache.filter(function(c) {
        return (c.nombre || '').toLowerCase().indexOf(q) !== -1 ||
               (c.rif || '').toLowerCase().indexOf(q) !== -1;
    });
    showComunidadDropdown(filtered);
    comunidadId.value = '';
});

comunidadSearch.addEventListener('focus', function() {
    showComunidadDropdown(comunidadesCache);
});

function showComunidadDropdown(data) {
    comunidadDropdown.innerHTML = '';
    if (data.length === 0) {
        comunidadDropdown.innerHTML = '<div class="grp-dropdown-empty">No se encontraron comunidades.</div>';
    } else {
        data.forEach(function(c) {
            var div = document.createElement('div');
            div.className = 'com-row' + (comunidadId.value == c.id ? ' selected' : '');
            div.innerHTML = '<strong>' + escapeHtml(c.nombre) + '</strong>' +
                (c.rif ? ' <span style="color:#666;font-size:10px;">(' + escapeHtml(c.rif) + ')</span>' : '');
            div.onclick = function() {
                comunidadId.value = c.id;
                comunidadSearch.value = '';
                comunidadDropdown.classList.remove('show');
                document.getElementById('comunidadSearchWrapper').style.display = 'none';
                var badge = document.getElementById('comunidadBadge');
                badge.querySelector('span').textContent = c.nombre;
                badge.style.display = 'flex';
            };
            comunidadDropdown.appendChild(div);
        });
    }
    comunidadDropdown.classList.add('show');
}

// ========== Community creation modal ==========
function abrirModalComunidad() {
    document.getElementById('comunidadModal').classList.add('show');
    document.getElementById('comunidadModalError').style.display = 'none';
    document.getElementById('comNombre').value = '';
    document.getElementById('comNombreStatus').textContent = '';
    document.getElementById('comRifNumero').value = '';
    document.getElementById('comRifNumero').dataset.rifOk = '';
    document.getElementById('comRifDigito').textContent = '?';
    document.getElementById('comRifStatus').textContent = '';
    document.getElementById('comRifStatus').style.display = 'none';
    document.getElementById('comCorreo').value = '';
    document.getElementById('comCorreo').dataset.correoOk = '';
    document.getElementById('comCorreoStatus').textContent = '';
    document.getElementById('comCorreoStatus').style.display = 'none';
    document.getElementById('comTelefono').value = '';
    document.getElementById('comEstado').value = '';
    document.getElementById('comMunicipio').disabled = true;
    document.getElementById('comMunicipio').innerHTML = '<option value="">— Primero seleccione estado —</option>';
    document.getElementById('comDireccion').value = '';
}

function cerrarModalComunidad() {
    document.getElementById('comunidadModal').classList.remove('show');
}

document.getElementById('comunidadModal').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalComunidad();
});

// ========== Community modal: RIF & Email validation now handled by global functions validarRif / validarCorreoRemoto (HTML onchange/oninput) ==========

// ========== Community modal: Estado \u2192 Municipio ==========
document.getElementById('comEstado').addEventListener('change', function() {
    var estadoId = this.value;
    var munSelect = document.getElementById('comMunicipio');
    munSelect.disabled = !estadoId;
    munSelect.innerHTML = '<option value="">— Cargando... —</option>';

    if (!estadoId) {
        munSelect.innerHTML = '<option value="">— Primero seleccione estado —</option>';
        return;
    }

    fetch('/comunidades/municipios/' + estadoId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            munSelect.innerHTML = '<option value="">— Seleccione municipio —</option>';
            data.forEach(function(m) {
                var opt = document.createElement('option');
                opt.value = m.mun_codigo;
                opt.textContent = m.mun_nombre;
                munSelect.appendChild(opt);
            });
        })
        .catch(function() {
            munSelect.innerHTML = '<option value="">— Error cargando municipios —</option>';
        });
});

// ========== Community modal: nombre availability (client-side vs cache) ==========
var comNombreTimeout = null;
document.getElementById('comNombre').addEventListener('input', function() {
    clearTimeout(comNombreTimeout);
    var nombre = this.value.trim();
    var statusEl = document.getElementById('comNombreStatus');
    if (nombre.length < 3) {
        statusEl.textContent = '';
        return;
    }
    comNombreTimeout = setTimeout(function() {
        // Verificar contra el caché de comunidades
        var exists = comunidadesCache.some(function(c) {
            return c.nombre.toLowerCase() === nombre.toLowerCase();
        });
        if (exists) {
            statusEl.textContent = 'Nombre no disponible';
            statusEl.className = 'status-indicator status-err';
        } else {
            statusEl.textContent = 'Disponible';
            statusEl.className = 'status-indicator status-ok';
        }
    }, 400);
});

// ========== Save community via AJAX ==========
function guardarComunidadAjax() {
    var errorEl = document.getElementById('comunidadModalError');
    errorEl.style.display = 'none';

    var nombre = document.getElementById('comNombre').value.trim();
    var estado = document.getElementById('comEstado').value;
    var municipio = document.getElementById('comMunicipio').value;
    var direccion = document.getElementById('comDireccion').value.trim();

    if (!nombre) { showModalError('El nombre es obligatorio.'); return; }
    if (!estado) { showModalError('Seleccione un estado.'); return; }
    if (!municipio) { showModalError('Seleccione un municipio.'); return; }
    if (!direccion) { showModalError('La dirección es obligatoria.'); return; }

    var rifNumero = document.getElementById('comRifNumero').value.trim();
    var correo = document.getElementById('comCorreo').value.trim();
    var rifInput = document.getElementById('comRifNumero');
    var correoInput = document.getElementById('comCorreo');

    if (rifNumero.length > 0 && rifInput.dataset.rifOk !== 'true') {
        showModalError('Corrige el RIF antes de guardar.');
        return;
    }
    if (correo.length >= 5 && correoInput.dataset.correoOk !== 'true') {
        showModalError('Corrige el correo antes de guardar.');
        return;
    }

    var data = {
        nombre: nombre,
        rif_letra: document.getElementById('comRifLetra').value,
        rif_numero: rifNumero,
        correo: correo,
        prefijo_telefono: document.getElementById('comTelPrefijo').value,
        numero_telefono: document.getElementById('comTelefono').value,
        estado_id: estado,
        municipio_id: municipio,
        dir_nombre: direccion
    };

    var btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch('{{ route('grupos-proyecto.api.crear-comunidad') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(data)
    })
    .then(function(r) {
        if (!r.ok) { return r.json().then(function(e) { throw new Error(e.error || 'Error al guardar'); }); }
        return r.json();
    })
    .then(function(result) {
        comunidadesCache.push({ id: result.id, nombre: result.nombre, rif: '' });
        comunidadId.value = result.id;
        comunidadSearch.value = result.nombre;
        cerrarModalComunidad();
    })
    .catch(function(err) {
        showModalError(err.message || 'Error al crear la comunidad.');
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = 'Guardar Comunidad';
    });
}

function showModalError(msg) {
    var el = document.getElementById('comunidadModalError');
    el.innerHTML = msg;
    el.style.display = 'block';
}

// ========== Form submit validation ==========
document.getElementById('grupoForm').addEventListener('submit', function(e) {
    miembrosInput.value = JSON.stringify(miembros);

    if (miembros.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un integrante al grupo.');
        return;
    }

    var tieneLider = miembros.some(function(m) { return m.rol_id === 1; });
    if (!tieneLider) {
        e.preventDefault();
        alert('Debe haber al menos un integrante con rol de Autor-Líder.');
        return;
    }

    if (!comunidadId.value) {
        e.preventDefault();
        alert('Debe seleccionar una comunidad.');
        return;
    }

    // Verificar disponibilidad del nombre
    var nombre = nombreInput.value.trim();
    var estadoNombre = nombreInput.dataset.nombreDisponible;

    if (nombre.length >= 2) {
        if (estadoNombre === 'false' || estadoNombre === 'checking') {
            e.preventDefault();
            if (estadoNombre === 'false') {
                alert('El nombre del grupo no está disponible. Cámbielo antes de guardar.');
            } else {
                alert('Verificando disponibilidad del nombre... Espere un momento y vuelva a intentar.');
            }
            return;
        }
        if (estadoNombre !== 'true') {
            e.preventDefault();
            nombreStatus.textContent = 'Verificando...';
            nombreStatus.className = 'status-indicator';
            recheckNombreDisponible(nombre).then(function(available) {
                if (!available) {
                    alert('El nombre del grupo no está disponible. Cámbielo antes de guardar.');
                } else {
                    document.getElementById('grupoForm').submit();
                }
            });
            return;
        }
    }
});

// ========== Utility ==========
function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ========== Init on page load ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded, lapsoPreseleccionado={{ $lapsoPreseleccionado ?? '' }}, editingMode=' + editingMode);
    @if (isset($grupo))
        if (lapsoSelect.value) {
            lapsoSelect.dispatchEvent(new Event('change'));

            @if (!$grupo->pro_codigo)
            setTimeout(function() {
                var lapso = lapsoSelect.value;
                if (!lapso) return;
                fetch('/grupos-proyecto/api/secciones/' + lapso)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        seccionSelect.innerHTML = '<option value="">— Seleccione secci&oacute;n —</option>';
                        data.forEach(function(s) {
                            var opt = document.createElement('option');
                            opt.value = s.sec_codigo;
                            opt.textContent = s.sec_nombre + (s.tra_nombre ? ' (' + s.tra_nombre + ')' : '');
                            seccionSelect.appendChild(opt);
                        });
                        seccionSelect.value = '{{ $grupo->sec_codigo }}';
                        seccionSelect.dispatchEvent(new Event('change'));
                    });
            }, 400);
            @endif
        }
    @elseif ($lapsoPreseleccionado)
        lapsoSelect.value = '{{ $lapsoPreseleccionado }}';
        lapsoSelect.dispatchEvent(new Event('change'));
    @else
        // Sin lapso preseleccionado: si el select ya tiene valor por selected, disparar igual
        if (lapsoSelect.value) {
            console.log('auto-trigger cascade from selected option:', lapsoSelect.value);
            lapsoSelect.dispatchEvent(new Event('change'));
        }
    @endif
});
</script>
@endpush
