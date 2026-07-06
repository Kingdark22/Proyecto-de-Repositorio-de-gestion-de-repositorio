<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Repositorio UPTP</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('imagenes/uptp-logo.png')); ?>">
    <style>
        body { background: #ffffff; margin: 0; padding: 20px 0; display: flex; justify-content: center; }
        #contenedor {
            border: 0 px #000000 dashed;
            height: 100%; width: 1010px; margin: 0 auto;
            box-shadow: 0px 0px 15px #000000; -moz-box-shadow: 0px 0px 15px #000000;
            border-radius: 15px; -moz-border-radius: 15px;
            font-family: "Verdana", Arial, sans-serif; font-size: 14px;
        }
        #arriba { width: 1010px; height: 90px; border-radius: 15px; overflow: hidden; }
        #centro_login { color: black; background-color: transparent; padding: 20px; box-sizing: border-box; min-height: 480px; text-align: center; }
        #abajo { font-size: 14px; text-align: center; background-color:#E0ECF8; border-radius:0px 0px 15px 15px; width:1010px; padding: 6px; box-sizing: border-box; border-top: 1px solid #b1b9c1; margin-top: 15px; }
        .boton { height:30px; background-color: #0072C6; color: #FFFFFF; border: 0 none; padding: 0 15px; cursor: pointer; font-weight: bold; }
        .boton:disabled { background-color: #6aabdb; cursor: not-allowed; }
        input[type="text"], input[type="email"], input[type="password"] { height:20px; width:220px; padding:5px 8px; border: 1px solid #a9a9a9; }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus { background: #fff; border:1px solid #A00; box-shadow: 0 0 3px #aaa; outline: none; }
    </style>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('login', []);

$key = null;
$__componentSlots = [];

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-4291602618-0', $key);

$__html = app('livewire')->mount($__name, $__params, $key, $__componentSlots);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/auth/login.blade.php ENDPATH**/ ?>