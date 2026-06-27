@extends('layouts.app')

@section('title', 'Registrar Línea de Investigación')
@section('header', 'Registrar Línea de Investigación')

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
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Registrar Línea</legend>

        <form method="POST" action="{{ route('lineas-investigacion.store') }}" style="margin: 0;">
            @csrf
            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                <tr>
                    <td width="30%"><b>Nombre Línea de Inv.:</b></td>
                    <td width="70%">
                        <input name="nombre_investigacion" type="text" value="{{ old('nombre_investigacion') }}" style="width: 90%;" required>
                        <span class="obligatorio">*</span>
                        @error('nombre_investigacion')
                            <br><span class="validation-error">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td width="30%"><b>Área Académica:</b></td>
                    <td width="70%">
                        <input name="area_de_investigacion" type="text" value="{{ old('area_de_investigacion') }}" style="width: 90%;" required>
                        <span class="obligatorio">*</span>
                        @error('area_de_investigacion')
                            <br><span class="validation-error">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td width="30%"><b>Seleccionar Programa:</b></td>
                    <td width="70%">
                        <select name="programa_id" style="width: 90%; padding: 2px;" required>
                            <option value="">Seleccione un Programa...</option>
                            @foreach ($programas as $p)
                                <option value="{{ $p->id }}" {{ old('programa_id') == $p->id ? 'selected' : '' }}>{{ $p->siglas }} - {{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="obligatorio">*</span>
                        @error('programa_id')
                            <br><span class="validation-error">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td width="30%" valign="top"><b>Descripción Breve:</b></td>
                    <td width="70%">
                        <textarea name="descripcion" rows="3" style="width: 90%;" required>{{ old('descripcion') }}</textarea>
                        <span class="obligatorio">*</span>
                        @error('descripcion')
                            <br><span class="validation-error">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
            </table>

            <div style="margin-top: 15px; font-size: 13px;">
                Los campos con <span class="obligatorio">*</span> son obligatorios
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="button" onclick="window.location='{{ route('lineas-investigacion') }}'" class="cm-btn cm-btn-danger" style="margin-right: 10px;">Cancelar</button>
                <button type="submit" class="cm-btn cm-btn-primary">Guardar</button>
            </div>
        </form>
    </fieldset>
@endsection
