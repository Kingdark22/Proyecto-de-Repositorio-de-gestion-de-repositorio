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
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.35rem 0.7rem; font-size: 0.85rem; }
</style>
@endpush

@section('content')
    @if (session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('error') }}
        </div>
    @endif

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Buscador y listado</legend>
        <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="65%">
                    <form method="GET" action="{{ route('comunidades.index') }}" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                        <b>Buscar (nombre / RIF):</b>
                        <input name="search" type="text" value="{{ $search }}" style="width: 70%; padding: 3px;" placeholder="Nombre o RIF...">
                        <button type="submit" class="cm-btn cm-btn-sm">Buscar</button>
                    </form>
                </td>
                <td width="35%" align="right">
                    @if ($puedeGestionar)
                        <button type="button" onclick="window.location='{{ route('comunidades.create') }}'" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 8px 18px;">
                            Registrar nueva comunidad
                        </button>
                    @endif
                </td>
            </tr>
        </table>

        <table width="100%" border="1" cellpadding="5" cellspacing="0"
            style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 10px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                    <th width="4%">N°</th>
                    <th width="30%">Comunidad / dirección</th>
                    <th width="11%">RIF</th>
                    <th width="16%">Contacto</th>
                    <th width="10%">Acciones</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach ($comunidades as $c)
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                        <td align="center">{{ $loop->iteration }}</td>
                        <td>
                            <span style="font-weight: bold;">{{ $c->nombre }}</span>
                            <br><span style="font-size: 9px; color: #555;">{{ $c->direccion?->municipio?->estado?->est_nombre ?? '' }} / {{ $c->direccion?->municipio?->mun_nombre ?? '' }} - {{ $c->direccion?->dir_calle ?? '' }}</span>
                        </td>
                        <td align="center">{{ $c->rif }}</td>
                        <td align="center">{{ $c->correo }}<br><b>{{ $c->numero_telefono }}</b></td>
                        <td align="center">
                            @if ($puedeGestionar)
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" onclick="window.location='{{ route('comunidades.edit', $c->id) }}'"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <form method="POST" action="{{ route('comunidades.destroy', $c->id) }}" style="display: inline; margin: 0;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar esta comunidad?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
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
                        <td colspan="6" align="center" style="padding: 20px;">No hay comunidades registradas.</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div style="margin-top: 10px;">{{ $comunidades->links() }}</div>
    </fieldset>
@endsection
