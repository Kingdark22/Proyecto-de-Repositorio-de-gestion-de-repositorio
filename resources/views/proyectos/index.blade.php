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
</style>
@endpush

@section('content')
    @if (session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">{{ session('error') }}</div>
    @endif

    {{-- GRUPOS DEL DOCENTE (profesor/admin) --}}
    @if (!empty($gruposDocente))
        <fieldset style="border: 2px solid #2e7d32; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
            <legend style="color: #2e7d32; font-weight: bold; font-style: italic; padding: 0 5px;">Equipos disponibles para registrar proyecto</legend>
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
                                @if ($g->tiene_proyecto)
                                    <a href="{{ route('proyectos.gestion.edit', $g->proyecto_id) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                    @if (($g->proyecto_estado_validacion ?? '') === 'aprobado')
                                        <a href="{{ route('proyectos.gestion.solvencia', $g->proyecto_id) }}" class="cm-btn cm-btn-primary cm-btn-sm">Solvencia</a>
                                    @endif
                                @else
                                    <a href="{{ route('proyectos.gestion.desde-grupo', $g->grp_codigo) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </fieldset>
    @endif

    {{-- PROYECTOS LÍDER (estudiante) --}}
    @if ($esEstudianteLider)
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
                    <tbody>
                        @foreach ($proyectosLider as $p)
                            <tr style="background: {{ $loop->iteration % 2 == 0 ? '#E8F5E9' : '#FFF' }};" valign="top">
                                <td style="padding:5px;font-weight:bold;">
                                    {{ $p->titulo }}
                                </td>
                                <td style="padding:5px;font-size:10px;">{{ $p->comunidad->nombre ?? 'N/A' }}</td>
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
                                    <a href="{{ route('proyectos.gestion.edit', $p->id) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
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
            <form method="GET" action="{{ route('proyectos.gestion') }}" style="margin-bottom:8px;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size:11px;">
                    <tr>
                        <td width="33%"><b>Título:</b><br>
                            <input name="search" type="text" value="{{ $search }}" class="filter-input" style="width:95%;" placeholder="Buscar...">
                        </td>
                        <td width="33%"><b>Estado:</b><br>
                                <select name="estado" class="filter-select" style="width:95%;" onchange="this.form.submit()">
                                    <option value="">- Todos -</option>
                                    <option value="pendiente" {{ $filterEstado == 'pendiente' ? 'selected' : '' }}>En proceso</option>
                                    <option value="completado" {{ $filterEstado == 'completado' ? 'selected' : '' }}>Completado</option>
                                    <option value="aprobado" {{ $filterEstado == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="rechazado" {{ $filterEstado == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                </select>
                        </td>
                        <td width="34%"><b>Comunidad:</b><br>
                            <select name="comunidad" class="filter-select" style="width:95%;" onchange="this.form.submit()">
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

            @php $proyectos = $datosListado['proyectos'] ?? collect(); @endphp
            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="25%">Título</th>
                        <th width="20%">Comunidad / equipo</th>
                        <th width="15%">Validación</th>
                        <th width="35%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proyectos as $p)
                        <tr style="background: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFF' }}; color: #000;" valign="top">
                            <td style="padding:5px;font-weight:bold;">
                                {{ $p->titulo }}
                            </td>
                            <td style="padding:5px;font-size:10px;">
                                Equipo: {{ $p->equipo_resumen }}<br>
                                Comunidad: {{ $p->comunidad->nombre ?? 'N/A' }}
                            </td>
                            <td align="center" style="padding:5px;">
                                @if ($p->estado_validacion === 'pendiente')
                                    <span style="color:#d4a017;font-weight:bold;">En proceso</span>
                                @elseif($p->estado_validacion === 'completado')
                                    <span style="color:#2e7d32;font-weight:bold;">Completado</span>
                                @elseif($p->estado_validacion === 'rechazado')
                                    <span style="color:#FF0000;font-weight:bold;" title="{{ $p->motivo_rechazo }}">Rechazado</span>
                                @else
                                    <span style="color:#008000;font-weight:bold;">Aprobado</span>
                                @endif
                            </td>
                            <td align="center" style="padding:5px;">
                                <div style="display:inline-flex;gap:4px;flex-wrap:wrap;justify-content:center;">
                                    @if (!empty($canValidate) && $p->estado_validacion === 'completado')
                                        <a href="{{ route('proyectos.gestion.approve', $p->id) }}" class="cm-btn cm-btn-success cm-btn-sm" onclick="return confirm('¿Aprueba este proyecto?')">Aprobar</a>
                                        <button type="button" class="cm-btn cm-btn-warning cm-btn-sm" onclick="abrirRechazar({{ $p->id }})">Rechazar</button>
                                    @endif
                                    <a href="{{ route('proyectos.gestion.edit', $p->id) }}" class="cm-btn cm-btn-primary cm-btn-sm">Actualizar</a>
                                    @if ($p->estado_validacion === 'aprobado')
                                        <a href="{{ route('proyectos.gestion.solvencia', $p->id) }}" class="cm-btn cm-btn-primary cm-btn-sm">Solvencia</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($proyectos->isEmpty())
                        <tr><td colspan="4" align="center" style="padding:20px;font-weight:bold;">No hay expedientes registrados</td></tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top:10px;">{{ $proyectos->links() }}</div>
        </fieldset>
    @endif

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
    function abrirRechazar(id) {
        document.getElementById('rejectForm').action = '{{ route("proyectos.gestion.reject", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
        document.getElementById('rejectModal').style.display = 'flex';
    }
    function cerrarRechazar() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    </script>
@endsection
