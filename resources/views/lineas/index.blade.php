@extends('layouts.app')

@section('title', 'Líneas de Investigación')
@section('header', 'Gestión de Líneas de Investigación')

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
    <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
        <form method="GET" action="{{ route('lineas-investigacion') }}" style="display: flex; align-items: center; gap: 8px; margin: 0;">
            <b>Buscar Línea:</b>
            <input name="search" type="text" value="{{ $search }}" style="width: 400px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Nombre de la línea...">
            <button type="submit" class="cm-btn cm-btn-sm">Buscar</button>
        </form>
        <button type="button" onclick="window.location='{{ route('lineas-investigacion.create') }}'" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 6px 16px;">
            Registrar Línea
        </button>
    </div>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Líneas de Investigación</legend>

        <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th style="padding: 5px;">Línea de Investigación</th>
                    <th style="padding: 5px;">Área / Coordinación</th>
                    <th style="padding: 5px;" width="80">Estado</th>
                    <th style="padding: 5px;" width="100">Acciones</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach ($items as $item)
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->activo ? 'color: #888;' : 'color: #000;' }}">
                        <td align="center" style="font-weight: bold;">
                            <b>{{ $item->nombre_investigacion }}</b>
                            <br>
                            <span style="font-size: 10px; font-weight: normal;">{{ Str::limit($item->descripcion, 50) ?: 'Sin descripción' }}</span>
                        </td>
                        <td align="center" style="padding: 5px;">
                            {{ $item->area_de_investigacion }}
                            <br>
                            <span style="font-size: 10px; font-weight: bold;">Programa: {{ $item->nombre_programa }}</span>
                        </td>
                        <td align="center">
                            @if ($item->activo)
                                <span style="color: #008000; font-weight: bold;">Activo</span>
                            @else
                                <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                            @endif
                        </td>
                        <td align="center">
                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                <button type="button" onclick="window.location='{{ route('lineas-investigacion.edit', $item->id) }}'" title="Editar" class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                <button type="button" onclick="if(confirm('{{ $item->activo ? '¿Deshabilitar esta línea?' : '¿Habilitar esta línea?' }}')){ window.location='{{ route('lineas-investigacion.toggle', $item->id) }}' }" title="{{ $item->activo ? 'Deshabilitar' : 'Habilitar' }}" class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->activo ? 'Deshabilitar' : 'Habilitar' }}</button>
                                <form method="POST" action="{{ route('lineas-investigacion.destroy', $item->id) }}" style="display: inline; margin: 0;" onsubmit="return confirm('¿Estás seguro de eliminar PERMANENTEMENTE esta línea de investigación?')">
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
@endsection
