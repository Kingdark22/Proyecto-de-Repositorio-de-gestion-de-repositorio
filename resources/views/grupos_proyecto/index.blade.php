@extends('layouts.app')

@section('title', 'Equipos de Proyecto')
@section('header', 'Equipos de Proyecto')

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
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
    .cm-btn-xs { padding: 0.2rem 0.5rem; font-size: 0.75rem; }

    .grp-filter-select, .grp-filter-input {
        height: 32px;
        padding: 4px 8px;
        font-size: 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #fff;
        box-sizing: border-box;
    }
    .grp-filter-select { min-width: 140px; }
    .grp-filter-input { width: 160px; }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        max-width: 600px;
        width: 90%;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
</style>
@endpush

@section('content')
    <h2 class="titulo" style="margin-bottom: 10px; font-weight: bolder;">Equipos de proyecto</h2>

    <p style="font-size: 11px; color: #444; margin-bottom: 12px;">
        Registre el <strong>grupo de proyecto</strong> eligiendo estudiantes de la <strong>sección del PNF</strong>.
        Queda identificado con un código único auto-generado.
    </p>

    {{-- Flash messages se muestran como toast flotante (app.blade.php) --}}

    @if (!$tablaOk)
        <div style="background: #fff3cd; padding: 10px; font-size: 11px; margin-bottom: 12px;">
            Falta la tabla <code>grupo_proyecto_modulo</code> en MySQL repositorio (solo del módulo, no es intranet).
            Ejecute:
            <code>php artisan migrate --path=database/migrations/2026_05_26_100000_create_grupo_proyecto_modulo_table.php</code>
        </div>
    @endif

    {{-- Filtros --}}
    <div style="margin-bottom: 10px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
        <form method="GET" action="{{ route('grupos-proyecto.index') }}" id="filterForm" style="display: contents;">
            <select name="lapso" class="grp-filter-select" onchange="this.form.submit()">
                <option value="">Lapso</option>
                @foreach ($lapsos as $l)
                    <option value="{{ $l->lap_codigo }}" {{ $filterLapso == $l->lap_codigo ? 'selected' : '' }}>{{ $l->lap_nombre }}</option>
                @endforeach
            </select>
            <select name="programa" class="grp-filter-select" onchange="this.form.submit()" {{ !$filterLapso ? 'disabled' : '' }}>
                <option value="">PNF / Programa</option>
                @foreach ($programas as $p)
                    <option value="{{ $p->pro_codigo }}" {{ $filterPrograma == $p->pro_codigo ? 'selected' : '' }}>{{ $p->pro_siglas }}</option>
                @endforeach
            </select>
            <select name="seccion" class="grp-filter-select" onchange="this.form.submit()" {{ (!$filterLapso || (!$filterPrograma && !$isProfessor)) ? 'disabled' : '' }}>
                <option value="">Sección</option>
                @foreach ($secciones as $s)
                    <option value="{{ $s->sec_codigo }}" {{ $filterSeccion == $s->sec_codigo ? 'selected' : '' }}>{{ $s->sec_nombre }}</option>
                @endforeach
            </select>
            <input name="search" type="text" value="{{ $search }}" placeholder="Buscar nombre&hellip;" class="grp-filter-input" style="flex: 1; min-width: 200px; position:relative;" id="grpSearchInput" autocomplete="off" oninput="buscarGrupo()">
            <div id="grpSearchAutocomplete" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #ccc;border-radius:4px;max-height:220px;overflow-y:auto;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
            <noscript><button type="submit" class="cm-btn cm-btn-sm">Buscar</button></noscript>
        </form>
        @if($isProfessor)
            <a href="{{ route('grupos-proyecto.create') }}" class="cm-btn cm-btn-success cm-btn-sm" style="margin-left: auto;">Registrar nuevo grupo</a>
        @endif
    </div>

    {{-- Listado --}}
    <div id="searchResults">
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Grupos de proyecto registrados</legend>
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 10px;">
                <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; min-width: 780px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold; text-align: center;">
                            <th style="padding: 5px;">Nombre</th>
                            <th style="padding: 5px;">Código</th>
                            <th style="padding: 5px;">PNF</th>
                            <th style="padding: 5px;">Sección</th>
                            <th style="padding: 5px;">Lapso</th>
                            <th style="padding: 5px;">Integrantes</th>
                            <th style="padding: 5px;">Proyecto</th>
                            <th style="padding: 5px;">Creador</th>
                            <th style="padding: 5px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $g)
                            @php
                                $proyecto = $proyectoPorClave->get($g->clave);
                                $tieneProyecto = $proyecto !== null;
                                $estadoVal = $proyecto?->estado_validacion ?? '';
                                $colorMap = ['aprobado' => '#008000', 'rechazado' => '#FF0000', 'completado' => '#2e7d32', 'pendiente' => '#d4a017'];
                                $labelMap = ['aprobado' => 'Aprobado', 'rechazado' => 'Rechazado', 'completado' => 'Completado', 'pendiente' => 'En proceso'];
                            @endphp
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; color: #000;">
                                <td>
                                    <a href="#" onclick="return false;"
                                       style="cursor:pointer; font-weight:bold; color:#333;"
                                       data-grp-codigo="{{ $g->grp_codigo }}"
                                       data-grp-nombre="{{ $g->nombre }}"
                                        data-grp-identificador="{{ $g->identificador ?? '' }}"
                                        data-grp-lapso="{{ $g->lap_nombre ?: 'Lapso #'.$g->lap_codigo }}"
                                       data-grp-pnf="{{ $g->pro_siglas ?: ($g->pro_nombre ?: '—') }}"
                                       data-grp-seccion="{{ $g->sec_nombre ?: 'Sec. '.$g->sec_codigo }}"
                                       data-grp-miembros='{{ json_encode($g->miembros ?? []) }}'
                                       data-grp-proyecto-titulo="{{ $proyecto?->titulo ?? '' }}"
                                       data-grp-proyecto-estado="{{ $estadoVal }}"
                                       onclick="abrirInfoGrupo(this)"
                                       title="Ver información del grupo">{{ $g->nombre }}</a>
                                </td>
                                <td><code style="font-size:9px;color:#8b0000;">{{ $g->identificador ?? '—' }}</code></td>
                                <td>{{ $g->pro_siglas ?: ($g->pro_nombre ?: '—') }}</td>
                                <td>{{ $g->sec_nombre ?: 'Sec. ' . $g->sec_codigo }}</td>
                                <td>{{ $g->lap_nombre ?: '—' }}</td>
                                <td align="center">{{ $g->integrantes }}</td>
                                <td align="center">
                                    @if($tieneProyecto)
                                        <span style="color: {{ $colorMap[$estadoVal] ?? '#d4a017' }}; font-weight: bold; font-size: 10px;">{{ $labelMap[$estadoVal] ?? 'En proceso' }}</span>
                                    @else
                                        <span style="color: #999; font-size: 10px;">Sin proyecto</span>
                                    @endif
                                </td>
                                <td align="center" style="font-size:10px;">
                                    @php $ced = trim($g->creador_cedula ?? ''); @endphp
                                    @if($ced !== '')
                                        {{ trim($g->creador_usuario ?? '') ?: ($creadorNombres[$ced] ?? $ced) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td align="center" nowrap>
                                    @if(($esAdmin ?? false) || ($esCoordinador ?? false))
                                        @if($tieneProyecto)
                                            <a href="{{ route('proyectos.gestion.edit', $proyecto->id) }}" class="cm-btn cm-btn-secondary cm-btn-xs" title="Ver proyecto (solo lectura)">Ver</a>
                                        @else
                                            <span style="color:#999;font-size:10px;">Sin proyecto</span>
                                        @endif
                                    @else
                                    {{-- Actualizar va al formulario de registro del proyecto --}}
                                    @if(!$tieneProyecto || ($proyecto?->estado_validacion ?? '') !== 'aprobado')
                                    <button type="button" class="cm-btn cm-btn-success cm-btn-xs"
                                        onclick="window.location='{{ $tieneProyecto ? route('proyectos.gestion.edit', $proyecto->id) : route('proyectos.gestion.desde-grupo', $g->grp_codigo) }}'"
                                        title="Ir al formulario de proyecto">Actualizar</button>
                                    @endif
                                    @if(trim($g->creador_usuario ?? '') === trim($userUsuNombre) && (!$tieneProyecto || ($proyecto?->estado_validacion ?? '') !== 'aprobado'))
                                        <a href="{{ route('grupos-proyecto.edit', $g->grp_codigo) }}" class="cm-btn cm-btn-secondary cm-btn-xs" title="Editar grupo">Editar</a>
                                    @endif
                                    @if(trim($g->creador_usuario ?? '') === trim($userUsuNombre) && (!$tieneProyecto || ($proyecto?->estado_validacion ?? '') !== 'aprobado'))
                                    <form method="POST" action="{{ route('grupos-proyecto.destroy', $g->grp_codigo) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cm-btn cm-btn-danger cm-btn-xs" title="Eliminar grupo" data-ajax-delete data-delete-name="{{ $g->nombre }}">Eliminar</button>
                                    </form>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" align="center">No hay grupos registrados. Cree uno con integrantes de la sección.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación manual --}}
            @php
                $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 0;
            @endphp
            @if($totalPages > 1)
                <div style="margin-top: 10px; display: flex; justify-content: center; gap: 4px;">
                    @for($i = 1; $i <= $totalPages; $i++)
                        <a href="{{ route('grupos-proyecto.index', array_merge(array_filter(request()->query()), ['page' => $i])) }}"
                           style="padding: 4px 10px; border: 1px solid #ccc; border-radius: 3px; text-decoration: none; font-size: 12px; {{ $page == $i ? 'background: #8bb2b7; color: #fff; font-weight: bold;' : 'background: #fff; color: #333;' }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>
            @endif
        </fieldset>
    </div>

    {{-- Modal de información del grupo --}}
    <div id="infoGrupoModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)cerrarInfoGrupo()">
        <div class="modal-content">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:15px;padding-bottom:10px;border-bottom:2px solid #8b0000;">
                <h3 style="margin:0;font-size:16px;font-weight:bold;color:#333;">Información del grupo</h3>
                <button type="button" onclick="cerrarInfoGrupo()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#999;padding:0 4px;">&times;</button>
            </div>
            <table width="100%" style="font-size:12px;border-collapse:collapse;">
                <tr><td style="padding:6px 8px;font-weight:bold;background:#f5f5f5;width:35%;">Nombre:</td><td id="infoNombre" style="padding:6px 8px;"></td></tr>
                <tr><td style="padding:6px 8px;font-weight:bold;background:#f5f5f5;">Código:</td><td id="infoIdentificador" style="padding:6px 8px;"></td></tr>
                <tr><td style="padding:6px 8px;font-weight:bold;background:#f5f5f5;">Lapso:</td><td id="infoLapso" style="padding:6px 8px;"></td></tr>
                <tr><td style="padding:6px 8px;font-weight:bold;background:#f5f5f5;">PNF:</td><td id="infoPnf" style="padding:6px 8px;"></td></tr>
                <tr><td style="padding:6px 8px;font-weight:bold;background:#f5f5f5;">Sección:</td><td id="infoSeccion" style="padding:6px 8px;"></td></tr>
                <tr><td style="padding:6px 8px;font-weight:bold;background:#f5f5f5;vertical-align:top;">Integrantes:</td>
                    <td id="infoIntegrantes" style="padding:6px 8px;"></td>
                </tr>
                <tr id="infoProyectoRow" style="display:none;">
                    <td style="padding:6px 8px;font-weight:bold;background:#f5f5f5;">Proyecto:</td>
                    <td id="infoProyecto" style="padding:6px 8px;"></td>
                </tr>
            </table>
            <div style="margin-top:15px;text-align:center;">
                <button type="button" onclick="cerrarInfoGrupo()" class="cm-btn cm-btn-secondary">Cerrar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
var _grpTimer = null;
var _grpCache = {};

function buscarGrupo() {
    clearTimeout(_grpTimer);
    var input = document.getElementById('grpSearchInput');
    var dropdown = document.getElementById('grpSearchAutocomplete');
    var q = input.value.trim();

    if (q.length < 2) { dropdown.style.display = 'none'; return; }

    var cacheKey = q.toLowerCase();
    if (_grpCache[cacheKey] && (Date.now() - _grpCache[cacheKey].ts < 30000)) {
        if (_grpCache[cacheKey].html) {
            dropdown.innerHTML = _grpCache[cacheKey].html;
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
        return;
    }

    _grpTimer = setTimeout(function() {
        fetch('{{ route("grupos-proyecto.api.buscar") }}?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.length) {
                    dropdown.style.display = 'none';
                    _grpCache[cacheKey] = { html: null, ts: Date.now() };
                    return;
                }
                var html = '';
                data.forEach(function(g) {
                    var label = g.title;
                    if (g.code) label += ' <span style="color:#8b0000;font-size:10px;">(' + g.code + ')</span>';
                    html += '<div class="grp-autocomplete-item" style="padding:6px 10px;cursor:pointer;border-bottom:1px solid #eee;font-size:12px;" onclick="document.getElementById(\'grpSearchInput\').value=\'' + g.title.replace(/'/g, "\\'") + '\';document.getElementById(\'grpSearchAutocomplete\').style.display=\'none\';document.getElementById(\'filterForm\').submit();">';
                    html += '<strong>' + label + '</strong>';
                    html += '</div>';
                });
                dropdown.innerHTML = html;
                dropdown.style.display = 'block';
                _grpCache[cacheKey] = { html: html, ts: Date.now() };
            }).catch(function() {});
    }, 200);
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#grpSearchInput') && !e.target.closest('#grpSearchAutocomplete')) {
        var d = document.getElementById('grpSearchAutocomplete');
        if (d) d.style.display = 'none';
    }
});

function abrirInfoGrupo(el) {
    var nombre = el.getAttribute('data-grp-nombre');
    var identificador = el.getAttribute('data-grp-identificador');
    var lapso = el.getAttribute('data-grp-lapso');
    var pnf = el.getAttribute('data-grp-pnf');
    var seccion = el.getAttribute('data-grp-seccion');
    var miembros = JSON.parse(el.getAttribute('data-grp-miembros') || '[]');
    var proyTitulo = el.getAttribute('data-grp-proyecto-titulo');
    var proyEstado = el.getAttribute('data-grp-proyecto-estado');

    document.getElementById('infoNombre').textContent = nombre;
    document.getElementById('infoIdentificador').innerHTML = identificador ? '<code style="color:#8b0000;">' + identificador + '</code>' : '—';
    document.getElementById('infoLapso').textContent = lapso;
    document.getElementById('infoPnf').textContent = pnf;
    document.getElementById('infoSeccion').textContent = seccion;

    // Integrantes
    var html = '<table width="100%" style="font-size:11px;border-collapse:collapse;">' +
        '<tr style="background:#ddd;"><th style="padding:3px 6px;">Cédula</th><th style="padding:3px 6px;">Nombre</th><th style="padding:3px 6px;">Rol</th></tr>';
    miembros.forEach(function(m) {
        var rol = (m.rol_id == 1) ? '<span style="color:#8b0000;font-weight:bold;">Líder</span>' : '<span style="color:#666;">Autor</span>';
        html += '<tr style="border-bottom:1px solid #eee;">' +
            '<td style="padding:3px 6px;">' + (m.cedula || '') + '</td>' +
            '<td style="padding:3px 6px;">' + (m.apellido || '') + ', ' + (m.nombre || '') + '</td>' +
            '<td style="padding:3px 6px;">' + rol + '</td></tr>';
    });
    html += '</table>';
    document.getElementById('infoIntegrantes').innerHTML = html;

    // Proyecto
    var proyRow = document.getElementById('infoProyectoRow');
    var proyEl = document.getElementById('infoProyecto');
    if (proyTitulo) {
        var colorMap = {'aprobado':'#008000','rechazado':'#FF0000','completado':'#2e7d32','pendiente':'#d4a017'};
        var labelMap = {'aprobado':'Aprobado','rechazado':'Rechazado','completado':'Completado','pendiente':'En proceso'};
        var estadoColor = colorMap[proyEstado] || '#d4a017';
        var estadoLabel = labelMap[proyEstado] || 'En proceso';
        proyEl.innerHTML = '<span style="font-weight:bold;">' + proyTitulo + '</span><br>' +
            '<span style="font-size:10px;color:#666;">Estado: <span style="color:' + estadoColor + ';font-weight:bold;">' + estadoLabel + '</span></span>';
        proyRow.style.display = '';
    } else {
        proyRow.style.display = 'none';
    }

    document.getElementById('infoGrupoModal').style.display = 'flex';
}

function cerrarInfoGrupo() {
    document.getElementById('infoGrupoModal').style.display = 'none';
}
</script>
@endpush
