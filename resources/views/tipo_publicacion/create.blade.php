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
.cm-btn:hover { transform: translateY(-1px); }
.cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
.cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
.cm-btn-warning { background: #f0b606; border-color: #d99e00; color: #212529; }
.cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
.cm-btn-secondary { background: #f4f4f4; border-color: #c2c2c2; color: #222; }
.cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
</style>
@endpush

@section('title', 'Registrar Tipo de Publicación')
@section('header', 'Registrar Tipo de Publicación')

@section('content')

@if ($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold;">
        <ul style="margin:0;padding-left:20px;">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
@endif

<fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
    <legend style="padding:0 5px;font-weight:bold;">&nbsp;</legend>

    <form method="POST" action="{{ route('tipos-publicacion.store') }}" style="margin: 0;" onsubmit="return validarFormulario(this)">
        @csrf

        <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
            <tr>
                <td width="35%"><b>Nombre del Tipo:</b></td>
                <td width="65%">
                                <input type="text" name="nombre" value="{{ old('nombre') }}" style="width: 90%;" required oninput="validarNombre(this)" data-check-url="/tipos-publicacion/check-nombre">
                                <span class="obligatorio">*</span>
                                <span id="nombreStatus" style="font-size:11px;display:none;"></span>
                                @error('nombre')
                                    <br><span class="validation-error">{{ $message }}</span>
                                @enderror
                </td>
            </tr>
            <tr>
                <td width="35%"><b>Mención Honorífica:</b></td>
                <td width="65%">
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="mencion_honorifica" value="1" {{ old('mencion_honorifica') ? 'checked' : '' }}>
                        <span style="font-size: 12px;">¿Este tipo otorga mérito especial?</span>
                    </label>
                    @error('mencion_honorifica')
                        <br><span class="validation-error">{{ $message }}</span>
                    @enderror
                </td>
            </tr>
        </table>

        <div style="margin-top: 15px; font-size: 13px;">
            Los campos con <span class="obligatorio">*</span> son obligatorios
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <button type="button" onclick="window.location='{{ route('tipos-publicacion') }}'" class="cm-btn cm-btn-danger" style="margin-right: 10px;">Cancelar</button>
            <button type="submit" class="cm-btn cm-btn-primary" data-confirm-register data-entity-type="Tipo de Publicación">Guardar</button>
        </div>
    </form>
</fieldset>

@endsection
