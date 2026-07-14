@extends('layouts.app')

@section('title', 'Gestión de Proyectos')
@section('header', 'Gestión de Proyectos')

@push('styles')
<style>
    .cm-btn {
        display: inline-flex;
        align-items: center; justify-content: center; border-radius: 6px;
        padding: 0.45rem 0.85rem; font-size: 0.85rem; font-weight: 600;
        border: 1px solid transparent; cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
    }
    .cm-btn { color: #fff; }
    td a.cm-btn:visited { color: #fff; }
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
    .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
    .filter-select, .filter-input { height: 30px; padding: 3px 6px; font-size: 11px; border: 1px solid #ccc; border-radius: 4px; }
    .filter-select { min-width: 130px; }
    .filter-input { width: 150px; }
    .autocomplete-item:hover { background: #f0f7ff; }
    .autocomplete-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')


    {{-- PROYECTOS LÍDER (estudiante) --}}
    @if ($esEstudianteLider)
        <fieldset style="border: 2px solid #2e7d32; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
            <legend style="color: #2e7d32; font-weight: bold; font-style: italic; padding: 0 5px;">Mis proyectos</legend>
            @if($proyectosLider->isNotEmpty())
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #a5d6a7; color: #000; text-align: center; font-weight: bold;">
                            <th width="30%">Título</th>
                            <th width="25%">Comunidad / equipo</th>
                            <th width="20%">Validación</th>
                            <th width="25%">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proyectosLider as $p)
                            <tr style="background: {{ $loop->iteration % 2 == 0 ? '#E8F5E9' : '#FFF' }};" valign="top">
                                <td style="padding:5px;font-weight:bold;">
                                    {{ $p->titulo }}
                                </td>
                                <td style="padding:5px;font-size:10px;">
                                    @if ($p->equipo_resumen !== '—')
                                        <b>Equipo:</b> {{ $p->equipo_resumen }}<br>
                                    @endif
                                    @if (($p->comunidad->nombre ?? '') !== '' && $p->comunidad->nombre !== 'N/A')
                                        <b>Comunidad:</b> {{ $p->comunidad->nombre }}
                                    @endif
                                </td>
                                <td align="center" style="padding:5px;">
                                    @if ($p->estado_validacion === 'completado')
                                        <span style="color: #2e7d32; font-weight: bold;">Completado</span>
                                    @elseif($p->estado_validacion === 'aprobado')
                                        <span style="color: #008000; font-weight: bold;">Aprobado</span>
                                    @elseif($p->estado_validacion === 'rechazado')
                                        <span style="color: #FF0000; font-weight: bold;" title="{{ $p->motivo_rechazo }}">Rechazado</span>
                                    @else
                                        <span style="color: #d4a017; font-weight: bold;">En proceso</span>
                                    @endif
                                </td>
                                <td align="center" style="padding:5px;">
                                    @if ($p->estado_validacion !== 'aprobado')
                                        <a href="{{ route('proyectos.gestion.edit', $p->id) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                    @endif
                                    @if ($p->estado_validacion === 'aprobado')
                                        <a href="{{ route('proyectos.gestion.solvencia', $p->id) }}" class="cm-btn cm-btn-primary cm-btn-sm">Solvencia</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-size:11px;color:#666;padding:10px;">No tienes proyectos asignados como líder.</p>
            @endif
        </fieldset>
    @endif

    {{-- LISTADO GENERAL (admin/coordinador/gestionador) --}}
    @if ($mostrarListado)
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de proyectos institucionales</legend>

            {{-- Equipos del profesor (dentro del mismo fieldset) --}}
            @if (!empty($gruposDocente))
                <div style="margin-bottom:12px;">
                    <div style="font-size:12px;font-weight:bold;color:#2e7d32;margin-bottom:5px;">Equipos disponibles para registrar proyecto</div>
                    <table width="100%" border="1" cellpadding="4" cellspacing="0"
                        style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                        <thead>
                            <tr style="background-color: #a5d6a7; color: #000; text-align: center; font-weight: bold;">
                                <th width="25%">Nombre del equipo</th>
                                <th width="15%">PNF / Sección</th>
                                <th width="10%">Integrantes</th>
                                <th width="25%">Proyecto</th>
                                <th width="25%">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gruposDocente as $g)
                                @php $g = (object) $g; @endphp
                                <tr style="background: {{ $loop->iteration % 2 == 0 ? '#E8F5E9' : '#FFF' }};" valign="top">
                                    <td style="padding:5px;font-weight:bold;">{{ $g->nombre }}</td>
                                    <td style="padding:5px;font-size:10px;">
                                        {{ $g->pro_siglas ?? '' }}@if($g->sec_nombre) · Secc. {{ $g->sec_nombre }}@endif
                                    </td>
                                    <td align="center" style="padding:5px;">{{ $g->integrantes }}</td>
                                    <td align="center" style="padding:5px;">
                                        @if ($g->tiene_proyecto)
                                            @if ($g->proyecto_estado_validacion === 'aprobado')
                                                <span style="color:#008000;font-weight:bold;">Aprobado</span>
                                            @elseif($g->proyecto_estado_validacion === 'rechazado')
                                                <span style="color:#FF0000;font-weight:bold;">Rechazado</span>
                                            @else
                                                <span style="color:#d4a017;font-weight:bold;">En proceso</span>
                                            @endif
                                        @else
                                            <span style="color:#999;">Sin proyecto</span>
                                        @endif
                                    </td>
                                    <td align="center" style="padding:5px;">
                                        @if ($esAdmin)
                                            <span style="color:#999;font-size:10px;">{{ $g->tiene_proyecto ? 'Tiene proyecto' : 'Sin proyecto' }}</span>
                                        @else
                                            @if ($g->tiene_proyecto)
                                                @if ($g->proyecto_estado_validacion !== 'aprobado')
                                                    <a href="{{ route('proyectos.gestion.edit', $g->proyecto_id) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                                @else
                                                    <span style="color:#008000;font-weight:bold;font-size:10px;">Aprobado</span>
                                                @endif
                                            @else
                                                <a href="{{ route('proyectos.gestion.desde-grupo', $g->grp_codigo) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Botón Exportar Excel (solo administrador y coordinador) --}}
            @php
                $activeRoleExport = app(\App\Services\UserRoleService::class)->getActiveRole(auth()->user());
            @endphp
            @if(in_array($activeRoleExport, ['administrador', 'coordinador']))
            <div style="margin-bottom:8px; display:flex; align-items:center; justify-content:flex-end;">
                <button type="button" onclick="abrirModalExcel()"
                   class="cm-btn cm-btn-success"
                   style="background:#155724; border-color:#0d3d19; font-size:0.8rem; padding:0.4rem 0.9rem;"
                   title="Descargar reporte Excel del depósito de proyectos">
                    &#8595; Exportar a Excel
                </button>
            </div>
            @endif

            <form method="GET" action="{{ route('proyectos.gestion') }}" style="margin-bottom:8px;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size:11px;">
                    <tr>
                        <td width="33%" style="position:relative;"><b>Título:</b><br>
                            <input name="search" type="text" value="{{ $search }}" class="filter-input" id="searchProjectInput" style="width:95%;" placeholder="Buscar..." autocomplete="off" oninput="buscarProyectos()">
                            <div id="searchAutocomplete" style="display:none;position:absolute;top:100%;left:0;right:5%;background:#fff;border:1px solid #ccc;border-radius:4px;max-height:220px;overflow-y:auto;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
                        </td>
                        <script>
                            function escAttr(str) { return String(str).replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
                            function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
                            let timerProyectos;
                            let timerAutocomplete;
                            let _proyectosSearchCache = {};

                            function buscarProyectos() {
                                clearTimeout(timerProyectos);
                                timerProyectos = setTimeout(function() {
                                    filtrarProyectos();
                                }, 200);
                            }

                            document.getElementById('searchProjectInput').addEventListener('input', function() {
                                var q = this.value.trim();
                                var dropdown = document.getElementById('searchAutocomplete');
                                clearTimeout(timerAutocomplete);
                                if (q.length < 2) { dropdown.style.display = 'none'; return; }

                                // Caché inmediata
                                var cacheKey = q.toLowerCase();
                                var cached = _proyectosSearchCache[cacheKey];
                                if (cached && (Date.now() - cached.ts < 30000)) {
                                    if (cached.html) {
                                        dropdown.innerHTML = cached.html;
                                        dropdown.style.display = 'block';
                                    } else {
                                        dropdown.style.display = 'none';
                                    }
                                    return;
                                }

                                timerAutocomplete = setTimeout(function() {
                                    fetch('{{ route("proyectos.gestion.buscar-ajax") }}?q=' + encodeURIComponent(q))
                                        .then(function(r) { return r.json(); })
                                        .then(function(data) {
                                            if (!data.length) {
                                                dropdown.style.display = 'none';
                                                _proyectosSearchCache[cacheKey] = { html: null, ts: Date.now() };
                                                return;
                                            }
                                            var html = '';
                                            data.forEach(function(p) {
                                                html += '<div class="autocomplete-item" data-id="' + escAttr(p.id) + '" style="padding:6px 10px;cursor:pointer;border-bottom:1px solid #eee;font-size:12px;" onclick="irAProyecto(' + escAttr(p.id) + ')">';
                                                html += '<strong>' + escHtml(p.title) + '</strong>';
                                                html += '</div>';
                                            });
                                            dropdown.innerHTML = html;
                                            dropdown.style.display = 'block';
                                            _proyectosSearchCache[cacheKey] = { html: html, ts: Date.now() };
                                        }).catch(function() {});
                                }, 200);
                            });

                            document.addEventListener('click', function(e) {
                                if (!e.target.closest('#searchProjectInput') && !e.target.closest('#searchAutocomplete')) {
                                    document.getElementById('searchAutocomplete').style.display = 'none';
                                }
                            });
                            function irAProyecto(id) {
                                window.location.href = '{{ url("proyectos/gestion") }}/' + id + '/edit';
                            }
                        </script>
                        <td width="33%"><b>Estado:</b><br>
                                <select name="estado" class="filter-select" id="filterEstado" style="width:95%;" onchange="filtrarProyectos()">
                                    <option value="">- Todos -</option>
                                    <option value="pendiente" {{ $filterEstado == 'pendiente' ? 'selected' : '' }}>En proceso</option>
                                    <option value="completado" {{ $filterEstado == 'completado' ? 'selected' : '' }}>Completado</option>
                                    <option value="aprobado" {{ $filterEstado == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="rechazado" {{ $filterEstado == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                </select>
                        </td>
                        <td width="34%"><b>Comunidad:</b><br>
                            <select name="comunidad" class="filter-select" id="filterComunidad" style="width:95%;" onchange="filtrarProyectos()">
                                <option value="">- Todas -</option>
                                @foreach(($datosListado['comunidades'] ?? []) as $com)
                                    <option value="{{ $com->id }}" {{ $filterComunidad == $com->id ? 'selected' : '' }}>{{ $com->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </table>
                <noscript><button type="submit" class="cm-btn cm-btn-sm">Buscar</button></noscript>
            </form>

            <div id="listadoProyectos">
                @include('proyectos._listado_tabla')
            </div>
            <div id="listadoPaginacion" style="margin-top:10px;">{{ ($datosListado['proyectos'] ?? collect())->links() }}</div>
        </fieldset>
    @endif

    {{-- MODAL EXPORTAR EXCEL — MULTI-FILTRO --}}
    <div id="excelModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarModalExcel()">
        <div style="background:#fff;border-radius:8px;padding:25px;max-width:680px;width:94%;box-shadow:0 8px 32px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;">
            <h3 style="margin:0 0 15px;font-weight:bold;font-size:17px;color:#000;">Exportar a Excel</h3>
            <p style="font-size:12px;color:#555;margin-bottom:14px;">Filtros disponibles para la exportación (solo proyectos aprobados):</p>
            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size:11px;">
                <tr>
                    <td width="50%" style="position:relative;"><b>Búsqueda:</b><br>                            <input type="text" id="excelSearch" placeholder="Buscar por título, resumen o equipo..." autocomplete="off"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;box-sizing:border-box;">
                        <div id="excelSearchAutocomplete" style="display:none;position:absolute;top:100%;left:0;right:4%;background:#fff;border:1px solid #ccc;border-radius:6px;max-height:220px;overflow-y:auto;z-index:10000;box-shadow:0 4px 16px rgba(0,0,0,0.18);margin-top:2px;"></div>
                        <div id="excelSearchBadge" style="display:none;margin-top:5px;padding:4px 10px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:4px;font-size:11px;color:#2e7d32;align-items:center;gap:4px;"></div>
                    </td>
                    <td width="50%"><b>Lapso académico:</b><br>
                        <select id="excelLapsoSelect" onchange="cargarProgramas()"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todos -</option>
                            @foreach($lapsosFiltro as $lap)
                                <option value="{{ $lap->lap_codigo }}">{{ $lap->lap_nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><b>Programa (PNF):</b><br>
                        <select id="excelProgramaSelect" onchange="cargarTrayectos()"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todos -</option>
                        </select>
                    </td>
                    <td><b>Trayecto:</b><br>
                        <select id="excelTrayectoSelect" onchange="cargarSecciones()"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todos -</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><b>Sección:</b><br>
                        <select id="excelSeccionSelect"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todas -</option>
                        </select>
                    </td>
                    <td><b>Comunidad:</b><br>
                        <select id="excelComunidadSelect"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todas -</option>
                            @foreach($comunidadesFiltro as $com)
                                <option value="{{ $com->com_codigo }}">{{ $com->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><b>Línea de investigación:</b><br>
                        <select id="excelLineaSelect"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todas -</option>
                            @foreach($lineasFiltro as $lin)
                                <option value="{{ $lin->id }}">{{ $lin->nombre_investigacion }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><b>Tipo de investigación:</b><br>
                        <select id="excelTipoInvSelect"
                            style="width:96%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todos -</option>
                            @foreach($tiposInvFiltro as $tin)
                                <option value="{{ $tin->id }}">{{ $tin->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><b>Metodología:</b><br>
                        <select id="excelMetodologiaSelect"
                            style="width:48%;padding:6px;border:1px solid #999;border-radius:4px;font-size:12px;background:#fff;box-sizing:border-box;">
                            <option value="">- Todas -</option>
                            @foreach($metodologiasFiltro as $met)
                                <option value="{{ $met->id }}">{{ $met->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            </table>
            <div style="margin-top:18px;text-align:center;display:flex;gap:12px;justify-content:center;">
                <button type="button" class="cm-btn cm-btn-success" onclick="descargarExcel()" style="padding:8px 24px;font-size:14px;">Descargar</button>
                <button type="button" class="cm-btn cm-btn-secondary" onclick="cerrarModalExcel()" style="padding:8px 24px;font-size:14px;">Cancelar</button>
            </div>
        </div>
    </div>

    {{-- MODAL RECHAZO --}}
    <div id="rejectModal" class="modal-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)cerrarRechazar()">
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
    var _filtroProyectosTimer = null;
    function filtrarProyectos() {
        clearTimeout(_filtroProyectosTimer);
        _filtroProyectosTimer = setTimeout(function() {
            var search = (document.getElementById('searchProjectInput') || {}).value || '';
            var estado = (document.getElementById('filterEstado') || {}).value || '';
            var comunidad = (document.getElementById('filterComunidad') || {}).value || '';
            var params = new URLSearchParams();
            if (search) params.set('search', search);
            if (estado) params.set('estado', estado);
            if (comunidad) params.set('comunidad', comunidad);
            params.set('ajax_listado', '1');
            var url = '{{ route("proyectos.gestion") }}?' + params.toString();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var target = document.getElementById('listadoProyectos');
                    if (target && data.html) target.innerHTML = data.html;
                    var pagTarget = document.getElementById('listadoPaginacion');
                    if (pagTarget && data.paginacion) pagTarget.innerHTML = data.paginacion;
                    else if (pagTarget) pagTarget.innerHTML = '';
                });
        }, 300);
    }
    function abrirRechazar(id) {
        document.getElementById('rejectForm').action = '{{ route("proyectos.gestion.reject", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
        document.getElementById('rejectModal').style.display = 'flex';
    }
    function cerrarRechazar() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    function abrirModalExcel() {
        limpiarSeleccionExcel();
        var searchActual = (document.getElementById('searchProjectInput') || {}).value || '';
        document.getElementById('excelSearch').value = searchActual;
        document.getElementById('excelModal').style.display = 'flex';
        cargarProgramas();
    }
    function cerrarModalExcel() {
        document.getElementById('excelModal').style.display = 'none';
    }

    var progBase = '{{ route("proyectos.gestion.export-programs", ["lapso" => "REPLACE_LAPSO"]) }}';
    var traBase = '{{ route("proyectos.gestion.export-trayectos", ["lapso" => "REPLACE_LAPSO"]) }}';
    var secBase = '{{ route("proyectos.gestion.export-secciones", ["lapso" => "REPLACE_LAPSO"]) }}';

    function cargarProgramas() {
        var lapso = document.getElementById('excelLapsoSelect').value;
        var progSel = document.getElementById('excelProgramaSelect');
        var traSel = document.getElementById('excelTrayectoSelect');
        var secSel = document.getElementById('excelSeccionSelect');

        traSel.innerHTML = '<option value="">- Todos -</option>';
        secSel.innerHTML = '<option value="">- Todas -</option>';

        if (!lapso) {
            progSel.innerHTML = '<option value="">- Todos -</option>';
            return;
        }

        fetch(progBase.replace('REPLACE_LAPSO', encodeURIComponent(lapso)))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                progSel.innerHTML = '<option value="">- Todos -</option>';
                data.forEach(function(p) {
                    progSel.innerHTML += '<option value="' + p.pro_codigo + '">' + (p.pro_siglas || p.pro_nombre) + '</option>';
                });
                cargarTrayectos();
            }).catch(function() {});
    }

    function cargarTrayectos() {
        var lapso = document.getElementById('excelLapsoSelect').value;
        var programa = document.getElementById('excelProgramaSelect').value;
        var traSel = document.getElementById('excelTrayectoSelect');
        var secSel = document.getElementById('excelSeccionSelect');

        secSel.innerHTML = '<option value="">- Todas -</option>';

        if (!lapso) {
            traSel.innerHTML = '<option value="">- Todos -</option>';
            return;
        }

        var url = traBase.replace('REPLACE_LAPSO', encodeURIComponent(lapso));
        if (programa) {
            url += '?programa=' + encodeURIComponent(programa);
        }

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                traSel.innerHTML = '<option value="">- Todos -</option>';
                data.forEach(function(t) {
                    traSel.innerHTML += '<option value="' + t.tra_codigo + '">' + t.tra_nombre + '</option>';
                });
                cargarSecciones();
            }).catch(function() {});
    }

    function cargarSecciones() {
        var lapso = document.getElementById('excelLapsoSelect').value;
        var programa = document.getElementById('excelProgramaSelect').value;
        var trayecto = document.getElementById('excelTrayectoSelect').value;
        var secSel = document.getElementById('excelSeccionSelect');

        if (!lapso) {
            secSel.innerHTML = '<option value="">- Todas -</option>';
            return;
        }

        var url = secBase.replace('REPLACE_LAPSO', encodeURIComponent(lapso));
        var params = [];
        if (programa) params.push('programa=' + encodeURIComponent(programa));
        if (trayecto) params.push('trayecto=' + encodeURIComponent(trayecto));
        if (params.length) url += '?' + params.join('&');

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                secSel.innerHTML = '<option value="">- Todas -</option>';
                data.forEach(function(s) {
                    secSel.innerHTML += '<option value="' + s.sec_codigo + '">Secc. ' + s.sec_nombre + '</option>';
                });
            }).catch(function() {});
    }
    // Autocomplete para búsqueda en modal excel — multi-parámetro con caché en memoria y AbortController
    (function() {
        var input = document.getElementById('excelSearch');
        var dropdown = document.getElementById('excelSearchAutocomplete');
        var badge = document.getElementById('excelSearchBadge');

        var cache = {};
        var CACHE_TTL = 30000;
        var pendingController = null;

        function queryKey(q, filters) {
            return q.toLowerCase().replace(/\s+/g, ' ').trim() + '|' + JSON.stringify(filters);
        }

        function getActiveFilters() {
            return {
                comunidad: document.getElementById('excelComunidadSelect')?.value || '',
                lapso: document.getElementById('excelLapsoSelect')?.value || '',
                linea: document.getElementById('excelLineaSelect')?.value || '',
                tipo_investigacion: document.getElementById('excelTipoInvSelect')?.value || '',
                metodologia: document.getElementById('excelMetodologiaSelect')?.value || '',
            };
        }

        function buscarProyectos() {
            var q = input.value.trim();
            clearTimeout(window.excelSearchTimer);
            if (q.length < 2) { dropdown.style.display = 'none'; return; }

            var filters = getActiveFilters();
            var key = queryKey(q, filters);

            var cached = cache[key];
            if (cached && (Date.now() - cached.ts < CACHE_TTL)) {
                dropdown.innerHTML = cached.html || '';
                dropdown.style.display = cached.html ? 'block' : 'none';
                return;
            }

            window.excelSearchTimer = setTimeout(function() {
                if (pendingController) pendingController.abort();
                pendingController = new AbortController();
                var signal = pendingController.signal;

                var url = '{{ route("proyectos.gestion.buscar-ajax") }}?q=' + encodeURIComponent(q);
                Object.keys(filters).forEach(function(k) {
                    if (filters[k]) url += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(filters[k]);
                });
                url += '&_=' + Date.now();

                fetch(url, { signal: signal })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!data.length) {
                            dropdown.style.display = 'none';
                            cache[key] = { html: null, ts: Date.now() };
                            return;
                        }
                        var html = '';
                        data.forEach(function(p) {
                            html += '<div class="excel-autocomplete-item" data-id="' + p.id + '" data-title="' + escAttr(p.title) + '" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #e8e8e8;font-size:12px;">';
                            html += '<strong style="font-size:12px;color:#222;">' + escHtml(p.title) + '</strong>';
                            html += '</div>';
                        });
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';
                        cache[key] = { html: html, ts: Date.now() };
                    }).catch(function(err) {
                        if (err.name === 'AbortError') return;
                    });
            }, 150);
        }

        input.addEventListener('input', buscarProyectos);

        // Cuando cambia cualquier filtro, re-buscar si hay texto
        ['excelLapsoSelect','excelComunidadSelect','excelLineaSelect','excelTipoInvSelect','excelMetodologiaSelect'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', function() {
                if (input.value.trim().length >= 2) {
                    cache = {}; // Limpiar caché al cambiar filtros
                    buscarProyectos();
                }
            });
        });

        dropdown.addEventListener('click', function(e) {
            var item = e.target.closest('.excel-autocomplete-item');
            if (!item) return;
            seleccionarProyectoExcel(item.dataset.title);
        });

        dropdown.addEventListener('mouseover', function(e) {
            var item = e.target.closest('.excel-autocomplete-item');
            if (item) item.style.background = '#f0f7ff';
        });
        dropdown.addEventListener('mouseout', function(e) {
            var item = e.target.closest('.excel-autocomplete-item');
            if (item) item.style.background = '#fff';
        });

        function escAttr(str) {
            return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
    })();

    function seleccionarProyectoExcel(title) {
        var input = document.getElementById('excelSearch');
        var badge = document.getElementById('excelSearchBadge');
        input.value = title;
        // Mostrar badge de selección
        badge.style.display = 'inline-flex';
        badge.innerHTML = 'Buscar por: <strong>' + escHtml(title) + '</strong> <span class="excel-badge-clear" style="margin-left:6px;cursor:pointer;font-weight:bold;color:#c62828;" onclick="limpiarSeleccionExcel()">&times;</span>';
        document.getElementById('excelSearchAutocomplete').style.display = 'none';
    }

    function limpiarSeleccionExcel() {
        var input = document.getElementById('excelSearch');
        input.value = '';
        document.getElementById('excelSearchBadge').style.display = 'none';
        document.getElementById('excelSearchAutocomplete').style.display = 'none';
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#excelSearch') && !e.target.closest('#excelSearchAutocomplete')) {
            var d = document.getElementById('excelSearchAutocomplete');
            if (d) d.style.display = 'none';
        }
    });

    // Limpiar selección si el usuario escribe manualmente
    document.getElementById('excelSearch').addEventListener('keydown', function() {
        if (this.value.trim()) {
            document.getElementById('excelSearchBadge').style.display = 'none';
        }
    });

    function escHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function descargarExcel() {
        var params = [];
        var campos = [
            { id: 'excelSearch', key: 'search' },
            { id: 'excelLapsoSelect', key: 'lapso' },
            { id: 'excelProgramaSelect', key: 'programa' },
            { id: 'excelTrayectoSelect', key: 'trayecto' },
            { id: 'excelSeccionSelect', key: 'seccion' },
            { id: 'excelComunidadSelect', key: 'comunidad' },
            { id: 'excelLineaSelect', key: 'linea' },
            { id: 'excelTipoInvSelect', key: 'tipo_investigacion' },
            { id: 'excelMetodologiaSelect', key: 'metodologia' },
        ];
        campos.forEach(function(c) {
            var val = document.getElementById(c.id).value;
            if (val) {
                params.push(encodeURIComponent(c.key) + '=' + encodeURIComponent(val));
            }
        });
        var url = '{{ route("proyectos.gestion.exportar-excel") }}';
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        window.open(url, '_blank');
        cerrarModalExcel();
    }
    </script>
@endsection
