<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solvencia</title>
    <style>
        @page {
            margin: 25mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #222;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 2px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header .logo-text {
            font-size: 9pt;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header .sub {
            font-size: 8pt;
            margin-top: 2px;
        }
        .header .institution {
            font-size: 8pt;
            margin-top: 2px;
        }
        .folio-box {
            text-align: right;
            font-size: 8pt;
            margin-top: 5px;
        }
        .title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 25px 0 30px 0;
        }
        .content {
            text-align: justify;
            margin: 0 5mm;
        }
        .content p {
            margin: 10px 0;
        }
        .content .highlight {
            font-weight: bold;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .data-table td {
            padding: 5px 8px;
            vertical-align: top;
        }
        .data-table td.label {
            width: 140px;
            font-weight: bold;
        }
        .data-table td.value {
            border-bottom: 1px solid #ccc;
        }
        .signature-area {
            margin-top: 50px;
            text-align: center;
        }
        .signature-area .line {
            width: 250px;
            border-top: 1px solid #333;
            margin: 0 auto 8px auto;
        }
        .signature-area .name {
            font-weight: bold;
            font-size: 10pt;
        }
        .signature-area .role {
            font-size: 9pt;
        }
        .footer {
            position: fixed;
            bottom: 15mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 7pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-text">República Bolivariana de Venezuela</div>
        <div class="institution">Ministerio del Poder Popular para la Educación Universitaria</div>
        <div class="sub">Universidad Politécnica Territorial del Estado Cojedes</div>
        <div class="institution" style="font-weight:bold;font-size:10pt;">{{ $pnf ? "Programa Nacional de Formación en $pnf" : 'Programa Nacional de Formación' }}</div>
    </div>

    <div class="folio-box">
        Folio: <strong>{{ $folio }}</strong>
    </div>

    <div class="title">Solvencia de Proyecto Sociocomunitario</div>

    <div class="content">
        <p>
            Por medio de la presente se hace constar que el/la ciudadano(a):
        </p>

        <table class="data-table">
            <tr>
                <td class="label">Nombre:</td>
                <td class="value"><strong>{{ $estudiante_nombre }}</strong></td>
            </tr>
            <tr>
                <td class="label">Cédula de Identidad:</td>
                <td class="value"><strong>{{ $estudiante_cedula }}</strong></td>
            </tr>
        </table>

        <p>
            Ha culminado y aprobado satisfactoriamente el <strong>Proyecto Sociocomunitario</strong> titulado:
        </p>

        <p style="text-align:center;font-size:13pt;font-weight:bold;margin:15px 0;">
            "{{ $titulo_proyecto }}"
        </p>

        <p>
            Correspondiente al:
        </p>

        <table class="data-table">
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
            @if($pnf)
            <tr>
                <td class="label">PNF:</td>
                <td class="value">{{ $pnf }}</td>
            </tr>
            @endif
        </table>

        <p>
            Constancia que se expide a solicitud de parte interesada en la ciudad de 
            San Carlos, estado Cojedes, a los {{ $dia }} días del mes de {{ $mes }} del año {{ $anio }}.
        </p>
    </div>

    <div class="signature-area">
        <div class="line"></div>
        <div class="name">Coordinación del PNF</div>
        <div class="role">Coordinador(a) del Programa Nacional de Formación</div>
        <div style="margin-top:30px;">
            <div class="line"></div>
            <div class="name">Sello de la Institución</div>
        </div>
    </div>

    <div class="footer">
        Sistema de Repositorio de Proyectos Sociocomunitarios &mdash; Folio {{ $folio }}
    </div>

</body>
</html>
