@php $items = $items ?? collect(); $esAdminCoord = $esAdminCoord ?? false; @endphp
@if($items->isEmpty())
    <div class="acc-empty">No se encontraron resultados</div>
@else
<table>
    <thead>
        <tr>
            <th width="25%">Objetivo de Investigación</th>
            <th width="45%">Descripción</th>
            <th width="10%">Estado</th>
            <th width="20%">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr style="{{ $item->estado_logico ? '' : 'opacity:.5;' }}">
            <td class="font-bold">{{ $item->nombre }}</td>
            <td style="font-size:10px;">{{ $item->descripcion ?: 'Sin descripción' }}</td>
            <td style="text-align:center;">
                @if($item->estado_logico)
                    <span style="color:#059669;font-weight:600;font-size:10px;">Activo</span>
                @else
                    <span style="color:#9ca3af;font-weight:600;font-size:10px;">Inactivo</span>
                @endif
            </td>
            <td style="text-align:center;">
                <div class="btn-actions" style="justify-content:center;">
                    <button type="button" class="btn-edit" onclick="abrirEditar('objetivos','{{ $item->id }}','{{ addslashes($item->nombre) }}')">Editar</button>
                    @if($esAdminCoord)
                    <button type="button" class="btn-delete" onclick="eliminarRegistro('objetivos','{{ $item->id }}','{{ addslashes($item->nombre) }}')">Eliminar</button>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align:center;margin-top:8px;">{{ $items->links() }}</div>
@endif
