@extends('layouts.app')

@section('title', 'Vinculación de Componentes')
@section('header', 'Vinculaci&oacute;n de Componentes &rarr; PNF + Trayectos')

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

    .comp-checkbox:checked + .comp-label {
        background: #e8f5e9;
        border-color: #198754;
        font-weight: bold;
    }
    .comp-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background: #fafafa;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.15s ease;
    }
    .comp-label:hover {
        background: #f0f0f0;
        border-color: #999;
    }
    .comp-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
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

    <form method="POST" action="{{ route('componentes.vinculacion.guardar') }}" id="vinculacionForm">
        @csrf

        {{-- Selección de Componentes --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 15px; margin-bottom: 20px; background: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                Seleccionar Componentes
            </legend>

            <div style="font-size:12px;color:#666;margin-bottom:10px;">
                Seleccione uno o m&aacute;s componentes para vincularlos a los PNF y trayectos que elija abajo.
            </div>

            @if($componentes->isEmpty())
                <div style="text-align:center;padding:20px;font-weight:bold;color:#999;">
                    No hay componentes activos disponibles.
                </div>
            @else
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($componentes as $comp)
                        @php
                            $asigsComp = $asignaciones->where('comp_codigo', $comp->id);
                        @endphp
                        <label class="comp-label" style="{{ $asigsComp->isNotEmpty() ? 'background:#e8f5e9;border-color:#198754;' : '' }}">
                            <input type="checkbox" name="componente_ids[]" value="{{ $comp->id }}"
                                class="comp-checkbox"
                                onchange="toggleComponenteLabel(this)">
                            <span>
                                <b>{{ $comp->nombre }}</b>
                                @if($asigsComp->isNotEmpty())
                                    <span style="font-size:10px;color:#666;display:block;">
                                        ({{ $asigsComp->count() }} vinculacione{{ $asigsComp->count() === 1 ? 's' : 's' }})
                                    </span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                <div style="margin-top:10px;font-size:11px;color:#666;">
                    <span id="selectedCount">0</span> componente(s) seleccionado(s).
                    <button type="button" class="cm-btn cm-btn-sm" onclick="seleccionarTodos(true)" style="margin-left:8px;">Seleccionar todos</button>
                    <button type="button" class="cm-btn cm-btn-sm" onclick="seleccionarTodos(false)">Deseleccionar todos</button>
                </div>
            @endif
        </fieldset>

        {{-- PNF + Trayectos --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 15px; margin-bottom: 20px; background: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                Asignar PNF y Trayectos
            </legend>

            @if(!empty($pnfRows))
                <div style="font-size:12px;color:#666;margin-bottom:10px;">
                    Marque los PNF y trayectos que desea asignar a los componentes seleccionados arriba.
                </div>

                <table width="100%" border="1" cellpadding="6" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="5%">N&deg;</th>
                            <th width="15%">PNF</th>
                            <th width="5%">Activo</th>
                            <th width="60%">Trayectos asignados</th>
                            <th width="15%"></th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach($pnfRows as $proCodigo => $row)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                                <td align="center">{{ $loop->iteration }}</td>
                                <td style="font-weight: bold; padding: 8px; font-size: 12px;">
                                    {{ $row['pro_siglas'] ?? 'PNF #'.$proCodigo }}
                                </td>
                                <td align="center">
                                    <input type="hidden" name="pnf_activo[{{ $proCodigo }}]" value="0">
                                    <input type="checkbox"
                                        name="pnf_activo[{{ $proCodigo }}]"
                                        value="1"
                                        {{ $row['activo'] ? 'checked' : '' }}
                                        onchange="togglePnfTrayectos(this, 'pnf_{{ $proCodigo }}')"
                                        style="width:18px;height:18px;cursor:pointer;">
                                </td>
                                <td style="padding: 6px;" id="pnf_{{ $proCodigo }}_trayectos">
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;{{ !$row['activo'] ? 'opacity:0.5;' : '' }}">
                                        @foreach($row['trayectos'] ?? [] as $traCodigo => $traData)
                                            <label style="display:flex;align-items:center;gap:4px;background:#f8f8f8;border:1px solid #ddd;border-radius:5px;padding:4px 8px;cursor:pointer;font-size:11px;">
                                                <input type="checkbox"
                                                    name="tra_selected[{{ $proCodigo }}][{{ $traCodigo }}]"
                                                    value="1"
                                                    {{ $traData['selected'] ?? false ? 'checked' : '' }}
                                                    class="tra-{{ $proCodigo }}"
                                                    onchange="actualizarActivoPnf({{ $proCodigo }})"
                                                    style="cursor:pointer;">
                                                <span>{{ $traData['nombre'] ?? $traCodigo }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td align="center"></td>
                            </tr>
                        @endforeach
                        @if(empty($pnfRows))
                            <tr>
                                <td colspan="5" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                    No hay PNF disponibles.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @else
                <div style="text-align:center;padding:20px;font-weight:bold;color:#999;">
                    No hay PNF disponibles para vincular.
                </div>
            @endif
        </fieldset>

        {{-- Botones --}}
        <div style="text-align: center; margin-top: 20px;">
            <button type="submit" class="cm-btn cm-btn-success" style="margin-right: 10px;"
                onclick="return validarFormulario()">
                Guardar Vinculaci&oacute;n
            </button>
            <a href="{{ route('componentes.index') }}" class="cm-btn cm-btn-danger">
                Cancelar
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function toggleComponenteLabel(checkbox) {
        var label = checkbox.closest('.comp-label');
        if (checkbox.checked) {
            label.style.background = '#e8f5e9';
            label.style.borderColor = '#198754';
        } else {
            label.style.background = '';
            label.style.borderColor = '';
        }
        actualizarContador();
    }

    function actualizarContador() {
        var checked = document.querySelectorAll('.comp-checkbox:checked');
        document.getElementById('selectedCount').textContent = checked.length;
    }

    function seleccionarTodos(seleccionar) {
        var checkboxes = document.querySelectorAll('.comp-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = seleccionar;
            toggleComponenteLabel(cb);
        });
    }

    function togglePnfTrayectos(checkbox, prefix) {
        var trayectosContainer = document.getElementById(prefix + '_trayectos');
        var traCheckboxes = trayectosContainer.querySelectorAll('input[type="checkbox"]');
        traCheckboxes.forEach(function(cb) {
            cb.checked = checkbox.checked;
        });
    }

    function actualizarActivoPnf(proCodigo) {
        var checkboxes = document.querySelectorAll('.tra-' + proCodigo);
        var algunSeleccionado = false;
        checkboxes.forEach(function(cb) {
            if (cb.checked) algunSeleccionado = true;
        });
        var pnfCheckbox = document.querySelector('input[name="pnf_activo[' + proCodigo + ']"]');
        if (pnfCheckbox) {
            pnfCheckbox.checked = algunSeleccionado;
        }
    }

    function validarFormulario() {
        var componentesSeleccionados = document.querySelectorAll('.comp-checkbox:checked');
        if (componentesSeleccionados.length === 0) {
            alert('Debe seleccionar al menos un componente.');
            return false;
        }
        var pnfActivos = document.querySelectorAll('input[name^="pnf_activo["]:checked');
        if (pnfActivos.length === 0) {
            alert('Debe seleccionar al menos un PNF con trayectos.');
            return false;
        }
        return confirm('¿Guardar vinculación para ' + componentesSeleccionados.length + ' componente(s)?');
    }

    // Inicializar contador
    document.addEventListener('DOMContentLoaded', function() {
        actualizarContador();
    });
</script>
@endpush
