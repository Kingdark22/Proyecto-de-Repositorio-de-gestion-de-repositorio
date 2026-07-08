<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title><?php echo e($subjectLine ?? 'Notificación'); ?></title></head>
<body style="font-family:Arial,sans-serif;font-size:14px;line-height:1.6;color:#333;padding:20px;">
    <div style="max-width:600px;margin:auto;border:1px solid #ddd;border-radius:6px;overflow:hidden;">
        <div style="background:#8b0000;color:#fff;padding:15px 20px;font-size:18px;font-weight:bold;"><?php echo e($subjectLine ?? 'Notificación'); ?></div>
        <div style="padding:20px;">
            <p><?php echo e(nl2br(e($messageBody ?? ''))); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($actionUrl && $actionText): ?>
                <p style="text-align:center;margin-top:20px;">
                    <a href="<?php echo e($actionUrl); ?>" style="display:inline-block;background:#8b0000;color:#fff;padding:10px 24px;border-radius:4px;text-decoration:none;font-weight:bold;"><?php echo e($actionText); ?></a>
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p style="font-size:12px;color:#999;margin-top:30px;">Sistema de Gestión de Proyectos — UPTP Juan de Jesús Montilla</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views\emails\notificacion-proyecto.blade.php ENDPATH**/ ?>