<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Vinculación - {{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #222; margin: 30px; }
        h1 { font-size: 16pt; color: #8b0000; text-align: center; margin-bottom: 4px; }
        h2 { font-size: 11pt; color: #555; text-align: center; font-weight: normal; margin-top: 0; margin-bottom: 20px; }
        .fecha { text-align: right; font-size: 8pt; color: #888; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        th { background: #8b0000; color: #fff; padding: 6px 4px; text-align: left; font-weight: bold; }
        td { padding: 5px 4px; border-bottom: 1px solid #ddd; vertical-align: top; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .comunidad { font-size: 8pt; color: #555; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 7pt; color: #aaa; padding: 8px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>REPÚBLICA BOLIVARIANA DE VENEZUELA</h1>
    <h2>Reporte de Vinculación: {{ $titulo }}</h2>
    <div class="fecha">Generado: {{ $fecha }}</div>

    @if($vinculaciones->isEmpty())
        <p style="text-align:center;color:#999;margin-top:40px;">No se encontraron proyectos vinculados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th width="5%">N°</th>
                    <th width="35%">Título del Proyecto</th>
                    <th width="25%">Título Vinculación</th>
                    <th width="35%">Comunidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vinculaciones as $idx => $v)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $v->proyecto->titulo ?? 'N/A' }}</td>
                        <td><strong>{{ $v->vin_titulo }}</strong></td>
                        <td>
                            @if($v->comunidad)
                                <strong>{{ $v->comunidad->nombre }}</strong>
                                @if($v->comunidad->rif)
                                    <br><span class="comunidad">RIF: {{ $v->comunidad->rif }}</span>
                                @endif
                            @else
                                <span style="color:#999;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Sistema de Gestión de Proyectos Socio-Tecnológicos — PNFI</div>
</body>
</html>
