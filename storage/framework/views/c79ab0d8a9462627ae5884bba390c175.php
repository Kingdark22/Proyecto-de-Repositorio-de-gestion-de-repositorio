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
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Tipos de
        Investigación</h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode === 'list'): ?>
        <div style="margin-bottom: 15px; display: flex; align-items: center;">
            <div>
                <b>Buscar Tipo:</b>
                <input wire:model.live.debounce.300ms="search" type="text" style="width: 400px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Nombre del tipo...">
            </div>

            <button wire:click="create" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 6px 16px; margin-left: auto; margin-right: 30px;">
                Registrar Tipo
            </button>
        </div>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Tipos de
                Investigación</legend>

            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="25%">Tipo de Investigación</th>
                        <th padding="5" width="45%">Descripción</th>
                        <th padding="5" width="10%">Estado</th>
                        <th padding="5" width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr
                            style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>; <?php echo e(!$item->estado_logico ? 'color: #888;' : 'color: #000;'); ?>">
                            <td align="center" style="font-weight: bold; padding: 5px;">
                                <?php echo e($item->nombre); ?>

                            </td>
                            <td align="left" style="padding: 5px; font-size: 11px;">
                                <?php echo e($item->descripcion ?: 'Sin descripción'); ?>

                            </td>
                            <td align="center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->estado_logico): ?>
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                <?php else: ?>
                                    <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td align="center">
                                <div
                                    style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" wire:click.prevent="edit(<?php echo e($item->id); ?>)" title="Editar"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" wire:click.prevent="toggleStatus(<?php echo e($item->id); ?>)"
                                        title="<?php echo e($item->estado_logico ? 'Deshabilitar' : 'Habilitar'); ?>"
                                        class="cm-btn cm-btn-warning cm-btn-sm"><?php echo e($item->estado_logico ? 'Deshabilitar' : 'Habilitar'); ?></button>
                                    <button type="button" wire:click.prevent="delete(<?php echo e($item->id); ?>)"
                                        wire:confirm="¿Estás seguro de eliminar PERMANENTEMENTE este tipo de investigación?"
                                        title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items->isEmpty()): ?>
                        <tr>
                            <td colspan="4" align="center"
                                style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No se encontraron resultados
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 10px;">
                <?php echo e($items->links()); ?>

            </div>
        </fieldset>
    <?php else: ?>
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                <?php echo e($editingId ? 'Editar Tipo' : 'Registrar Tipo'); ?>

            </legend>

            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                        <td width="30%"><b>Nombre del Tipo:</b></td>
                        <td width="70%">
                            <input wire:model.live.debounce.500ms="nombre" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nombreStatus === 'disponible'): ?>
                                <br><span style="color: #28a745; font-size: 11px;">✓ Nombre disponible</span>
                            <?php elseif($nombreStatus === 'no_disponible'): ?>
                                <br><span style="color: #dc3545; font-size: 11px;">✗ Este nombre ya está en uso</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <br><span class="validation-error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" valign="top"><b>Descripción Breve:</b></td>
                        <td width="70%">
                            <textarea wire:model="descripcion" rows="4" style="width: 90%;"></textarea>
                            <span class="obligatorio">*</span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <br><span class="validation-error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/tipo-investigacion-manager.blade.php ENDPATH**/ ?>