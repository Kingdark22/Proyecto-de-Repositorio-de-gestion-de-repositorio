@php $items = $items ?? collect(); $esAdminCoord = $esAdminCoord ?? false; @endphp
@if($items->isEmpty())
    <div class="acc-empty">No se encontraron resultados</div>
@else
<div style="text-align:right;margin-bottom:8px;">
    @if($esAdminCoord)
    <button type="button" class="btn-vincular" onclick="abrirVinculacion()" style="padding:6px 14px;font-size:11px;">+ Vincular Componentes</button>
    @endif
</div>
<table>
    <thead>
        <tr>
            <th width="15%">Componente</th>
            <th width="22%">Asignaciones</th>
            <th width="9%">Tipo Archivo</th>
            <th width="8%">Tamaño</th>
            <th width="8%">Obligatorio</th>
            <th width="8%">Estatus</th>
            <th width="18%">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td class="font-bold">{{ $item->comp_nombre }}</td>
            <td style="font-size:10px;">
                @php
                    $grupos = $item->programas->groupBy('pro_codigo');
                @endphp
                @forelse($grupos as $progCodigo => $progItems)
                    @php $primero = $progItems->first(); @endphp
                    <div style="margin-bottom:3px;">
                        <span style="color:#2563eb;font-weight:600;">{{ $primero->pro_siglas ?? $primero->pro_nombre ?? 'PNF' }}</span>
                        @foreach($progItems as $prog)
                            <span style="display:inline-block;background:#dbeafe;border-radius:3px;padding:1px 5px;font-size:9px;margin:1px;">T{{ $prog->tra_codigo }}</span>
                        @endforeach
                    </div>
                @empty
                    <span style="color:#9ca3af;">Sin asignar</span>
                @endforelse
            </td>
            <td style="text-align:center;text-transform:uppercase;font-size:10px;">{{ $item->tipo_archivo }}</td>
            <td style="text-align:center;font-size:10px;">{{ $item->tamano_maximo_mb }} MB</td>
            <td style="text-align:center;">
                @if($item->es_obligatorio)
                    <span style="color:#dc2626;font-weight:600;font-size:10px;">SI</span>
                @else
                    <span style="color:#059669;font-weight:600;font-size:10px;">NO</span>
                @endif
            </td>
            <td style="text-align:center;">
                @if($item->estado_logico)
                    <span style="color:#059669;font-weight:600;font-size:10px;">Activo</span>
                @else
                    <span style="color:#9ca3af;font-weight:600;font-size:10px;">Suspendido</span>
                @endif
            </td>
            <td style="text-align:center;">
                <div class="btn-actions" style="justify-content:center;">
                    <button type="button" class="btn-edit" onclick="abrirEditar('componentes','{{ $item->id }}','{{ addslashes($item->comp_nombre) }}')">Editar</button>
                    @if($esAdminCoord)
                    <button type="button" class="btn-delete" onclick="eliminarRegistro('componentes','{{ $item->id }}','{{ addslashes($item->comp_nombre) }}')">Eliminar</button>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align:center;margin-top:8px;">{{ $items->links() }}</div>
@endif
