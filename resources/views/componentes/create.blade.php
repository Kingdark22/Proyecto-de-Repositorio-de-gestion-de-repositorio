@extends('layouts.app')

@section('title', 'Registrar Componente')
@section('header', 'Registrar Componente')

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
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .obligatorio { color: red; font-weight: bold; }
    .validation-error { color: #dc3545; font-size: 11px; }
</style>
@endpush

@section('content')
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
        <legend style="padding:0 5px;font-weight:bold;">&nbsp;</legend>

        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold;">
                <ul style="margin:0;padding-left:20px;">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('componentes.store') }}" style="margin: 0;" onsubmit="return validarFormulario(this)">
            @csrf
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 12px;">
                <tr>
                    <td width="25%"><b>Nombre del componente:</b></td>
                    <td width="75%">
                        <input name="nombre" type="text" value="{{ old('nombre') }}"
                            style="width: 95%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required>
                        <span class="obligatorio">*</span>
                        @error('nombre')<br><span class="validation-error">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Tipo de archivo:</b></td>
                    <td>
                        <select name="tipo_archivo" style="width: 60%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                            @foreach (\App\Models\Componente::tiposArchivo() as $val => $label)
                                <option value="{{ $val }}" {{ old('tipo_archivo') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="obligatorio">*</span>
                        @error('tipo_archivo')<br><span class="validation-error">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Tamaño máximo (MB):</b></td>
                    <td>
                        <input name="tamano_maximo_mb" type="number" value="{{ old('tamano_maximo_mb', 10) }}"
                            style="width: 80px; padding: 6px; border: 1px solid #ccc; border-radius: 4px;" min="1" max="200">
                        MB
                        <span class="obligatorio">*</span>
                        @error('tamano_maximo_mb')<br><span class="validation-error">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Obligatorio:</b></td>
                    <td>
                        <input type="checkbox" name="es_obligatorio" value="1" {{ old('es_obligatorio', true) ? 'checked' : '' }}
                            style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-size: 11px; color: #666;">(marcar si el proyecto debe incluir obligatoriamente este documento)</span>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 15px; font-size: 13px;">
                Los campos con <span class="obligatorio">*</span> son obligatorios
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="button" onclick="window.location='{{ route('componentes.index') }}'" class="cm-btn cm-btn-danger" style="margin-right: 10px;">Cancelar</button>
                <button type="submit" class="cm-btn cm-btn-primary" data-confirm-register data-entity-type="Componente">Guardar Componente</button>
            </div>
        </form>
    </fieldset>
@endsection
