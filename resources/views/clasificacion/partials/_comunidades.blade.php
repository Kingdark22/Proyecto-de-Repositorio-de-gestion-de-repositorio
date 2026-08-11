@php $items = $items ?? collect(); $esAdminCoord = $esAdminCoord ?? false; @endphp
@if($items->isEmpty())
    <div class="acc-empty">No se encontraron resultados</div>
@else
<table>
    <thead>
        <tr>
            <th>Comunidad</th>
            <th width="12%">RIF</th>
            <th width="16%">Contacto</th>
            <th width="12%">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $c)
        @php
            $dir = $c->direccion ?? null;
            $mun = $dir ? $dir->municipio : null;
            $est = $mun ? $mun->estado : null;
        @endphp
        <tr>
            <td>
                <span class="font-bold">{{ $c->nombre }}</span><br>
                <span style="font-size:10px;color:#6b7280;">
                    {{ $est->est_nombre ?? '' }}{{ $mun && $mun->mun_nombre ? ', '.$mun->mun_nombre : '' }}{{ $dir && $dir->dir_calle ? ', '.$dir->dir_calle : '' }}
                </span>
            </td>
            <td>{{ $c->rif }}</td>
            <td style="font-size:10px;">{{ $c->correo }}<br>{{ $c->numero_telefono }}</td>
            <td style="text-align:center;">
                <div class="btn-actions" style="justify-content:center;">
                    <button type="button" class="btn-edit" onclick="abrirEditar('comunidades','{{ $c->id }}','{{ addslashes($c->nombre) }}')">Editar</button>
                    @if($esAdminCoord)
                    <button type="button" class="btn-delete" onclick="eliminarRegistro('comunidades','{{ $c->id }}','{{ addslashes($c->nombre) }}')">Eliminar</button>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align:center;margin-top:8px;">{{ $items->links() }}</div>
@endif
