<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOLVENCIA OFICIAL</title>
    <style>
        @page { margin: 25mm; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; line-height: 1.8; text-align: justify; }
        .header-img { text-align: center; margin-bottom: 20px; }
        .header-img img { width: 100%; max-width: 650px; }
        .title { text-align: center; font-size: 16pt; font-weight: bold; margin: 40px 0; text-decoration: underline; }
        .content { margin-bottom: 60px; }
        .signature-area { margin-top: 100px; text-align: center; }
        .signature-line { width: 50%; border-top: 1px solid #000; margin: 50px auto 5px auto; }
        .footer { position: fixed; bottom: 10mm; left: 25mm; right: 25mm; text-align: center; font-size: 8pt; color: #777; }
    </style>
</head>
<body>
    <div class="header-img">
        <img src="<?php echo e(public_path('imagenes/barras.jpeg')); ?>" alt="Encabezado UPTP">
    </div>

    <div class="title">SOLVENCIA</div>

    <div class="content">
        <p>
            Quien emite, <strong>Coordinación de Proyectos</strong> de la Universidad Politécnica Territorial Juan de Jesús Montilla, hace constar por medio de la presente que el/la bachiller <strong><?php echo e($integrante['nombre_completo']); ?></strong>, titular de la cédula de identidad N° <strong>V-<?php echo e($integrante['cedula']); ?></strong>, adscrito(a) a la sección <strong><?php echo e($seccion); ?></strong> del Trayecto <strong><?php echo e($trayecto); ?></strong>, se encuentra <strong>SOLVENTE</strong> con todos los requisitos académicos, documentales y técnicos exigidos por el programa, tras haber culminado y aprobado satisfactoriamente el <strong><?php echo e($tipoProyecto); ?></strong> titulado <strong>“<?php echo e($titulo_proyecto); ?>”</strong>, desarrollado en la comunidad <strong><?php echo e($comunidad); ?></strong> durante el lapso académico <strong><?php echo e($lapso); ?></strong>, bajo la supervisión y aval académico del docente <strong><?php echo e($profesor_responsable); ?></strong>.
        </p>
        
        <p>
            Constancia que se expide a solicitud de la parte interesada, en la ciudad de Acarigua, estado Portuguesa, a los <?php echo e($dia); ?> días del mes de <?php echo e($mes); ?> del año <?php echo e($anio); ?>.
        </p>
    </div>

    <div class="signature-area">
        <div class="signature-line"></div>
        <strong>Coordinación de Proyectos</strong>
        <br>UPTP Juan de Jesús Montilla
    </div>

    <div class="footer">
        Sistema de Repositorio de <?php echo e($tipoProyecto == 'Proyecto Sociotecnológico' ? 'Proyectos Sociotecnológicos' : 'Proyectos Sociocomunitarios'); ?>

    </div>
</body>
</html><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/pdf/solvencia.blade.php ENDPATH**/ ?>