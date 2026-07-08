<?php $__env->startSection('title', 'Configuración'); ?>
<?php $__env->startSection('header', 'Configuración'); ?>

<?php $__env->startSection('content'); ?>
<div style="font-size: 13px;">
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Perfil de usuario</legend>
        
        <?php $user = auth()->user(); ?>
        <table width="100%" cellpadding="6" cellspacing="0" style="font-size: 13px;">
            <tr>
                <td width="30%"><b>Cédula:</b></td>
                <td><?php echo e($user->usu_cedula ?? '—'); ?></td>
            </tr>
            <tr>
                <td><b>Nombre:</b></td>
                <td><?php echo e($user->usu_nombre ?? '—'); ?></td>
            </tr>
            <tr>
                <td><b>Rol activo:</b></td>
                <td><?php echo e(app(\App\Services\UserRoleService::class)->activeRoleLabel($user) ?? 'Sin rol'); ?></td>
            </tr>
        </table>


    </fieldset>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\configuracion\index.blade.php ENDPATH**/ ?>