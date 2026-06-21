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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode === 'list'): ?>
        <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 12px;">
            <b>Buscar:</b>
            <input wire:model.live.debounce.300ms="search" type="text" style="width: 350px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;"
                placeholder="Componente...">
            <span style="margin-left: auto;"></span>
            <button wire:click="irAVinculacion" class="cm-btn cm-btn-primary" style="font-size: 13px; padding: 6px 14px;">
                Vinculaci&oacute;n PNF &rarr; Componentes
            </button>
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
                        <th width="22%">Nombre del Componente</th>
                        <th width="25%">Asignaciones (PNF &rarr; Trayecto)</th>
                        <th width="12%">Tipo Archivo</th>
                        <th width="8%">Tama&ntilde;o</th>
                        <th width="8%">Oblig.</th>
                        <th width="8%">Estatus</th>
                        <th width="12%">Configurar</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $listaRegistros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php
                            $asignaciones = $item->programas;
                        ?>
                        <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>; <?php echo e(!$item->estado_logico ? 'color: #888;' : 'color: #000;'); ?>"
                            valign="top">
                            <td align="center"><?php echo e($loop->iteration); ?></td>
                            <td align="center" style="font-weight: bold; padding: 8px;">
                                <?php echo e($item->nombre); ?></td>
                            <td style="padding: 6px; font-size: 10px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asignaciones->isNotEmpty()): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $asignaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <span style="display:inline-block; background:#e8f0fe; border:1px solid #b3d4fc; border-radius:3px; padding:1px 5px; margin:1px; white-space:nowrap;">
                                            <b>PNF #<?php echo e($asig->pro_codigo); ?></b>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asig->tra_codigo): ?> &rarr; T.<?php echo e($asig->tra_codigo); ?><?php else: ?> <i>(todos)</i><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#999; font-style:italic;">Global</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center" style="padding: 8px;">
                                <span style="font-weight:bold;text-transform:uppercase;"><?php echo e($item->tipo_archivo ?? 'pdf'); ?></span>
                            </td>
                            <td align="center" style="padding: 8px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->tamano_maximo_mb): ?>
                                    <span style="font-weight:bold;"><?php echo e($item->tamano_maximo_mb); ?> MB</span>
                                <?php else: ?>
                                    <span style="color:#999;">10 MB</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center">
                                <?php echo $item->es_obligatorio
                                    ? '<span style="color: #FF0000; font-weight:bold;">S&Iacute;</span>'
                                    : '<span style="color: #008000; font-weight:bold;">NO</span>'; ?>

                            </td>
                            <td align="center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->estado_logico): ?>
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                <?php else: ?>
                                    <span style="color: #FF0000; font-weight: bold;">Suspendido</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center">
                                <div class="cm-btn-group"
                                    style="display: inline-flex; flex-wrap: wrap; justify-content: center;">
                                    <button type="button" wire:click.prevent="edit(<?php echo e($item->id); ?>)"
                                        title="Editar" class="cm-btn cm-btn-secondary cm-btn-sm">
                                        Editar
                                    </button>
                                    <button type="button" wire:click.prevent="toggleStatus(<?php echo e($item->id); ?>)"
                                        title="<?php echo e($item->estado_logico ? 'Suspender' : 'Publicar'); ?>"
                                        class="cm-btn cm-btn-warning cm-btn-sm">
                                        <?php echo e($item->estado_logico ? 'Suspender' : 'Publicar'); ?>

                                    </button>
                                    <button type="button" wire:click.prevent="delete(<?php echo e($item->id); ?>)"
                                        wire:confirm="&iquest;Seguro desea eliminar este componente?"
                                        title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm">
                                        Borrar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listaRegistros->isEmpty()): ?>
                        <tr>
                            <td colspan="8" align="center"
                                style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay componentes configurados en la Base de Datos.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 10px;"><?php echo e($listaRegistros->links()); ?></div>
        </fieldset>
    <?php elseif($viewMode === 'vinculacion'): ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                Vinculaci&oacute;n PNF &rarr; Componentes
            </legend>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; margin-right: 10px;">Seleccione el PNF:</label>
                <select wire:model.live="selectedProgramaId" style="width: 400px; padding: 6px; font-size: 13px;">
                    <option value="">- Seleccione un PNF -</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $programasDisponibles ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <option value="<?php echo e($prog->pro_codigo); ?>"><?php echo e($prog->pro_siglas ?? $prog->pro_nombre); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProgramaId !== '' && !empty($vinculacionRows)): ?>
                <div style="font-size:12px;color:#666;margin-bottom:10px;">
                    Marque los componentes que aplican al PNF seleccionado. Cada componente puede asignarse a un trayecto espec&iacute;fico (o dejarlo vac&iacute;o para todos los trayectos) con una cantidad.
                </div>

                <table width="100%" border="1" cellpadding="6" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                            <th width="5%">N&deg;</th>
                            <th width="25%">Componente</th>
                            <th width="10%">Asignado</th>
                            <th width="25%">Trayecto</th>
                            <th width="10%">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $vinculacionRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $compCodigo => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;"
                                valign="middle">
                                <td align="center"><?php echo e($loop->iteration); ?></td>
                                <td style="font-weight: bold; padding: 8px;"><?php echo e($row['nombre']); ?></td>
                                <td align="center">
                                    <button type="button" wire:click="toggleAsignacionVinculacion(<?php echo e($compCodigo); ?>)"
                                        style="background:none;border:1px solid #ccc;border-radius:4px;padding:4px 10px;cursor:pointer;font-size:11px;<?php echo e($row['asignado'] ? 'background:#198754;color:#fff;border-color:#166f43;' : ''); ?>">
                                        <?php echo e($row['asignado'] ? 'S&Iacute;' : 'NO'); ?>

                                    </button>
                                </td>
                                <td align="center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['asignado']): ?>
                                        <select wire:change="cambiarTrayectoVinculacion(<?php echo e($compCodigo); ?>, $event.target.value)" style="width:90%;padding:4px;font-size:11px;">
                                            <option value="">- Todos los trayectos -</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trayectosVinculacion ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                <option value="<?php echo e($tra->tra_codigo); ?>" <?php echo e($row['tra_codigo'] == $tra->tra_codigo ? 'selected' : ''); ?>>
                                                    <?php echo e($tra->tra_nombre); ?>

                                                </option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    <?php else: ?>
                                        <span style="color:#999;font-style:italic;">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td align="center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['asignado']): ?>
                                        <input type="number" min="1" max="200"
                                            wire:change="cambiarCantidadVinculacion(<?php echo e($compCodigo); ?>, $event.target.value)"
                                            value="<?php echo e($row['cantidad']); ?>"
                                            style="width:60px;padding:4px;text-align:center;font-size:11px;">
                                    <?php else: ?>
                                        <span style="color:#999;font-style:italic;">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="guardarVinculacion" class="cm-btn cm-btn-success" style="margin-right: 10px;">
                        Guardar Vinculaci&oacute;n
                    </button>
                    <button type="button" wire:click="cancelarVinculacion" class="cm-btn cm-btn-danger">
                        Cancelar
                    </button>
                </div>
            <?php elseif($selectedProgramaId !== '' && empty($vinculacionRows)): ?>
                <div style="text-align:center;padding:20px;font-weight:bold;color:#999;">
                    No hay componentes activos disponibles.
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </fieldset>
    <?php elseif($viewMode === 'form'): ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                <?php echo e($editingId ? 'Editar Componente' : 'Nuevo Componente'); ?>

            </legend>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rows'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
                    <?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <form wire:submit="save" style="margin: 0;">
                <div style="margin-top: 10px;">
                    <div style="font-size:12px;color:#666;margin-bottom:10px;">
                        Las asignaciones a PNF + Trayecto se gestionan desde <b>Vinculaci&oacute;n PNF &rarr; Componentes</b>.
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <fieldset style="border: 1px solid #ddd; border-radius: 6px; padding: 12px; margin-bottom: 15px; background: #fafafa;">
                        <legend style="font-weight: bold; font-size: 13px; padding: 0 8px; color: #333;">
                            Componente #<?php echo e($index + 1); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row['id'])): ?><span style="color:#999;font-size:10px;"> (ID: <?php echo e($row['id']); ?>)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </legend>

                        <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                            <tr>
                                <td width="35%"><b>Nombre:</b></td>
                                <td width="65%">
                                    <input type="text" wire:model="rows.<?php echo e($index); ?>.nombre"
                                        style="width: 95%; padding: 4px;"
                                        placeholder="Ej: Informe Final...">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["rows.$index.nombre"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <br><span style="color:red; font-size:10px;"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Tipo de archivo:</b></td>
                                <td>
                                    <select wire:model="rows.<?php echo e($index); ?>.tipo_archivo" style="width: 60%; padding: 4px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Componente::tiposArchivo(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <option value="<?php echo e($val); ?>"><?php echo e($label); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["rows.$index.tipo_archivo"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <br><span style="color:red; font-size:10px;"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Tama&ntilde;o m&aacute;x. (MB):</b></td>
                                <td>
                                    <input type="number" wire:model="rows.<?php echo e($index); ?>.tamano_maximo_mb"
                                        style="width: 80px; padding: 4px;" min="1" max="200">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["rows.$index.tamano_maximo_mb"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <br><span style="color:red; font-size:10px;"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Obligatorio:</b></td>
                                <td>
                                    <input type="checkbox" wire:model="rows.<?php echo e($index); ?>.es_obligatorio" style="width:18px;height:18px;cursor:pointer;">
                                </td>
                            </tr>
                        </table>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($row['id']) && (!$editingId || count($rows) > 1)): ?>
                            <div style="margin-top: 8px; text-align: right;">
                                <button type="button" wire:click="removeRow(<?php echo e($index); ?>)"
                                    class="cm-btn cm-btn-danger cm-btn-sm"
                                    style="padding:2px 10px;font-size:10px;min-width:auto;">
                                    Quitar este componente
                                </button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </fieldset>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <div style="margin-top: 10px;">
                        <button type="button" wire:click="addRow()" class="cm-btn cm-btn-success cm-btn-sm">
                            Agregar otro componente
                        </button>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="cm-btn cm-btn-success" style="margin-right: 10px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingId): ?>
                            Guardar cambios
                        <?php else: ?>
                            Registrar Componentes
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <button type="button" wire:click="cancel" class="cm-btn cm-btn-danger">
                        Cancelar
                    </button>
                </div>
            </form>
        </fieldset>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/componente-manager.blade.php ENDPATH**/ ?>