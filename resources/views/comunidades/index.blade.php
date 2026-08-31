@extends('layouts.app')

@section('title', 'Gestión de Comunidades')
@section('header', 'Gestión de Comunidades')

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
    .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.35rem 0.7rem; font-size: 0.85rem; }
</style>
@endpush

@section('content')

    <div id="searchResults">
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Comunidades</legend>

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th style="padding: 5px;">Comunidad / dirección</th>
                        <th style="padding: 5px;">RIF</th>
                        <th style="padding: 5px;">Contacto</th>
                        <th style="padding: 5px;" width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($comunidades as $c)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td style="padding: 5px;">
                                <span style="font-weight: bold;">{{ $c->nombre }}</span>
                                <br><span style="font-size: 9px; color: #555;">{{ $c->direccion?->municipio?->estado?->est_nombre ?? '' }} / {{ $c->direccion?->municipio?->mun_nombre ?? '' }} - {{ $c->direccion?->dir_calle ?? '' }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">{{ $c->rif }}</td>
                            <td align="center" style="padding: 5px;">{{ $c->correo }}<br><b>{{ $c->numero_telefono }}</b></td>
                            <td align="center" style="padding: 5px;">
                                @if ($puedeGestionar)
                                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                                        <button type="button" onclick="window.location='{{ route('comunidades.edit', $c->id) }}'"
                                            class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                        <form method="POST" action="{{ route('comunidades.destroy', $c->id) }}" style="display: inline; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cm-btn cm-btn-danger cm-btn-sm" data-ajax-delete data-delete-name="{{ $c->nombre }}">Eliminar</button>
                                        </form>
                                    </div>
                                @else
                                    <span style="color: #888; font-size: 10px;">Solo lectura</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if ($comunidades->isEmpty())
                        <tr>
                            <td colspan="4" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay comunidades registradas.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div style="margin-top: 10px;">
                {{ $comunidades->links() }}
            </div>
        </fieldset>
    </div>
@endsection
