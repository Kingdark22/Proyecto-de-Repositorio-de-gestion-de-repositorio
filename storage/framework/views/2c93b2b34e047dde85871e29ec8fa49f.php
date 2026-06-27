<?php $__env->startSection('title', 'Tipos de Investigación'); ?>
<?php $__env->startSection('header', 'Gestión de Tipos de Investigación'); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div style="margin-bottom: 15px; display: flex; align-items: center;">
        <form method="GET" action="<?php echo e(route('tipos-investigacion')); ?>" style="display: contents;">
            <div>
                <b>Buscar Tipo:</b>
                <input name="search" type="text" value="<?php echo e($search); ?>" style="width: 400px; padding: 4px 6px; border-radius: 4px; border: 1px solid #999;" placeholder="Nombre del tipo...">
                <button type="submit" class="cm-btn cm-btn-sm">Buscar</button>
            </div>
        </form>

        <button type="button" onclick="window.location='<?php echo e(route('tipos-investigacion.create')); ?>'" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 6px 16px; margin-left: auto; margin-right: 30px;">
            Registrar Tipo
        </button>
    </div>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Tipos de
            Investigaci&oacute;n</legend>

        <table width="100%" border="1" cellpadding="4" cellspacing="0"
            style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th style="padding: 5px;" width="25%">Tipo de Investigaci&oacute;n</th>
                    <th style="padding: 5px;" width="45%">Descripci&oacute;n</th>
                    <th style="padding: 5px;" width="10%">Estado</th>
                    <th style="padding: 5px;" width="20%">Acciones</th>
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
                            <?php echo e($item->descripcion ?: 'Sin descripci&oacute;n'); ?>

                        </td>
                        <td align="center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->estado_logico): ?>
                                <span style="color: #008000; font-weight: bold;">Activo</span>
                            <?php else: ?>
                                <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td align="center">
                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                <button type="button" onclick="window.location='<?php echo e(route('tipos-investigacion.edit', $item->id)); ?>'" title="Editar"
                                    class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                <button type="button" onclick="if(confirm('¿Cambiar estado de este tipo?'))window.location='<?php echo e(route('tipos-investigacion.toggle', $item->id)); ?>'" title="<?php echo e($item->estado_logico ? 'Deshabilitar' : 'Habilitar'); ?>"
                                    class="cm-btn cm-btn-warning cm-btn-sm"><?php echo e($item->estado_logico ? 'Deshabilitar' : 'Habilitar'); ?></button>
                                <form method="POST" action="<?php echo e(route('tipos-investigacion.destroy', $item->id)); ?>" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar PERMANENTEMENTE este tipo de investigación?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </form>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/tipo_investigacion/index.blade.php ENDPATH**/ ?>