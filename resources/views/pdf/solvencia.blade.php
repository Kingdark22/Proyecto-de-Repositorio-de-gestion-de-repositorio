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

        /* ── Header ─────────────────────────────────────── */
        .header {
            text-align: center;
            margin-bottom: 4px;
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 12px;
        }
        .header .ministerio {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #555;
        }
        .header .institution {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a3a5c;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }
        .header .institution-sub {
            font-size: 8pt;
            color: #444;
            margin-top: 2px;
        }
        .header .pnf-line {
            font-size: 9pt;
            font-weight: bold;
            color: #1a3a5c;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #ccc;
        }

        /* ── Folio ────────────────────────────────────────── */
        .folio-box {
            text-align: right;
            font-size: 8pt;
            color: #666;
            margin-top: 6px;
        }
        .folio-box strong {
            color: #1a3a5c;
        }

        /* ── Title ────────────────────────────────────────── */
        .title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a3a5c;
            margin: 22px 0 25px 0;
            letter-spacing: 1px;
        }
        .title::after {
            content: '';
            display: block;
            width: 80px;
            height: 2px;
            background: #1a3a5c;
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
            font-size: 12pt;
            font-weight: bold;
            font-style: italic;
            color: #1a3a5c;
            margin: 16px 0;
            padding: 10px;
            border-left: 3px solid #1a3a5c;
            background: #f8f9fa;
        }

        /* ── Integrante highlight box ─────────────────────── */
        .integrante-box {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            color: #1a3a5c;
            margin: 16px auto;
            padding: 12px 20px;
            border: 2px solid #1a3a5c;
            background: #f0f4f8;
            border-radius: 4px;
            max-width: 85%;
        }
        .integrante-box .cedula-text {
            font-size: 10pt;
            font-weight: normal;
            color: #555;
        }
        .integrante-box .rol-text {
            font-size: 9pt;
            font-weight: normal;
            color: #666;
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
            color: #1a3a5c;
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
            color: #1a3a5c;
        }
    </style>
</head>
<body>

    <!-- ─── HEADER ──────────────────────────────────────── -->
    <div class="header">
        <div class="ministerio">
            República Bolivariana de Venezuela<br>
            Ministerio del Poder Popular para la Educación Universitaria
        </div>
        <div class="institution">
            Universidad Politécnica Territorial Juan de Jesús Montilla
        </div>
        <div class="institution-sub">
            — UPTP Juan de Jesús Montilla —<br>
            Acarigua, Estado Portuguesa
        </div>
        <div class="pnf-line">
            @if($pnf)
                Programa Nacional de Formación en {{ $pnf_nombre ?: $pnf }}
            @else
                Programa Nacional de Formación
            @endif
        </div>
    </div>

    <div class="folio-box">
        Folio: <strong>{{ $folio }}</strong>
    </div>

    <!-- ─── TITLE ────────────────────────────────────────── -->
    <div class="title">Solvencia de Proyecto Sociocomunitario</div>

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
            Ha culminado y aprobado satisfactoriamente el <strong>Proyecto Sociocomunitario</strong>
            titulado:
        </p>

        <div class="project-title">
            “{{ $titulo_proyecto }}”
        </div>

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
            Constancia que se expide a solicitud de parte interesada en la ciudad de
            <strong>Acarigua, estado Portuguesa</strong>, a los <strong>{{ $dia }}</strong> días del mes de
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
        Sistema de Repositorio de Proyectos Sociocomunitarios — UPTP Juan de Jesús Montilla
        &mdash; Folio {{ $folio }}
    </div>

</body>
</html>
