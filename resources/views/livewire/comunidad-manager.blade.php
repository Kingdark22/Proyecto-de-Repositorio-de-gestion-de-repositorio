<div>
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
        .cm-btn-danger {
            background: #c82333;
            border-color: #a71d2a;
            color: #fff;
        }
        .cm-btn-secondary {
            background: #f4f4f4;
            border: 1px solid #c2c2c2;
            color: #222;
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
        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Comunidades</h2>

    <p style="font-size: 10px; color: #555; margin-bottom: 12px;">
        Datos en tabla <b>comunidades</b> del repositorio.
        @if ($lapsoVigente)
            Lapso vigente intranet: <b>{{ $lapsoVigente->lap_nombre }}</b>.
        @endif
    </p>

    @if ($viewMode === 'list')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Buscador y listado
            </legend>
            <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="65%">
                        <b>Buscar (nombre / RIF):</b>
                        <input wire:model.live="search" type="text" style="width: 90%; padding: 3px;" placeholder="Nombre o RIF...">
                    </td>
                    <td width="35%" align="right">
                        @if ($puedeGestionar)
                            <button type="button" wire:click="create" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 8px 18px;">
                                Registrar nueva comunidad
                            </button>
                        @endif
                    </td>
                </tr>
            </table>

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="4%">N°</th>
                        <th width="30%">Comunidad / dirección</th>
                        <th width="11%">RIF</th>
                        <th width="16%">Contacto</th>
                        <th width="10%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($comunidades as $c)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};"
                            valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td>
                                <span style="font-weight: bold;">{{ $c->nombre }}</span>
                                <br><span style="font-size: 9px; color: #555;">{{ $c->direccion?->municipio?->estado?->est_nombre ?? '' }} / {{ $c->direccion?->municipio?->mun_nombre ?? '' }} - {{ $c->direccion?->dir_calle ?? '' }}</span>
                            </td>
                            <td align="center">{{ $c->rif }}</td>
                            <td align="center">{{ $c->correo }}<br><b>{{ $c->numero_telefono }}</b></td>
                            <td align="center">
                                @if ($puedeGestionar)
                                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                                        <button type="button" wire:click.prevent="edit({{ $c->id }})"
                                            class="cm-btn cm-btn-secondary cm-btn-sm" wire:loading.attr="disabled"
                                            wire:target="edit">
                                            Editar
                                        </button>
                                        <button type="button" wire:click.prevent="delete({{ $c->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta comunidad?"
                                            class="cm-btn cm-btn-danger cm-btn-sm">
                                            Eliminar
                                        </button>
                                    </div>
                                @else
                                    <span style="color: #888; font-size: 10px;">Solo lectura</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if ($comunidades->isEmpty())
                        <tr>
                            <td colspan="6" align="center" style="padding: 20px;">No hay comunidades registradas.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $comunidades->links() }}</div>
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Modificar comunidad' : 'Registrar comunidad' }}
            </legend>
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="50%" style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Nombre:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <input wire:model.live.debounce.500ms="nombre" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                    <span class="obligatorio" style="color: red; font-weight: bold;">*</span>
                                </div>
                                @if($nombreStatus === 'disponible')
                                    <span style="color: #28a745; font-size: 11px;">✓ Nombre disponible</span>
                                @elseif($nombreStatus === 'no_disponible')
                                    <span style="color: #dc3545; font-size: 11px;">✗ Este nombre ya está en uso</span>
                                @endif
                                @error('nombre')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </td>
                    <td width="50%" style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 40px;">RIF:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <select wire:model.live="rifLetra" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 11px; width: 48px;">
                                        <option value="V">V</option>
                                        <option value="E">E</option>
                                        <option value="J">J</option>
                                        <option value="G">G</option>
                                        <option value="P">P</option>
                                    </select>
                                    <input wire:model.live.debounce.500ms="rifNumero" type="text" maxlength="9" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Número (máx. 9 dígitos)">
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                @if($rifStatus === 'valido')
                                    <span style="color: #28a745; font-size: 11px;">✓ RIF válido</span>
                                @elseif($rifStatus === 'invalido')
                                    <span style="color: #dc3545; font-size: 11px;">✗ RIF inválido</span>
                                @endif
                                @error('rifNumero')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding: 0 4px 10px 0;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Correo:</b>
                            <div style="flex: 1;">
                                <input wire:model="correo" type="email" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                @error('correo')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Teléfono:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <select wire:model="prefijo_telefono" style="padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        <option value="0424">0424</option>
                                        <option value="0414">0414</option>
                                        <option value="0412">0412</option>
                                        <option value="0422">0422</option>
                                        <option value="0416">0416</option>
                                        <option value="0426">0426</option>
                                    </select>
                                    <input wire:model="numero_telefono" type="text" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="XXX-XXXX">
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                @error('prefijo_telefono')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
                                @error('numero_telefono')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
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
                                    <select wire:model.live="estado_id" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        <option value="">-- Seleccione estado --</option>
                                        @foreach ($estados as $e)
                                            <option value="{{ $e->est_codigo }}">{{ $e->est_nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="obligatorio" style="color: red; font-weight: bold;">*</span>
                                </div>
                                @error('estado_id')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 60px;">Municipio:</b>
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <select wire:model="municipio_id" style="width: 100%; padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; background: #fff; font-size: 11px;">
                                        <option value="">-- Seleccione municipio --</option>
                                        @foreach ($municipios as $m)
                                            <option value="{{ $m->mun_codigo }}">{{ $m->mun_nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="obligatorio" style="color: red; font-weight: bold;">*</span>
                                </div>
                                @error('municipio_id')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
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
                                    <input wire:model="dir_nombre" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
                                        placeholder="Av./Calle/Casa Nro., sector, referencia...">
                                    <span class="obligatorio" style="color: red; font-weight: bold; margin-top: 5px;">*</span>
                                </div>
                                @error('dir_nombre')
                                    <span class="validation-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </td>
                </tr>
             </table>
 
             <div style="margin-top: 15px; text-align: center;">
                 <button type="button" wire:click="cancel" class="cm-btn cm-btn-danger"
                     style="margin-right: 10px;">Cancelar</button>
                 <button type="button" wire:click="save" class="cm-btn cm-btn-primary">Guardar</button>
             </div>
         </fieldset>
     @endif
 </div>
