<?php $__env->startSection('title', 'Visor de documento'); ?>
<?php $__env->startSection('header', 'Visor de documento'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .watermark-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0.15;
    }
    .watermark-overlay img {
        width: 50%;
        max-width: 400px;
        height: auto;
    }
    .pdf-container {
        width: 100%;
        height: calc(100vh - 140px);
        position: relative;
    }
    .pdf-container embed,
    .pdf-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
</style>

<div style="background:#f5f5f5; padding:10px; margin-bottom:10px; border:1px solid #ccc; font-size:12px;">
    <b>Documento:</b> <?php echo e($doc->componente?->nombre ?? 'Sin nombre'); ?> |
    <b>Proyecto:</b> <?php echo e($doc->proyecto->titulo ?? 'N/A'); ?> |
    <a href="<?php echo e(route('proyectos.buscar')); ?>" style="color:#0000EE;">&larr; Volver a explorador</a>
</div>

<div class="pdf-container">
    <embed src="<?php echo e($docUrl); ?>#toolbar=1&navpanes=0" type="application/pdf">
</div>

<div class="watermark-overlay">
    <img src="<?php echo e(asset('imagenes/uptp-logo.png')); ?>" alt="UPTP">
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/documentos/viewer.blade.php ENDPATH**/ ?>