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
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Líneas de
        Investigación</h2>

    @if ($viewMode === 'list')
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Buscar Línea:</b>
                <input wire:model.live.debounce.300ms="search" type="text" style="width: 400px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Nombre de la línea...">
            </div>
            <button wire:click="create" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 6px 16px;">
                Registrar Línea
            </button>
        </div>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Líneas de
                Investigación</legend>

            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5">Línea de Investigación</th>
                        <th padding="5">Área / Coordinación</th>
                        <th padding="5" width="80">Estado</th>
                        <th padding="5" width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($items as $item)
                        <tr
                            style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->activo ? 'color: #888;' : 'color: #000;' }}">
                            <td align="center" style="font-weight: bold;">
                                {{ $item->nombre_investigacion }}
                                <br>
                                <span
                                    style="font-size: 10px; font-weight: normal;">{{ Str::limit($item->descripcion, 50) ?: 'Sin descripción' }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $item->area_de_investigacion }}
                                <br>
                                <span style="font-size: 10px; font-weight: bold;">Programa:
                                    {{ $item->nombre_programa }}</span>
                            </td>
                            <td align="center">
                                @if ($item->activo)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                @endif
                            </td>
                            <td align="center">
                                <div
                                    style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" wire:click.prevent="edit({{ $item->id }})" title="Editar"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" wire:click.prevent="toggleStatus({{ $item->id }})"
                                        title="{{ $item->activo ? 'Deshabilitar' : 'Habilitar' }}"
                                        class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->activo ? 'Deshabilitar' : 'Habilitar' }}</button>
                                    <button type="button" wire:click.prevent="delete({{ $item->id }})"
                                        wire:confirm="¿Estás seguro de eliminar PERMANENTEMENTE esta línea de investigación?"
                                        title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($items->isEmpty())
                        <tr>
                            <td colspan="4" align="center"
                                style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No se encontraron resultados
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div style="margin-top: 10px;">
                {{ $items->links() }}
            </div>
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Editar Línea' : 'Registrar Línea' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                        <td width="30%"><b>Nombre Línea de Inv.:</b></td>
                        <td width="70%">
                            <input wire:model.live.debounce.500ms="nombre_investigacion" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @if($nombreStatus === 'disponible')
                                <br><span style="color: #28a745; font-size: 11px;">✓ Nombre disponible</span>
                            @elseif($nombreStatus === 'no_disponible')
                                <br><span style="color: #dc3545; font-size: 11px;">✗ Este nombre ya está en uso</span>
                            @endif
                            @error('nombre_investigacion')
                                <br><span class="validation-error">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="30%"><b>Área Académica:</b></td>
                        <td width="70%">
                            <input wire:model="area_de_investigacion" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @error('area_de_investigacion')
                                <br><span class="validation-error">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="30%"><b>Seleccionar Programa:</b></td>
                        <td width="70%">
                            <select wire:model="programa_id" style="width: 90%; padding: 2px;">
                                <option value="">Seleccione un Programa...</option>
                                @foreach ($programas as $p)
                                    <option value="{{ $p->id }}">{{ $p->siglas }} - {{ $p->nombre }}
                                    </option>
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
                            <textarea wire:model="descripcion" rows="3" style="width: 90%;"></textarea>
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
                    <button type="button" wire:click="cancel" class="cm-btn cm-btn-danger"
                        style="margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="cm-btn cm-btn-primary">Guardar</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
