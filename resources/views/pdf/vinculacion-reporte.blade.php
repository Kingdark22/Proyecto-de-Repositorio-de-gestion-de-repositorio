<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Vinculación - {{ $titulo }}</title>
    <style>
        .header-box {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 3px double #000;
        }
        .header-box .institucion {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-box .sub {
            font-size: 8pt;
            color: #333;
            margin-top: 2px;
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #000; margin: 30px; }
        h1 { font-size: 14pt; color: #000; text-align: center; margin-bottom: 2px; }
        h2 { font-size: 10pt; color: #000; text-align: center; font-weight: bold; margin-top: 4px; margin-bottom: 4px; }
        .fecha { text-align: right; font-size: 7pt; color: #555; margin-bottom: 16px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 7pt; color: #666; padding: 8px; border-top: 1px solid #000; }
        .watermark {
            position: fixed;
            top: 42%;
            left: 10%;
            right: 10%;
            text-align: center;
            font-size: 36pt;
            color: #eee;
            z-index: -1;
            transform: rotate(-30deg);
            font-weight: bold;
            letter-spacing: 4px;
        }
        .card {
            border: 1px solid #999;
            border-radius: 4px;
            padding: 12px 14px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .card-header {
            font-size: 11pt;
            font-weight: bold;
            color: #000;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .field-label {
            font-size: 7.5pt;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 6px;
            margin-bottom: 1px;
        }
        .field-value {
            font-size: 9pt;
            color: #000;
            margin-bottom: 2px;
        }
        .two-col {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .two-col td {
            width: 50%;
            padding: 2px 8px 2px 0;
            vertical-align: top;
            border: none;
        }
        .tag {
            display: inline-block;
            background: #f5f5f5;
            border: 1px solid #ccc;
            border-radius: 2px;
            padding: 1px 6px;
            font-size: 7.5pt;
            color: #000;
            margin-right: 3px;
        }
        .comunidad-block {
            background: #f5f5f5;
            border-left: 3px solid #000;
            padding: 6px 10px;
            margin-top: 6px;
            font-size: 8.5pt;
        }
        .resumen-text {
            font-size: 8.5pt;
            color: #222;
            line-height: 1.4;
            margin-top: 4px;
            text-align: justify;
        }
        .empty-state {
            text-align: center;
            color: #999;
            margin-top: 60px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="watermark">UPT-PNFI</div>

    <div style="text-align:center;margin-bottom:12px;">
        <img src="{{ public_path('imagenes/barras.jpeg') }}" alt="Encabezado UPTP" style="width:100%;max-width:650px;">
    </div>
    <div class="header-box">
        <div class="institucion">República Bolivariana de Venezuela</div>
        <div class="sub">Universidad Politécnica Territorial &laquo;Juan de Jesús Montilla&raquo;</div>
        <div class="sub">Programa Nacional de Formación en Informática</div>
    </div>

    <h1>Reporte de los Proyectos seleccionados</h1>
    <h2>{{ $titulo }}</h2>
    <div class="fecha">Generado: {{ $fecha }}</div>

    @if($vinculaciones->isEmpty())
        <p class="empty-state">No se encontraron proyectos vinculados.</p>
    @else
        @foreach($vinculaciones as $idx => $v)
            @php $p = $v->proyecto; @endphp
            <div class="card">
                <div class="card-header">
                    {{ $idx + 1 }}. {{ $p?->titulo ?? 'N/A' }}
                </div>

                <table class="two-col">
                    <tr>
                        <td>
                            @if($p && $p->resumen)
                                <div class="field-label">Resumen</div>
                                <div class="resumen-text">{{ Str::limit(strip_tags($p->resumen), 300) }}</div>
                            @endif

                            <div class="field-label">Título de la vinculación</div>
                            <div class="field-value">{{ $v->titulo }}</div>

                            @if(isset($v->lapso) && $v->lapso && isset($lapsosNombres[$v->lapso]))
                                <div class="field-label">Lapso académico</div>
                                <div class="field-value">{{ $lapsosNombres[$v->lapso] }}</div>
                            @endif
                        </td>
                        <td>
                            @if($p)
                                @if($p->equipo_ref)
                                    <div class="field-label">Equipo / Grupo</div>
                                    <div class="field-value">{{ $p->equipo_resumen }}</div>
                                @endif
                                @if($p->creador_cedula)
                                    <div class="field-label">Creador (Cédula)</div>
                                    <div class="field-value">{{ $p->creador_cedula }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                </table>

                @if($p)
                    <div class="field-label" style="margin-top:8px;">Clasificación</div>
                    <div>
                        @if($p->linea_investigacion)
                            <span class="tag">{{ $p->linea_investigacion->nombre ?? $p->linea_investigacion->lin_nombre_investigacion }}</span>
                        @endif
                        @if($p->tipo_investigacion)
                            <span class="tag">{{ $p->tipo_investigacion->tin_nombre ?? '' }}</span>
                        @endif
                        @if($p->metodologia)
                            <span class="tag">{{ $p->metodologia->mei_nombre ?? '' }}</span>
                        @endif
                        @if($p->tipo_publicacion)
                            <span class="tag">{{ $p->tipo_publicacion->tpu_nombre ?? '' }}</span>
                        @endif
                        @if(!$p->linea_investigacion && !$p->tipo_investigacion && !$p->metodologia && !$p->tipo_publicacion)
                            <span style="color:#999;">Sin clasificación</span>
                        @endif
                    </div>
                @endif

                @if(isset($v->integrantes) && $v->integrantes->isNotEmpty())
                    <div class="field-label" style="margin-top:8px;">Integrantes del equipo</div>
                    <table style="width:100%;border-collapse:collapse;font-size:8pt;margin-top:4px;">
                        <thead>
                            <tr style="background:#f0f0f0;">
                                <th style="border:1px solid #ccc;padding:4px 6px;text-align:center;width:20px;font-weight:bold;">N°</th>
                                <th style="border:1px solid #ccc;padding:4px 6px;text-align:left;font-weight:bold;">Apellidos y Nombres</th>
                                <th style="border:1px solid #ccc;padding:4px 6px;text-align:center;font-weight:bold;width:90px;">Cédula</th>
                                <th style="border:1px solid #ccc;padding:4px 6px;text-align:center;font-weight:bold;width:70px;">Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($v->integrantes as $i => $integrante)
                                @php
                                    $fullName = trim(($integrante->apellido ?? '') . ', ' . ($integrante->nombre ?? ''));
                                    if (trim($fullName) === ', ') $fullName = trim(($integrante->nombre ?? '') . ' ' . ($integrante->apellido ?? ''));
                                    $rol = ($integrante->rol ?? '') === 'Líder' ? 'Líder' : 'Integrante';
                                @endphp
                                <tr style="background:{{ $loop->iteration % 2 == 0 ? '#fafafa' : '#fff' }};">
                                    <td style="border:1px solid #ddd;padding:3px 6px;text-align:center;">{{ $loop->iteration }}</td>
                                    <td style="border:1px solid #ddd;padding:3px 6px;">{{ $fullName }}</td>
                                    <td style="border:1px solid #ddd;padding:3px 6px;text-align:center;">{{ $integrante->cedula }}</td>
                                    <td style="border:1px solid #ddd;padding:3px 6px;text-align:center;font-weight:{{ $rol === 'Líder' ? 'bold' : 'normal' }};color:{{ $rol === 'Líder' ? '#000' : '#555' }};">{{ $rol }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if($v->comunidad)
                    <div class="comunidad-block">
                        <strong>Comunidad:</strong> {{ $v->comunidad->nombre }}
                        @if($v->comunidad->rif)
                            <br><span style="font-size:7.5pt;color:#555;">RIF: {{ $v->comunidad->rif }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <div class="footer">Sistema de Gestión de Proyectos Socio-Tecnológicos — PNFI | Total: {{ $vinculaciones->count() }} proyecto(s)</div>
</body>
</html>
