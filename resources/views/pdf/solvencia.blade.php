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
        <img src="{{ public_path('imagenes/barras.jpeg') }}" alt="Encabezado UPTP">
    </div>

    <div class="title">SOLVENCIA</div>

    <div class="content">
        <p>
            Quien emite es el <strong>Departamento de Creación Intelectual</strong> de la Universidad Politécnica Territorial Juan de Jesús Montilla, hace constar por medio de la presente que el/la estudiante <strong>{{ $integrante['nombre_completo'] }}</strong>, titular de la cédula de identidad N° <strong>V-{{ $integrante['cedula'] }}</strong>, adscrito(a) a la sección <strong>{{ $seccion }}</strong> del Trayecto <strong>{{ $trayecto }}</strong> del <strong>{{ $pnf_nombre }}</strong>, se encuentra <strong>SOLVENTE</strong> con todos los requisitos académicos, documentales y técnicos exigidos por el programa, tras haber culminado y aprobado satisfactoriamente el <strong>{{ $tipoProyecto }}</strong> titulado <strong>“{{ $titulo_proyecto }}”</strong>, desarrollado en la comunidad <strong>{{ $comunidad }}</strong> durante el lapso académico <strong>{{ $lapso }}</strong>, bajo la supervisión y aval académico del docente <strong>{{ $profesor_responsable }}</strong>.
        </p>
        
        <p>
            Constancia que se expide a solicitud de la parte interesada, en la ciudad de Acarigua, estado Portuguesa, a los {{ $dia }} días del mes de {{ $mes }} del año {{ $anio }}.
        </p>
    </div>

    <div class="signature-area">
        <div class="signature-line"></div>
        <strong>Departamento de Creación Intelectual</strong>
        <br>UPTP Juan de Jesús Montilla
    </div>

    <div class="footer">
        Sistema de Repositorio de {{ $tipoProyecto == 'Proyecto Sociotecnológico' ? 'Proyectos Sociotecnológicos' : 'Proyectos Sociocomunitarios' }}
    </div>
</body>
</html>