<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOLVENCIA OFICIAL - {{ $folio }}</title>
    <style>
        @page { margin: 25mm; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; line-height: 1.8; text-align: justify; }
        .header-img { text-align: center; margin-bottom: 20px; }
        .header-img img { width: 100%; max-width: 650px; }
        .folio { text-align: right; font-size: 9pt; margin-bottom: 30px; }
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

    <div class="folio">Folio: {{ $folio }}</div>

    <div class="title">SOLVENCIA</div>

    <div class="content">
        <p>
            Quien suscribe, <strong>Coordinación del Programa Nacional de Formación en {{ $pnf_limpio }}</strong> de la Universidad Politécnica Territorial Juan de Jesús Montilla, hace constar por medio de la presente que el/la bachiller <strong>{{ $integrante['nombre_completo'] }}</strong>, titular de la cédula de identidad N° <strong>V-{{ $integrante['cedula'] }}</strong>, adscrito(a) a la sección <strong>{{ $seccion }}</strong> del Trayecto <strong>{{ $trayecto }}</strong>, se encuentra <strong>SOLVENTE</strong> con todos los requisitos académicos, documentales y técnicos exigidos por el programa, tras haber culminado y aprobado satisfactoriamente el <strong>{{ $tipoProyecto }}</strong> titulado <strong>“{{ $titulo_proyecto }}”</strong>, desarrollado en la comunidad <strong>{{ $comunidad }}</strong> durante el lapso académico <strong>{{ $lapso }}</strong>, bajo la supervisión y aval académico del docente <strong>{{ $profesor_responsable }}</strong>.
        </p>
        
        <p>
            Constancia que se expide a solicitud de la parte interesada, en la ciudad de Acarigua, estado Portuguesa, a los {{ $dia }} días del mes de {{ $mes }} del año {{ $anio }}.
        </p>
    </div>

    <div class="signature-area">
        <div class="signature-line"></div>
        <strong>Coordinación del PNF</strong>
        <br>UPTP Juan de Jesús Montilla
    </div>

    <div class="footer">
        Sistema de Repositorio de {{ $tipoProyecto == 'Proyecto Sociotecnológico' ? 'Proyectos Sociotecnológicos' : 'Proyectos Sociocomunitarios' }} — Folio: {{ $folio }}
    </div>
</body>
</html>