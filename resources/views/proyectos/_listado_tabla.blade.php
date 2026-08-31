@php
    $proyectos = $datosListado['proyectos'] ?? collect();
    $gruposSinProyecto = collect($gruposDocente ?? [])
        ->map(function($g) { return (object) $g; })
        ->filter(function($g) { return !($g->tiene_proyecto ?? false); })
        ->values();
@endphp
<table width="100%" border="1" cellpadding="4" cellspacing="0"
    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
    <thead>
        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
            <th width="4%">N&deg;</th>
            <th width="26%">Nombre</th>
            <th width="22%">Detalles del equipo</th>
            <th width="14%">Estado</th>
            <th width="34%">Acci&oacute;n</th>
        </tr>
    </thead>
    <tbody>
        @php $combinedRow = 1; @endphp
        @foreach ($gruposSinProyecto as $g)
            <tr style="background: {{ $combinedRow % 2 == 0 ? '#E0E0E0' : '#FFF' }};" valign="top">
                <td align="center" style="padding:5px;font-weight:bold;">{{ $combinedRow++ }}</td>
                <td style="padding:5px;font-weight:bold;">{{ $g->nombre }}</td>
                <td style="padding:5px;font-size:10px;">
                    PNF: {{ $g->pro_siglas ?? 'N/A' }}@if($g->sec_nombre) &middot; Secc. {{ $g->sec_nombre }}@endif<br>
                    <b>Integrantes:</b> {{ $g->integrantes }}
                </td>
                <td align="center" style="padding:5px;">
                    <span style="color:#999;">Sin proyecto</span>
                </td>
                <td align="center" style="padding:5px;">
                    <a href="{{ route('proyectos.gestion.desde-grupo', $g->grp_codigo) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                </td>
            </tr>
        @endforeach
        @foreach ($proyectos as $p)
            <tr style="background: {{ $combinedRow % 2 == 0 ? '#E0E0E0' : '#FFF' }}; color: #000;" valign="top">
                <td align="center" style="padding:5px;font-weight:bold;">{{ $combinedRow++ }}</td>
                <td style="padding:5px;">
                    <span style="font-weight:bold;">{{ $p->titulo }}</span><br>
                    <span style="font-size:10px;color:#555;">Equipo: {{ $p->equipo_resumen }}</span>
                </td>
                <td style="padding:5px;font-size:10px;">
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
                        @if (!empty($canValidate) && $p->estado_validacion === 'pendiente' && !in_array($p->pry_codigo, $proyectosConDocumentosRechazados ?? []))
                            <button type="button" class="cm-btn cm-btn-success cm-btn-sm" onclick="mostrarModalAccion({icon:'\u2705',title:'Aprobar proyecto',message:'\u00bfAprueba este proyecto?',detailValue:'{{ addslashes($p->titulo) }}',confirmText:'S\u00ed, aprobar',confirmClass:'cm-btn-success',onConfirm:function(){window.location='{{ route('proyectos.gestion.approve', $p->id) }}'}})">Aprobar</button>
                            <button type="button" class="cm-btn cm-btn-warning cm-btn-sm" onclick="abrirRechazar({{ $p->id }})">Rechazar</button>
                        @endif
                        @if (($esAdmin ?? false) || ($esCoordinador ?? false) || ($esGestionador ?? false))
                            <a href="{{ route('proyectos.gestion.edit', $p->id) }}" class="cm-btn cm-btn-secondary cm-btn-sm" style="background:#fff;border:2px solid #6c757d;color:#333;font-weight:600;">Ver</a>
                        @elseif ($p->estado_validacion !== 'aprobado')
                            <a href="{{ route('proyectos.gestion.edit', $p->id) }}" class="cm-btn cm-btn-success cm-btn-sm">Actualizar</a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        @if ($combinedRow === 1)
            <tr><td colspan="5" align="center" style="padding:20px;font-weight:bold;">No hay expedientes registrados</td></tr>
        @endif
    </tbody>
</table>