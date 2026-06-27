@extends('layouts.app')

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

    .cm-btn:hover {
        transform: translateY(-1px);
    }

    .cm-btn-primary {
        background: #19692e;
        border-color: #154f26;
        color: #fff;
    }

    .cm-btn-success {
        background: #198754;
        border-color: #166f43;
        color: #fff;
    }

    .cm-btn-warning {
        background: #f0b606;
        border-color: #d99e00;
        color: #212529;
    }

    .cm-btn-danger {
        background: #c82333;
        border-color: #a71d2a;
        color: #fff;
    }

    .cm-btn-secondary {
        background: #f4f4f4;
        border-color: #c2c2c2;
        color: #222;
    }

    .cm-btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('title', 'Metodologías de Investigación')
@section('header', 'Gesti&oacute;n de Metodolog&iacute;as de Investigaci&oacute;n')

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
    <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; gap: 20px;">
        <div>
            <form method="GET" action="{{ route('metodologia-investigacion') }}" style="display: inline-flex; align-items: center; gap: 6px;">
                <b>Buscar Metodolog&iacute;a:</b>
                <input name="search" type="text" value="{{ $search }}" style="width: 400px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Nombre de la metodología...">
                <button type="submit" class="cm-btn cm-btn-sm">Buscar</button>
            </form>
        </div>

        <button type="button" onclick="window.location='{{ route('metodologia-investigacion.create') }}'" class="cm-btn cm-btn-success cm-btn-sm">
            Registrar Metodolog&iacute;a
        </button>
    </div>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Metodolog&iacute;as
            de Investigaci&oacute;n</legend>

        <table width="100%" border="1" cellpadding="4" cellspacing="0"
            style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th style="padding: 5px;" width="25%">Metodolog&iacute;a</th>
                    <th style="padding: 5px;" width="45%">Descripci&oacute;n</th>
                    <th style="padding: 5px;" width="10%">Estado</th>
                    <th style="padding: 5px;" width="20%">Acciones</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach ($items as $item)
                    <tr
                        style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->estado_logico ? 'color: #888;' : 'color: #000;' }}">
                        <td align="center" style="font-weight: bold; padding: 5px;">
                            {{ $item->nombre }}
                        </td>
                        <td align="left" style="padding: 5px; font-size: 11px;">
                            {{ $item->descripcion ?: 'Sin descripción' }}
                        </td>
                        <td align="center">
                            @if ($item->estado_logico)
                                <span style="color: #008000; font-weight: bold;">Activo</span>
                            @else
                                <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                            @endif
                        </td>
                        <td align="center">
                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                <button type="button" onclick="window.location='{{ route('metodologia-investigacion.edit', $item->id) }}'" title="Editar"
                                    class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>

                                <button type="button" onclick="if(confirm('{{ $item->estado_logico ? '¿Estás seguro de deshabilitar esta metodología?' : '¿Estás seguro de habilitar esta metodología?' }}')){ window.location='{{ route('metodologia-investigacion.toggle', $item->id) }}' }"
                                    title="{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}"
                                    class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}</button>

                                <form action="{{ route('metodologia-investigacion.destroy', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar PERMANENTEMENTE esta metodología de investigación?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if ($items->isEmpty())
                    <tr>
                        <td colspan="4" align="center"
                            style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                            No se encontraron resultados
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 10px;">
            {{ $items->links() }}
        </div>
    </fieldset>
@endsection
