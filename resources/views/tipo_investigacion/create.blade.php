@extends('layouts.app')

@section('title', 'Registrar Tipo de Investigación')
@section('header', 'Registrar Tipo de Investigación')

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

    .obligatorio {
        color: #FF0000;
        font-weight: bold;
    }

    .validation-error {
        color: #dc3545;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
        <legend style="padding:0 5px;font-weight:bold;">&nbsp;</legend>

        @if ($errors->any())
            <div style="color: #dc3545; font-weight: bold; margin-bottom: 15px;">
                <ul style="margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tipos-investigacion.store') }}" style="margin: 0;" onsubmit="return validarFormulario(this)">
            @csrf
            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                <tr>
                    <td width="30%"><b>Nombre del Tipo:</b></td>
                    <td width="70%">
                        <input name="nombre" type="text" value="{{ old('nombre') }}" style="width: 90%;">
                        <span class="obligatorio">*</span>
                        @error('nombre')
                            <br><span class="validation-error">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td width="30%" valign="top"><b>Descripci&oacute;n Breve:</b></td>
                    <td width="70%">
                        <textarea name="descripcion" rows="4" style="width: 90%;">{{ old('descripcion') }}</textarea>
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
                <button type="button" onclick="window.location='{{ route('tipos-investigacion') }}'" class="cm-btn cm-btn-danger"
                    style="margin-right: 10px;">Cancelar</button>
                <button type="submit" class="cm-btn cm-btn-primary" data-confirm-register data-entity-type="Tipo de Investigación">Guardar</button>
            </div>
        </form>
    </fieldset>
@endsection
