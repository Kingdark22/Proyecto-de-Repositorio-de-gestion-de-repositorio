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
        Datos en tabla <b>comunidades</b> del repositorio. Las personas de contacto se registran en la sección correspondiente.
        @if ($lapsoVigente)
            Lapso vigente intranet: <b>{{ $lapsoVigente->lap_nombre }}</b>.
        @endif
    </p>

    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px;">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('message_error'))
        <div
            style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px;">
            {{ session('message_error') }}
        </div>
    @endif

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
                        <th width="15%">Contacto</th>
                        <th width="16%">Personas contacto</th>
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
                            <td style="font-size:10px;">
                                @if ($c->relationLoaded('contactos') && $c->contactos->isNotEmpty())
                                    @foreach ($c->contactos as $ct)
                                        {{ trim($ct->ccon_nombre . ' ' . $ct->ccon_apellido) }}<br>
                                        @if ($ct->ccon_cargo) <i>{{ config('comunidades.cargos_contacto.' . $ct->ccon_cargo, $ct->ccon_cargo) }}</i><br>@endif
                                    @endforeach
                                @else
                                    <span style="color:#999;">-</span>
                                @endif
                            </td>
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
                                    <input wire:model="nombre" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                    <span class="obligatorio" style="color: red; font-weight: bold;">*</span>
                                </div>
                                @error('nombre')
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </td>
                    <td width="50%" style="vertical-align: top; padding: 0 0 10px 4px;">
                        <div style="display: flex; align-items: flex-start; gap: 6px;">
                            <b style="white-space: nowrap; padding-top: 8px; min-width: 40px;">RIF:</b>
                            <div style="flex: 1;">
                                <input wire:model="rif" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                @error('rif')
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
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
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
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
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
                                @enderror
                                @error('numero_telefono')
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
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
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
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
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
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
                                    <span style="color:red;font-size:10px; display: block; margin-top: 2px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 25px; border-top: 2px solid #8b0000; padding-top: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <h4 style="margin: 0; font-size: 14px; font-weight: bold; color: #8b0000; font-style: italic;">Personas de Contacto</h4>
                    <button type="button" wire:click="agregarContacto" class="cm-btn cm-btn-primary cm-btn-sm">
                        + Agregar contacto
                    </button>
                </div>
                @if (empty($contactos))
                    <div style="background: #fdfdfd; border: 1px dashed #bbbbbb; border-radius: 6px; padding: 20px; text-align: center; color: #555; font-size: 11px; font-style: italic;">
                        No hay personas de contacto registradas para esta comunidad.
                    </div>
                @else
                    @foreach ($contactos as $i => $contacto)
                        <div style="background: #fff; border: 1px solid #bbbbbb; border-radius: 6px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background-color: #8bb2b7; color: #000; border-bottom: 1px solid #bbbbbb;">
                                <span style="font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 5px;">
                                    Contacto #{{ $loop->iteration }}
                                </span>
                                <button type="button" wire:click="quitarContacto({{ $i }})" wire:confirm="¿Desea eliminar este contacto?" class="cm-btn cm-btn-danger cm-btn-sm" style="padding: 2px 8px; font-size: 10px;">
                                    Quitar
                                </button>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; padding: 15px; font-size: 11px; background: #fff;">
                                <div>
                                    <label style="display: block; font-weight: bold; margin-bottom: 4px; color: #000;">Nombre:</label>
                                    <input wire:model="contactos.{{ $i }}.nombre" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 11px; box-sizing: border-box;">
                                    @error('contactos.' . $i . '.nombre')
                                        <span style="color:red; font-size:10px; display: block; margin-top: 3px;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label style="display: block; font-weight: bold; margin-bottom: 4px; color: #000;">Apellido:</label>
                                    <input wire:model="contactos.{{ $i }}.apellido" type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 11px; box-sizing: border-box;">
                                    @error('contactos.' . $i . '.apellido')
                                        <span style="color:red; font-size:10px; display: block; margin-top: 3px;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label style="display: block; font-weight: bold; margin-bottom: 4px; color: #000;">Cargo:</label>
                                    @if ($contactos[$i]['mostrar_input_cargo'] ?? false)
                                        <div style="display: flex; gap: 4px; align-items: center;">
                                            <input wire:model="contactos.{{ $i }}.cargo_custom" type="text" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 11px; box-sizing: border-box;" placeholder="Escriba el cargo...">
                                            <button type="button" wire:click="aceptarCargoPersonalizado({{ $i }})" style="padding: 6px 10px; border: none; border-radius: 4px; background: #19692e; color: #fff; cursor: pointer; font-size: 11px; white-space: nowrap;">✓</button>
                                            <button type="button" wire:click="cancelarCargoPersonalizado({{ $i }})" style="padding: 6px 10px; border: 1px solid #aaa; border-radius: 4px; background: #f4f4f4; cursor: pointer; font-size: 11px; white-space: nowrap;">✗</button>
                                        </div>
                                    @else
                                        <div style="display: flex; gap: 4px; align-items: center;">
                                            <select wire:model="contactos.{{ $i }}.cargo" style="flex: 1; padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 11px; background: #fff; box-sizing: border-box;">
                                                <option value="">-- Seleccione cargo --</option>
                                                @foreach (config('comunidades.cargos_contacto', []) as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" wire:click="mostrarCargoPersonalizado({{ $i }})" title="Añadir otro cargo" style="padding: 6px 10px; border: 1px solid #aaa; border-radius: 4px; background: #fff; cursor: pointer; font-size: 14px; line-height: 1; white-space: nowrap;">+</button>
                                        </div>
                                    @endif
                                    @error('contactos.' . $i . '.cargo')
                                        <span style="color:red; font-size:10px; display: block; margin-top: 3px;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label style="display: block; font-weight: bold; margin-bottom: 4px; color: #000;">Correo Electrónico:</label>
                                    <input wire:model.live="contactos.{{ $i }}.correo" type="email" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 11px; box-sizing: border-box;">
                                    <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                    @error('contactos.' . $i . '.correo')
                                        <span style="color:red; font-size:10px; display: block; margin-top: 3px;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label style="display: block; font-weight: bold; margin-bottom: 4px; color: #000;">Confirmar Correo Electrónico:</label>
                                    <input wire:model.live="contactos.{{ $i }}.correo_confirmacion" type="email" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 11px; box-sizing: border-box;">
                                    @error('contactos.' . $i . '.correo_confirmacion')
                                        <span style="color:red; font-size:10px; display: block; margin-top: 3px;">{{ $message }}</span>
                                    @enderror
                                    @php
                                        $ce = trim($contactos[$i]['correo'] ?? '');
                                        $cf = trim($contactos[$i]['correo_confirmacion'] ?? '');
                                    @endphp
                                    @if ($ce !== '' && $cf !== '')
                                        @if ($ce === $cf)
                                            <span style="color:green; font-size:10px; display: block; margin-top: 2px;">✓ Coinciden</span>
                                        @else
                                            <span style="color:red; font-size:10px; display: block; margin-top: 2px;">✗ No coinciden</span>
                                        @endif
                                    @endif
                                </div>

                                <div>
                                    <label style="display: block; font-weight: bold; margin-bottom: 4px; color: #000;">Número de Teléfono:</label>
                                    <div style="display: flex; gap: 5px; align-items: center;">
                                        <select wire:model="contactos.{{ $i }}.prefijo" style="padding: 6px 6px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: #fff; font-size: 11px; min-width: 65px;">
                                            <option value="0424">0424</option>
                                            <option value="0414">0414</option>
                                            <option value="0412">0412</option>
                                            <option value="0422">0422</option>
                                            <option value="0416">0416</option>
                                            <option value="0426">0426</option>
                                        </select>
                                        <input wire:model="contactos.{{ $i }}.telefono" type="text" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 11px; box-sizing: border-box;" placeholder="123-4567" maxlength="7">
                                    </div>
                                    <div style="font-size:10px; color:#888; margin-top:2px;">(opcional)</div>
                                    @error('contactos.' . $i . '.telefono')
                                        <span style="color:red; font-size:10px; display: block; margin-top: 3px;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div style="padding: 8px 15px 12px; text-align: right; border-top: 1px solid #eee;">
                                <button type="button" wire:click="guardarContactosAhora" class="cm-btn cm-btn-primary cm-btn-sm" style="background: #5a7d8a; border-color: #4a6a77; padding: 4px 14px; font-size: 11px;">
                                    Guardar
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div style="margin-top: 15px; text-align: center;">
                <button type="button" wire:click="cancel" class="cm-btn cm-btn-danger"
                    style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="save" class="cm-btn cm-btn-primary">Guardar</button>
            </div>
        </fieldset>
    @endif
</div>
