<div style="font-family: Arial, Helvetica, sans-serif; padding-top: 30px;">
    <h2 style="font-size: 23px; font-weight: bold; margin-bottom: 50px; margin-top: -30px; color: #000;">
        <?php
            $hour = date('H');
            $greeting = $hour < 12 ? 'Buenos días' : ($hour < 18 ? 'Buenas tardes' : 'Buenas noches');
        ?>
        <?php echo e($greeting); ?>: <?php echo e(auth()->user()->nombre); ?> <?php echo e(auth()->user()->apellido); ?>

    </h2>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; font-family: Arial, Helvetica, sans-serif; background-color: transparent; margin: 0; padding: 20px; width: 100%; box-sizing: border-box;">
        <legend style="margin-left: -5px; padding: 0;">
            <img src="<?php echo e(asset('imagenes/enterate.png')); ?>" alt="Entérate" style="height: 150px; display: inline-block; object-fit: contain; border: none; background-color: transparent; padding: 0; margin-top: -50px;" />
        </legend>
        
        <div style="font-size: 18px; margin-top: -15px; line-height: 1.1;">
            <b style="color: #000;">Nuestras redes sociales son:</b><br>
            <a href="https://www.facebook.com/UPTP-Juan-de-Jesús-Montilla-321794751770801" target="_blank" style="color: #0000EE; font-weight: bold; text-decoration: none;">www.facebook.com/UPTP-Juan-de-Jesús-Montilla-321794751770801</a><br>
            <a href="https://www.instagram.com/uptpjuandejesus" target="_blank" style="color: #0000EE; font-weight: bold; text-decoration: none;">www.instagram.com/uptpjuandejesus</a><br>
            <a href="https://www.twitter.com/UptpJuandeJesus" target="_blank" style="color: #0000EE; font-weight: bold; text-decoration: none;">www.twitter.com/UptpJuandeJesus</a>
        </div>
    </fieldset>
</div>
<?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/livewire/dashboard-manager.blade.php ENDPATH**/ ?>