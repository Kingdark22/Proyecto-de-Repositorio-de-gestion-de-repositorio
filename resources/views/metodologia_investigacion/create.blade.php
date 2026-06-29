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

    .cm-btn-danger {
        background: #c82333;
        border-color: #a71d2a;
        color: #fff;
    }

    .cm-btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }

    .obligatorio {
        color: #FF0000;
    }

    .validation-error {
        color: #dc3545;
        font-size: 11px;
    }
</style>
@endpush

@section('title', 'Registrar Metodología de Investigación')
@section('header', 'Registrar Metodología de Investigación')

@section('content')
<div>
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
        <legend style="padding:0 5px;font-weight:bold;">&nbsp;</legend>

        <form method="POST" action="{{ route('metodologia-investigacion.store') }}" style="margin: 0;" onsubmit="return validarFormulario(this)">
            @csrf
            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                <tr>
                    <td width="30%"><b>Nombre de Metodología:</b></td>
                    <td width="70%">
                                <input name="nombre" type="text" value="{{ old('nombre') }}" maxlength="100" style="width: 90%;" required oninput="validarNombre(this)" data-check-url="/metodologia-investigacion/check-nombre">
                                <span class="obligatorio">*</span>
                                <span id="nombreStatus" style="font-size:11px;display:none;"></span>
                                @error('nombre')
                                    <br><span class="validation-error">{{ $message }}</span>
                                @enderror
                    </td>
                </tr>
                <tr>
                    <td width="30%" valign="top"><b>Descripción Breve:</b></td>
                    <td width="70%">
                        <textarea name="descripcion" rows="4" maxlength="500" style="width: 90%;" required>{{ old('descripcion') }}</textarea>
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
                <button type="button" onclick="window.location='{{ route('metodologia-investigacion') }}'" class="cm-btn cm-btn-danger"
                    style="margin-right: 10px;">Cancelar</button>
                <button type="submit" class="cm-btn cm-btn-primary" data-confirm-register data-entity-type="Metodología de Investigación">Guardar</button>
            </div>
        </form>
    </fieldset>
</div>
@endsection
