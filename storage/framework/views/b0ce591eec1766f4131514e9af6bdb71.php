<div id="contenedor">
    <div id="arriba">
        <img src="<?php echo e(asset('imagenes/barras.jpeg')); ?>" alt="Encabezado Institucional" style="width: 100%; height: 100%; object-fit: fill; display: block;">
    </div>

    <div id="centro_login">
        <h2 style="font-size: 22px; font-weight: bold; margin-top: 30px; margin-bottom: 30px;">
            Iniciar Sesi&oacute;n &mdash; Repositorio UPTP
        </h2>

        <form wire:submit="login">
            <table align="center" style="margin-bottom: 20px;">
                <tr>
                    <td align="right" style="font-weight: bold; padding: 10px 10px 10px 0; font-size: 15px;">Usuario:</td>
                    <td align="left" style="padding: 5px 0;">
                        <input wire:model="usuario" id="inp-usuario" type="text" placeholder="CÉDULA O USUARIO" required autocomplete="username">
                        <span style="color: red; font-weight: bold; font-size: 16px; margin-left: 5px;">*</span>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="font-weight: bold; padding: 10px 10px 10px 0; font-size: 15px;">Contrase&ntilde;a:</td>
                    <td align="left" style="padding: 5px 0;">
                        <input wire:model="password" id="inp-password" type="password" placeholder="CONTRASEÑA" required autocomplete="current-password">
                        <span style="color: red; font-weight: bold; font-size: 16px; margin-left: 5px;">*</span>
                    </td>
                </tr>
            </table>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #dc2626; color: #991b1b; padding: 12px 16px; margin-bottom: 20px; border-radius: 6px; font-size: 13px; font-weight: 600; text-align: center; box-shadow: 0 2px 8px rgba(220,38,38,0.08); display: flex; align-items: center; justify-content: center; gap: 10px; max-width: 380px; margin-left: auto; margin-right: auto;">
                    <span style="font-size: 18px;">✘</span>
                    <span><?php echo e($error); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div style="margin-bottom: 30px;">
                <button type="submit" id="btn-login" class="boton" style="margin-bottom: 30px;" wire:loading.attr="disabled">
                    <span wire:loading.remove>Iniciar sesi&oacute;n</span>
                    <span wire:loading>Verificando...</span>
                </button>
            </div>
        </form>

        <div style="text-align: left; padding: 0 10px; margin-top: 80px;">
            <p style="margin-bottom: 15px; font-size: 14px;">Los campos con <span style="color: red; font-weight: bold;">*</span> son obligatorios</p>
            <p style="margin: 0; font-size: 12px; font-weight: normal; line-height: 1.4;">
                Nota:<br>
                -Si es la primera vez que ingresa, su usuario y contrase&ntilde;a es la c&eacute;dula.<br>
                -Debe cambiar la contrase&ntilde;a cuando inicie sesi&oacute;n por primera vez.
            </p>
        </div>
    </div>

    <div id="abajo" style="margin-top: 0;">
        Todos los Derechos Reservados 2014 UPTP - Cr&eacute;ditos Unidad de Sistemas / Desarrollo de Software.
    </div>
</div>
<?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/login.blade.php ENDPATH**/ ?>