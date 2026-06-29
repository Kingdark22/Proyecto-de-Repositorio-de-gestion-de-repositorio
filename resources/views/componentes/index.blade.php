@extends('layouts.app')

@section('title', 'Gestión de Componentes')
@section('header', 'Gestión de Componentes')

@push('styles')
<style>
    .cm-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        padding: 0.55rem 0.95rem;
        min-width: 110px;
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
    .cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.35rem 0.7rem; min-width: auto; font-size: 0.85rem; }
    .cm-btn-group button { margin-right: 0.35rem; margin-bottom: 0.25rem; }
</style>
@endpush

@section('content')
    <div id="flashContainer">
    @if (session('success'))
        <div data-flash-msg style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('success') }}
        </div>
    @endif
    </div>

    <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 12px;">
        <form method="GET" action="{{ route('componentes.index') }}" style="display: flex; align-items: center; gap: 8px; margin: 0;" id="searchForm">
            <b>Buscar:</b>
            <input name="search" type="text" value="{{ $search }}" style="width: 350px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Componente..." oninput="buscarConDebounce(this)">
        </form>
        <span style="margin-left: auto;"></span>
        <button type="button" onclick="window.location='{{ route('componentes.create') }}'" class="cm-btn cm-btn-success" style="font-size: 13px; padding: 6px 14px;">
            Nuevo Componente
        </button>
    </div>

    <div id="searchResults">
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Sistema de Componentes de Proyecto</legend>
            <div style="text-align: right; margin-bottom: 6px; font-size: 11px;">
                <a href="{{ route('componentes.vinculacion') }}" style="color: #19692e; text-decoration: none; border: 1px solid #19692e; padding: 3px 10px; border-radius: 4px; font-weight: bold; display: inline-block;">
                    + Vincular Componentes
                </a>
            </div>
            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N&deg;</th>
                        <th width="20%">Nombre del Componente</th>
                        <th width="22%">Asignaciones (PNF &rarr; Trayecto)</th>
                        <th width="10%">Tipo Archivo</th>
                        <th width="8%">Tama&ntilde;o</th>
                        <th width="8%">Obligatorio</th>
                        <th width="8%">Estatus</th>
                        <th width="14%">Configurar</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($listaRegistros as $item)
                        @php $asignaciones = $item->programas; @endphp
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->estado_logico ? 'color: #888;' : 'color: #000;' }}" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td align="center" style="font-weight: bold; padding: 8px;">{{ $item->nombre }}</td>
                            <td style="padding: 6px; font-size: 10px;">
                                @if($asignaciones->isNotEmpty())
                                    @php
                                        $pnfs = $asignaciones->groupBy('pro_codigo');
                                    @endphp
                                    @foreach($pnfs as $proCodigo => $asigsPnf)
                                        @php
                                            $trayectos = $asigsPnf->pluck('tra_codigo')->filter()->map(function($t) { return 'T.'.$t; })->implode(', ');
                                        @endphp
                                        <span style="display:inline-block; background:#e8f0fe; border:1px solid #b3d4fc; border-radius:3px; padding:2px 6px; margin:1px; white-space:nowrap; font-size:10px;">
                                            <b>{{ $asigsPnf->first()->programa_nombre }}</b>
                                            @if($trayectos) &rarr; {{ $trayectos }}@else <i>(todos)</i>@endif
                                        </span>
                                    @endforeach
                                @else
                                    <span style="color:#999; font-style:italic;">Global</span>
                                @endif
                            </td>
                            <td align="center" style="padding: 8px;">
                                <span style="font-weight:bold;text-transform:uppercase;">{{ $item->tipo_archivo ?? 'pdf' }}</span>
                            </td>
                            <td align="center" style="padding: 8px;">
                                @if($item->tamano_maximo_mb)
                                    <span style="font-weight:bold;">{{ $item->tamano_maximo_mb }} MB</span>
                                @else
                                    <span style="color:#999;">10 MB</span>
                                @endif
                            </td>
                            <td align="center">
                                {!! $item->es_obligatorio
                                    ? '<span style="color: #FF0000; font-weight:bold;">S&Iacute;</span>'
                                    : '<span style="color: #008000; font-weight:bold;">NO</span>' !!}
                            </td>
                            <td align="center">
                                @if ($item->estado_logico)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Suspendido</span>
                                @endif
                            </td>
                            <td align="center">
                                <div class="cm-btn-group" style="display: inline-flex; flex-wrap: wrap; justify-content: center;">
                                    <button type="button" onclick="window.location='{{ route('componentes.edit', $item->id) }}'" title="Editar" class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" data-ajax-toggle="{{ route('componentes.toggle', $item->id) }}" data-toggle-name="{{ $item->nombre }}" title="{{ $item->estado_logico ? 'Suspender' : 'Activar' }}" class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->estado_logico ? 'Suspender' : 'Activar' }}</button>
                                    <form method="POST" action="{{ route('componentes.destroy', $item->id) }}" style="display:inline;" >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm" data-ajax-delete data-delete-name="{{ $item->nombre }}">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($listaRegistros->isEmpty())
                        <tr>
                            <td colspan="8" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay componentes configurados en la Base de Datos.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $listaRegistros->links() }}</div>
        </fieldset>
    </div>
@endsection
