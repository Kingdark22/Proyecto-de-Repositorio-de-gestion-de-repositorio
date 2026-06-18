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
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gesti&oacute;n de Tipos de Publicaci&oacute;n
    </h2>

    @if ($viewMode === 'list')
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; gap: 20px;">
            <div>
                <b>Buscar Tipo:</b>
                <input wire:model.live.debounce.300ms="search" type="text" style="width: 500px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Nombre del tipo...">
            </div>

            <button wire:click="create" class="cm-btn cm-btn-success cm-btn-sm" style="margin-right: 30px;">
                Registrar Tipo
            </button>
        </div>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Tipos de
                Publicaci&oacute;n</legend>

            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="40%">Tipo de Publicaci&oacute;n</th>
                        <th padding="5" width="20%">Menci&oacute;n Honor&iacute;fica</th>
                        <th padding="5" width="20%">Estado</th>
                        <th padding="5" width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($items as $item)
                        <tr
                            style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->estado_logico ? 'color: #888;' : 'color: #000;' }}">
                            <td align="center" style="font-weight: bold; padding: 5px;">
                                {{ $item->nombre }}
                            </td>
                            <td align="center">
                                @if ($item->mencion_honorifica)
                                    <span style="font-weight: bold; color: #d4a017;">S&iacute;</span>
                                @else
                                    <span style="font-style: italic; color: #888;">No aplica</span>
                                @endif
                            </td>
                            <td align="center">
                                @if ($item->estado_logico)
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
                                        title="{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}"
                                        class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}</button>
                                    <button type="button" wire:click.prevent="delete({{ $item->id }})"
                                        wire:confirm="¿Estás seguro de eliminar PERMANENTEMENTE este tipo de publicación?"
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
                {{ $editingId ? 'Editar Tipo' : 'Registrar Tipo' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                        <td width="35%"><b>Nombre del Tipo:</b></td>
                        <td width="65%">
                            <input wire:model="nombre" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @error('nombre')
                                <br><span class="validation-error">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="35%"><b>Mención Honorífica:</b></td>
                        <td width="65%">
                            <label style="display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" wire:model="mencion_honorifica">
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
                    <button type="button" wire:click="cancel" class="cm-btn cm-btn-danger"
                        style="margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="cm-btn cm-btn-primary">Guardar</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
