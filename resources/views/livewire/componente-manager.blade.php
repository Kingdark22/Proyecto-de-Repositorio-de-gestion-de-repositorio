<div>
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
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
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
            padding: 0.35rem 0.7rem;
            min-width: auto;
            font-size: 0.85rem;
        }

        .cm-btn-group button {
            margin-right: 0.35rem;
            margin-bottom: 0.25rem;
        }
    </style>

    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gesti&oacute;n de Componentes</h2>

    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if ($viewMode === 'list')
        <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 12px;">
            <select wire:model.live="filterPrograma" style="padding: 4px; min-width: 200px;">
                <option value="">Todos los programas</option>
                @foreach ($programas as $p)
                    <option value="{{ $p->id }}">{{ $p->siglas }} - {{ $p->nombre }}</option>
                @endforeach
            </select>
            <b>Buscar:</b>
            <input wire:model.live.debounce.300ms="search" type="text" style="width: 350px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;"
                placeholder="Componente...">
            <span style="margin-left: auto;"></span>
            <button wire:click="create" class="cm-btn cm-btn-success" style="font-size: 13px; padding: 6px 14px;">
                Adicionar Componente Nuevo
            </button>
        </div>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Sistema de Componentes
                de Proyecto</legend>
            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N&deg;</th>
                        <th width="35%">Nombre del Documento Exigido</th>
                        <th width="30%">Coordinaci&oacute;n Asociada</th>
                        <th width="10%">Obligatorio</th>
                        <th width="10%">Estatus</th>
                        <th width="10%">Configurar</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($listaRegistros as $item)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->estado_logico ? 'color: #888;' : 'color: #000;' }}"
                            valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td align="center" style="font-weight: bold; padding: 8px;">
                                {{ $item->nombre }}</td>
                            <td align="center" style="font-weight: bold; font-style: italic; padding: 8px;">
                                {{ $item->nombre_programa }}
                            </td>
                            <td align="center">
                                {!! $item->es_obligatorio
                                    ? '<span style="color: #FF0000; font-weight:bold;">S&Iacute;</span>'
                                    : '<span style="color: #008000; font-weight:bold;">NO</span>' !!}
                            </td>
                            <td align="center">
                                @if ($item->estado_logico)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Suspendido</span>
                                @endif
                            </td>
                            <td align="center">
                                <div class="cm-btn-group"
                                    style="display: inline-flex; flex-wrap: wrap; justify-content: center;">
                                    <button type="button" wire:click.prevent="edit({{ $item->id }})"
                                        title="Editar Regla" class="cm-btn cm-btn-secondary cm-btn-sm">
                                        Editar
                                    </button>
                                    <button type="button" wire:click.prevent="toggleStatus({{ $item->id }})"
                                        title="{{ $item->estado_logico ? 'Suspender Regla' : 'Publicar Regla' }}"
                                        class="cm-btn cm-btn-warning cm-btn-sm">
                                        {{ $item->estado_logico ? 'Suspender' : 'Publicar' }}
                                    </button>
                                    <button type="button" wire:click.prevent="delete({{ $item->id }})"
                                        wire:confirm="&iquest;Seguro desea eliminar esta regla? Desaparecer&aacute;n solicitudes antiguas para este documento."
                                        title="Eliminar Base" class="cm-btn cm-btn-danger cm-btn-sm">
                                        Borrar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($listaRegistros->isEmpty())
                        <tr>
                            <td colspan="6" align="center"
                                style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay componentes configurados en la Base de Datos.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $listaRegistros->links() }}</div>
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Editar Directriz de Componente' : 'Registrar Exigencias de Proyecto' }}
            </legend>
            @error('rows')
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
                    {{ $message }}
                </div>
            @enderror
            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    <tr>
                        <td width="30%"><b>Programa Titular:</b></td>
                        <td width="70%">
                            @if (auth()->user()->hasRole('administrador'))
                                <select wire:model.live="programa_id" style="width: 80%; padding: 4px;">
                                    <option value="">Seleccione a qui&eacute;n pertenece esta regla...</option>
                                    @foreach ($programas as $p)
                                        <option value="{{ $p->id }}">{{ $p->siglas }} -
                                            {{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div
                                    style="padding: 4px 8px; background-color: #f5f5f5; border: 1px solid #ddd; width: 80%; font-weight:bold; color: #555;">
                                    {{ collect($programas)->firstWhere('id', $programa_id)?->nombre ?? '[COORDINACI&Oacute;N AUTOASIGNADA]' }}
                                </div>
                            @endif
                            @error('programa_id')
                                <br><span style="color:red; font-size:10px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;">
                    <table width="100%" border="1" cellpadding="5" cellspacing="0"
                        style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px;">
                        <thead style="background-color: #f0f0f0;">
                            <tr>
                                <th>Nombre del Componente</th>
                                <th width="15%">Obligatorio</th>
                                <th width="10%">Acci&oacute;n</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $index => $row)
                                <tr>
                                    <td>
                                        <input type="text" wire:model="rows.{{ $index }}.nombre"
                                            style="width: 95%; padding: 4px;"
                                            placeholder="Ej: Trabajo Escrito...">
                                        @error("rows.$index.nombre")
                                            <br><span style="color:red; font-size:10px;">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td align="center">
                                        <input type="checkbox"
                                            wire:model="rows.{{ $index }}.es_obligatorio">
                                    </td>
                                    <td align="center">
                                        @if (empty($row['id']))
                                            @if (!$editingId || count($rows) > 1)
                                                <button type="button" wire:click="removeRow({{ $index }})"
                                                    class="cm-btn cm-btn-danger cm-btn-sm">
                                                    Quitar
                                                </button>
                                            @endif
                                        @else
                                            <span style="font-size: 10px; color: #666; font-weight: bold;">(Original)</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="margin-top: 10px;">
                        <button type="button" wire:click="addRow()" class="cm-btn cm-btn-success cm-btn-sm">
                            Agregar otro componente
                        </button>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="cm-btn cm-btn-success" style="margin-right: 10px;">
                        @if ($editingId)
                            Guardar cambios
                        @else
                            Registrar Componentes
                        @endif
                    </button>
                    <button type="button" wire:click="cancel" class="cm-btn cm-btn-danger">
                        Cancelar
                    </button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
