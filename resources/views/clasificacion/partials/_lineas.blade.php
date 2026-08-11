@php $items = $items ?? collect(); $esAdminCoord = $esAdminCoord ?? false; @endphp
@if($items->isEmpty())
    <div class="acc-empty">No se encontraron resultados</div>
@else
<table>
    <thead>
        <tr>
            <th>Línea de Investigación</th>
            <th>Área / Coordinación</th>
            <th width="80px">Estado</th>
            <th width="120px">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr style="{{ $item->activo ? '' : 'opacity:.5;' }}">
            <td>
                <span class="font-bold">{{ $item->nombre_investigacion }}</span><br>
                <span style="font-size:10px;color:#6b7280;">{{ \Illuminate\Support\Str::limit($item->descripcion, 50) }}</span>
            </td>
            <td style="font-size:10px;">
                {{ $item->area_de_investigacion }}<br>
                <span style="color:#6b7280;">{{ $item->nombre_programa }}</span>
            </td>
            <td style="text-align:center;">
                @if($item->activo)
                    <span style="color:#059669;font-weight:600;font-size:10px;">ACTIVA</span>
                @else
                    <span style="color:#9ca3af;font-weight:600;font-size:10px;">INACTIVA</span>
                @endif
            </td>
            <td style="text-align:center;">
                <div class="btn-actions" style="justify-content:center;">
                    <button type="button" class="btn-edit" onclick="abrirEditar('lineas','{{ $item->id }}','{{ addslashes($item->nombre_investigacion) }}')">Editar</button>
                    @if($esAdminCoord)
                    <button type="button" class="btn-delete" onclick="eliminarRegistro('lineas','{{ $item->id }}','{{ addslashes($item->nombre_investigacion) }}')">Eliminar</button>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align:center;margin-top:8px;">{{ $items->links() }}</div>
@endif
