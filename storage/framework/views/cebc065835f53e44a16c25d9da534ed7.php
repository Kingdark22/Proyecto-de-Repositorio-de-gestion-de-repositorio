<?php $__env->startSection('title', 'Gestión de Comunidades'); ?>
<?php $__env->startSection('header', 'Gestión de Comunidades'); ?>

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
    .cm-btn:hover { transform: translateY(-1px); }
    .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
    .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
    .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
    .cm-btn-sm { padding: 0.35rem 0.7rem; font-size: 0.85rem; }
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

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Buscador y listado</legend>
        <table width="100%" border="0" cellpadding="8" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="65%">
                    <form method="GET" action="<?php echo e(route('comunidades.index')); ?>" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                        <b>Buscar (nombre / RIF):</b>
                        <input name="search" type="text" value="<?php echo e($search); ?>" style="width: 70%; padding: 3px;" placeholder="Nombre o RIF...">
                        <button type="submit" class="cm-btn cm-btn-sm">Buscar</button>
                    </form>
                </td>
                <td width="35%" align="right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeGestionar): ?>
                        <button type="button" onclick="window.location='<?php echo e(route('comunidades.create')); ?>'" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 8px 18px;">
                            Registrar nueva comunidad
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comunidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <tr style="background-color: <?php echo e($loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF'); ?>;" valign="top">
                        <td align="center"><?php echo e($loop->iteration); ?></td>
                        <td>
                            <span style="font-weight: bold;"><?php echo e($c->nombre); ?></span>
                            <br><span style="font-size: 9px; color: #555;"><?php echo e($c->direccion?->municipio?->estado?->est_nombre ?? ''); ?> / <?php echo e($c->direccion?->municipio?->mun_nombre ?? ''); ?> - <?php echo e($c->direccion?->dir_calle ?? ''); ?></span>
                        </td>
                        <td align="center"><?php echo e($c->rif); ?></td>
                        <td align="center"><?php echo e($c->correo); ?><br><b><?php echo e($c->numero_telefono); ?></b></td>
                        <td align="center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeGestionar): ?>
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" onclick="window.location='<?php echo e(route('comunidades.edit', $c->id)); ?>'"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <form method="POST" action="<?php echo e(route('comunidades.destroy', $c->id)); ?>" style="display: inline; margin: 0;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar esta comunidad?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span style="color: #888; font-size: 10px;">Solo lectura</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comunidades->isEmpty()): ?>
                    <tr>
                        <td colspan="6" align="center" style="padding: 20px;">No hay comunidades registradas.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div style="margin-top: 10px;"><?php echo e($comunidades->links()); ?></div>
    </fieldset>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/comunidades/index.blade.php ENDPATH**/ ?>