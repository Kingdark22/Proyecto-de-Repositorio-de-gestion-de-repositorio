@extends('layouts.app')

@section('title', 'Editar Comunidad')
@section('header', 'Editar Comunidad')

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
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Modificar comunidad</legend>

        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold;">
                <ul style="margin:0;padding-left:20px;">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('comunidades.update', $id) }}" style="margin: 0;">
            @csrf
            @method('PUT')
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="50%" style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Nombre:</b>
                            <div style="flex: 1;">
                                <input name="nombre" type="text" value="{{ old('nombre', $datos['nombre']) }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required>
                                <span class="obligatorio">*</span>
                                @error('nombre')<br><span class="validation-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </td>
                    <td width="50%" style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 40px;">RIF:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <select name="rif_letra" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 11px; width: 48px;">
                                        @foreach(['V','E','J','G','P'] as $l)
                                            <option value="{{ $l }}" {{ ($parsed['letra'] ?? 'J') == $l ? 'selected' : '' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                    <input name="rif_numero" type="text" inputmode="numeric" maxlength="9" value="{{ old('rif_numero', $parsed['numero'] ?? '') }}"
                                        style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Número (máx. 9 dígitos)">
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                @error('rif_numero')<br><span class="validation-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Correo:</b>
                            <div style="flex: 1;">
                                <input name="correo" type="email" value="{{ old('correo', $datos['correo']) }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="ejemplo@gmail.com">
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                @error('correo')<br><span class="validation-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Teléfono:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <select name="prefijo_telefono" style="padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        @foreach(['0424','0414','0412','0422','0416','0426'] as $p)
                                            <option value="{{ $p }}" {{ $prefijo == $p ? 'selected' : '' }}>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                    <input name="numero_telefono" type="text" value="{{ old('numero_telefono', $numeroTel) }}" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="XXX-XXXX">
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                @error('numero_telefono')<br><span class="validation-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Estado:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <select name="estado_id" id="estado_id" onchange="cargarMunicipios(this.value)" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;" required>
                                        <option value="">-- Seleccione estado --</option>
                                        @foreach ($estados as $e)
                                            <option value="{{ $e->est_codigo }}" {{ $datos['estado_id'] == $e->est_codigo ? 'selected' : '' }}>{{ $e->est_nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="obligatorio">*</span>
                                </div>
                                @error('estado_id')<br><span class="validation-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Municipio:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <select name="municipio_id" id="municipio_id" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;" required>
                                        <option value="">-- Seleccione municipio --</option>
                                        @foreach ($municipios as $m)
                                            <option value="{{ $m->mun_codigo }}" {{ $datos['municipio_id'] == $m->mun_codigo ? 'selected' : '' }}>{{ $m->mun_nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="obligatorio">*</span>
                                </div>
                                @error('municipio_id')<br><span class="validation-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 145px;">Dirección exacta:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: flex-start; gap: 4px;">
                                    <input name="dir_nombre" type="text" value="{{ old('dir_nombre', $datos['dir_nombre']) }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Av./Calle/Casa Nro., sector, referencia..." required>
                                    <span class="obligatorio" style="margin-top: 5px;">*</span>
                                </div>
                                @error('dir_nombre')<br><span class="validation-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 15px; font-size: 13px;">
                Los campos con <span class="obligatorio">*</span> son obligatorios
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="button" onclick="window.location='{{ route('comunidades.index') }}'" class="cm-btn cm-btn-danger" style="margin-right: 10px;">Cancelar</button>
                <button type="submit" class="cm-btn cm-btn-primary">Guardar</button>
            </div>
        </form>
    </fieldset>
@endsection

@push('scripts')
<script>
function cargarMunicipios(estadoId) {
    var munSelect = document.getElementById('municipio_id');
    munSelect.innerHTML = '<option value="">-- Cargando... --</option>';
    if (!estadoId) {
        munSelect.innerHTML = '<option value="">-- Seleccione municipio --</option>';
        return;
    }
    var selectedMun = '{{ $datos['municipio_id'] }}';
    fetch('/comunidades/municipios/' + estadoId)
        .then(r => r.json())
        .then(data => {
            munSelect.innerHTML = '<option value="">-- Seleccione municipio --</option>';
            data.forEach(function(m) {
                var opt = document.createElement('option');
                opt.value = m.mun_codigo;
                opt.textContent = m.mun_nombre;
                if (selectedMun == m.mun_codigo) opt.selected = true;
                munSelect.appendChild(opt);
            });
        })
        .catch(() => {
            munSelect.innerHTML = '<option value="">-- Error al cargar --</option>';
        });
}
</script>
@endpush
