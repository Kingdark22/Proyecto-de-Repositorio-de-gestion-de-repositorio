<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solvencia - {{ $folio }}</title>
    <style>
        @page {
            margin: 20mm 18mm 22mm 18mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #222;
            line-height: 1.6;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 20%;
            width: 60%;
            opacity: 0.1;
            z-index: -1;
            transform: rotate(-30deg);
        }

        /* ── Header ─────────────────────────────────────── */
        .header-img {
            text-align: center;
            margin-bottom: 10px;
        }
        .header-img img {
            width: 100%;
            height: auto;
            max-width: 700px;
        }
        .header .pnf-line {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            color: #000;
            margin-top: 5px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        /* ── Folio ────────────────────────────────────────── */
        .folio-box {
            text-align: right;
            font-size: 8pt;
            color: #666;
            margin-top: 6px;
        }
        .folio-box strong {
            color: #000;
        }

        /* ── Title ────────────────────────────────────────── */
        .title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 22px 0 25px 0;
            letter-spacing: 1px;
        }
        .title::after {
            content: '';
            display: block;
            width: 80px;
            height: 2px;
            background: #000;
            margin: 8px auto 0 auto;
        }

        /* ── Body text ────────────────────────────────────── */
        .content {
            text-align: justify;
            margin: 0 3mm;
        }
        .content p {
            margin: 10px 0;
        }
        .content .project-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            font-style: italic;
            color: #000;
            margin: 20px 0;
        }

        /* ── Integrante highlight box ─────────────────────── */
        .integrante-box {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            margin: 20px auto;
        }
        .integrante-box .cedula-text {
            font-size: 11pt;
            font-weight: normal;
            color: #333;
        }
        .integrante-box .rol-text {
            font-size: 10pt;
            font-weight: normal;
            color: #555;
            margin-top: 2px;
        }
        
        /* ── Data table ───────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 9.5pt;
        }
        .data-table td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .data-table td.label {
            width: 100px;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }
        .data-table td.value {
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        /* ── Signature area ───────────────────────────────── */
        .signature-area {
            margin-top: 50px;
            text-align: center;
        }
        .signature-row {
            display: inline-block;
            width: 45%;
            vertical-align: top;
            text-align: center;
            padding: 0 15px;
        }
        .signature-line {
            width: 80%;
            border-top: 1.5px solid #333;
            margin: 0 auto 8px auto;
        }
        .signature-name {
            font-weight: bold;
            font-size: 10pt;
            color: #000;
        }
        .signature-role {
            font-size: 8.5pt;
            color: #666;
        }

        /* ── Footer ───────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 12mm;
            left: 18mm;
            right: 18mm;
            text-align: center;
            font-size: 7pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }

        /* ── Comunidad info ───────────────────────────────── */
        .comunidad-info {
            font-size: 9pt;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 2px;
            margin: 10px 0;
        }
        .comunidad-info strong {
            color: #000;
        }
    </style>
</head>
<body>

    <!-- ─── HEADER ──────────────────────────────────────── -->
    <div class="header-img">
        <img src="{{ public_path('imagenes/barras.jpeg') }}" alt="Encabezado UPTP">
    </div>
    <div class="header">
        <div class="pnf-line">
            @if($pnf)
                Programa Nacional de Formación en {{ $pnf_limpio }}
            @else
                Programa Nacional de Formación
            @endif
        </div>
    </div>

    <div class="folio-box">
        Folio: <strong>{{ $folio }}</strong>
    </div>

    <!-- ─── TITLE ────────────────────────────────────────── -->
    <div class="title">Solvencia de {{ $tipoProyecto }}</div>

    <!-- ─── CONTENT ──────────────────────────────────────── -->
    <div class="content">

        <p>
            Por medio de la presente se hace constar que el/la ciudadano/a:
        </p>

        <!-- ─── INTEGRANTE ────────────────────────────────── -->
        @if($integrante)
        <div class="integrante-box">
            {{ $integrante['nombre_completo'] }}
            <div class="cedula-text">Cédula de Identidad: V-{{ $integrante['cedula'] }}</div>
            @if($integrante['rol'])
                <div class="rol-text">Rol: {{ $integrante['rol'] }}</div>
            @endif
        </div>
        @endif

        <p>
            Ha culminado y aprobado satisfactoriamente el <strong>{{ $tipoProyecto }}</strong>
            titulado:
        </p>

        <div class="project-title">
            “{{ $titulo_proyecto }}”
        </div>
        
        <p>
            Se certifica que el proyecto ha cumplido satisfactoriamente con todos los requerimientos académicos,
            documentales y técnicos exigidos por el programa para la creación y desarrollo de {{ $tipoProyecto == 'Proyecto Sociotecnológico' ? 'proyectos sociotecnológicos' : 'proyectos sociocomunitarios' }}.
        </p>
        
        <p style="text-align: center; font-weight: bold; font-size: 12pt; margin: 20px 0;">
            ESTADO DEL PROYECTO: APROBADO
        </p>

        <!-- ─── DATOS ACADÉMICOS ───────────────────────────── -->
        <table class="data-table">
            @if($pnf)
            <tr>
                <td class="label">PNF:</td>
                <td class="value">{{ $pnf_nombre ?: $pnf }}</td>
            </tr>
            @endif
            @if($trayecto)
            <tr>
                <td class="label">Trayecto:</td>
                <td class="value">{{ $trayecto }}</td>
            </tr>
            @endif
            @if($seccion)
            <tr>
                <td class="label">Sección:</td>
                <td class="value">{{ $seccion }}</td>
            </tr>
            @endif
            @if($lapso)
            <tr>
                <td class="label">Lapso Académico:</td>
                <td class="value">{{ $lapso }}</td>
            </tr>
            @endif
            @if($comunidad)
            <tr>
                <td class="label">Comunidad:</td>
                <td class="value">{{ $comunidad }}</td>
            </tr>
            @endif
        </table>

        <p>
            Constancia que se expide a solicitud de parte interesada, habiendo sido supervisada y avalada por el/la docente:
            <br>
            <strong>{{ $profesor_responsable }}</strong>
            <br><br>
            En la ciudad de <strong>Acarigua, estado Portuguesa</strong>, a los <strong>{{ $dia }}</strong> días del mes de
            <strong>{{ $mes }}</strong> del año <strong>{{ $anio }}</strong>.
        </p>

    </div>

    <!-- ─── FIRMAS ────────────────────────────────────────── -->
    <div class="signature-area">
        <div style="margin-bottom:40px;">
            <div class="signature-line" style="width:60%;"></div>
            <div class="signature-name">Coordinación del Programa Nacional de Formación</div>
            <div class="signature-role">Coordinador(a) del PNF</div>
        </div>
        <div>
            <div class="signature-line" style="width:60%;"></div>
            <div class="signature-name">Sello de la Institución</div>
            <div class="signature-role">Universidad Politécnica Territorial Juan de Jesús Montilla</div>
        </div>
    </div>

    <div class="footer">
        Sistema de Repositorio de {{ $tipoProyecto == 'Proyecto Sociotecnológico' ? 'Proyectos Sociotecnológicos' : 'Proyectos Sociocomunitarios' }} — UPTP Juan de Jesús Montilla
        &mdash; Folio {{ $folio }}
    </div>

</body>
</html>
