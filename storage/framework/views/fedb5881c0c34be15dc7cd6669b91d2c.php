<?php $__env->startSection('title', 'Gestión de Comunidades'); ?>
<?php $__env->startSection('header', 'Gestión de Comunidades'); ?>

<?php $__env->startSection('content'); ?>
    <div style="background-color: #FFFFFF; border: 1px solid #CCC; margin: 10px; padding: 15px;">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('comunidad-manager', []);

$key = null;
$__componentSlots = [];

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3977708997-0', $key);

$__html = app('livewire')->mount($__name, $__params, $key, $__componentSlots);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/comunidades/index.blade.php ENDPATH**/ ?>