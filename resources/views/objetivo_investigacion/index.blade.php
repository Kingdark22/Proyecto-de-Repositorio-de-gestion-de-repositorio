@extends('layouts.app')

@section('title', 'Objetivos de Investigación')
@section('header', 'Gestión de Objetivos de la Investigación')

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

@section('content')
    <div id="flashContainer">
    @if (session('success'))
        <div data-flash-msg
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom: 15px; display: flex; align-items: center;">
        <form method="GET" action="{{ route('objetivos-investigacion') }}" style="display: inline-flex; align-items: center;" id="searchForm">
            <b>Buscar Objetivo:</b>
            <input name="search" type="text" value="{{ $search }}"
                style="width: 400px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999; margin-left: 5px;"
                placeholder="Nombre del objetivo..." oninput="buscarConDebounce(this)">
        </form>

        <button type="button" onclick="window.location='{{ route('objetivos-investigacion.create') }}'"
            class="cm-btn cm-btn-success"
            style="font-size: 14px; padding: 6px 16px; margin-left: auto; margin-right: 30px;">
            Registrar Objetivo
        </button>
    </div>

    <div id="searchResults">
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Objetivos de la
                Investigación</legend>

            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="25%">Objetivo de la Investigación</th>
                        <th padding="5" width="45%">Descripción</th>
                        <th padding="5" width="10%">Estado</th>
                        <th padding="5" width="20%">Acciones</th>
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
                                    <button type="button"
                                        onclick="window.location='{{ route('objetivos-investigacion.edit', $item->id) }}'"
                                        title="Editar" class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button"
                                        data-ajax-toggle="{{ route('objetivos-investigacion.toggle', $item->id) }}" data-toggle-name="{{ $item->nombre }}"
                                        title="{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}"
                                        class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}</button>
                                    <form action="{{ route('objetivos-investigacion.destroy', $item->id) }}" method="POST"
                                        style="display: inline; margin: 0;"
                                        >
                                        @csrf
                                        @method('DELETE')<button type="submit" title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm" data-ajax-delete data-delete-name="{{ $item->nombre }}">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($items->isEmpty())
                        <tr>
                            <td colspan="4" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
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
    </div>
@endsection
