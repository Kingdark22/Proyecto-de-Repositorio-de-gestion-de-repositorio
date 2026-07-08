<?php $__env->startSection('title', 'Página no encontrada'); ?>

<?php $__env->startSection('content'); ?>
<div style="text-align: center; padding: 60px 20px;">
    <h1 style="font-size: 72px; color: #8b0000; margin: 0; font-weight: bold;">404</h1>
    <h2 style="color: #333; margin-top: 10px;">Página no encontrada</h2>
    <p style="color: #666; margin: 20px 0;">El recurso solicitado no existe o ha sido eliminado.</p>
    <a href="<?php echo e(url('/dashboard')); ?>" class="cm-btn cm-btn-primary">Volver al inicio</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\errors\404.blade.php ENDPATH**/ ?>