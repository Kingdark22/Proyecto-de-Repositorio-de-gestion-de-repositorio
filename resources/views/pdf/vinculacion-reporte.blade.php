<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Vinculacion - {{ $titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #000;
            margin: 14px 18px 30px 18px;
            line-height: 1.2;
        }
        .watermark {
            position: fixed;
            top: 42%;
            left: 10%;
            right: 10%;
            text-align: center;
            font-size: 34pt;
            color: #ebebeb;
            z-index: -1;
            transform: rotate(-30deg);
            font-weight: bold;
            letter-spacing: 4px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6.5pt;
            color: #555;
            padding: 4px 18px;
            border-top: 1px solid #000;
            background: #fff;
        }
        .header-img { text-align: center; margin-bottom: 3px; }
        .header-img img { width: 100%; max-width: 640px; }
        .header-box {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .header-box .inst { font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        .header-box .sub  { font-size: 7pt; color: #333; margin-top: 1px; }
        .reporte-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 3px 0 1px 0;
        }
        .reporte-sub {
            text-align: center;
            font-size: 8.5pt;
            font-weight: bold;
            margin: 0 0 2px 0;
        }
        .fecha {
            text-align: right;
            font-size: 6.5pt;
            color: #555;
            margin-bottom: 5px;
        }
        .titulo-header {
            background: #2c3e50;
            color: #fff;
            padding: 4px 8px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-radius: 2px;
        }
        .card {
            border: 1px solid #ccc;
            padding: 4px 6px;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }
        .card-header {
            font-size: 8.5pt;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }
        .vinculacion-info {
            background: #f0f4f8;
            border: 1px solid #b0bec5;
            border-radius: 3px;
            padding: 4px 8px;
            margin-bottom: 4px;
            font-size: 7.5pt;
            display: table;
            width: 100%;
        }
        .vinculacion-info .vi-item {
            display: table-cell;
            vertical-align: top;
            padding: 1px 6px;
        }
        .vinculacion-info .vi-label {
            font-size: 6.5pt;
            color: #555;
            text-transform: uppercase;
            font-weight: bold;
        }
        .vinculacion-info .vi-value {
            font-size: 8pt;
            color: #000;
            font-weight: bold;
        }
        .resumen-text {
            font-size: 7.5pt;
            color: #111;
            line-height: 1.3;
            text-align: justify;
            margin-bottom: 2px;
        }
        .lbl { font-size: 6.5pt; color: #555; text-transform: uppercase; margin-bottom: 0; }
        .fval { font-size: 7.5pt; color: #000; margin-bottom: 1px; }
        .integrantes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-top: 2px;
        }
        .integrantes-table th {
            background: #e8e8e8;
            border: 1px solid #bbb;
            padding: 2px 4px;
            font-weight: bold;
            text-align: left;
        }
        .integrantes-table td { border: 1px solid #ccc; padding: 1px 4px; }
        .integrantes-table tr:nth-child(even) td { background: #f9f9f9; }
        .section-footer {
            text-align: right;
            font-size: 6.5pt;
            color: #777;
            border-top: 1px dotted #ccc;
            padding-top: 2px;
            margin-top: 2px;
        }
        .extra-info {
            font-size: 6.5pt;
            color: #666;
            border-top: 1px dotted #ddd;
            padding-top: 2px;
            margin-top: 2px;
        }
        .empty-state { text-align: center; color: #999; margin-top: 60px; font-style: italic; }
    </style>
</head>
<body>
<div class="watermark">UPT-PNFI</div>

<div class="header-img">
    <img src="{{ public_path('imagenes/barras.jpeg') }}" alt="Encabezado UPTP">
</div>
<div class="header-box">
    <div class="inst">Republica Bolivariana de Venezuela</div>
    <div class="sub">Universidad Politecnica Territorial Juan de Jesus Montilla</div>
    <div class="sub">Programa Nacional de Formacion en Informatica</div>
</div>
<div class="reporte-title">Reporte de los Proyectos Vinculados</div>
<div class="reporte-sub">{{ $titulo }}</div>
<div class="fecha">Generado: {{ $fecha }}</div>

@if($vinculaciones->isEmpty())
    <p class="empty-state">No se encontraron proyectos vinculados.</p>
@else
    @foreach($gruposPorTitulo as $nombreTitulo => $vinculacionesDelGrupo)
        <div class="titulo-section" style="{{ $loop->first ? '' : 'page-break-before: always;' }}">
            @if(!$esFiltroEspecifico)
                <div class="titulo-header">{{ $loop->iteration }}. {{ $nombreTitulo }}</div>
            @endif

            @php
                $gruposPorComunidad = $vinculacionesDelGrupo->groupBy(function($v) {
                    return $v->comunidad?->id ?? 0;
                });
            @endphp

            @foreach($gruposPorComunidad as $comId => $vinculacionesDeComunidad)
                @php
                    $primerV = $vinculacionesDeComunidad->first();
                    $comunidad = $primerV->comunidad;
                @endphp

                @foreach($vinculacionesDeComunidad as $v)
                    @php $p = $v->proyecto; @endphp
                    <div style="border:1px solid #b0bec5;border-radius:4px;padding:6px 8px;margin-bottom:6px;page-break-inside:avoid;background:#fafbfc;">

                        {{-- Info de vinculacion: titulo + comunidad --}}
                        <div class="vinculacion-info">
                            <div class="vi-item" style="width:50%;">
                                <div class="vi-label">Titulo de vinculacion</div>
                                <div class="vi-value">{{ $v->titulo ?? 'Sin titulo' }}</div>
                            </div>
                            <div class="vi-item" style="width:50%;">
                                <div class="vi-label">Comunidad vinculada</div>
                                <div class="vi-value">{{ $comunidad?->nombre ?? 'Sin comunidad' }}</div>
                                @if($comunidad?->rif)
                                    <div style="font-size:6.5pt;color:#555;">RIF: {{ $comunidad->rif }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- Nombre del proyecto --}}
                        <div class="card-header" style="border-bottom:none;padding-bottom:0;margin-bottom:2px;border-left:3px solid #2c3e50;padding-left:4px;">
                            {{ $p?->titulo ?? 'N/A' }}
                        </div>

                        {{-- Resumen --}}
                        @if($p && $p->resumen)
                            <div class="lbl">Resumen</div>
                            <div class="resumen-text">{{ Str::limit(strip_tags($p->resumen), 350) }}</div>
                        @endif

                        {{-- Lapso y Equipo --}}
                        @if($p)
                            @php
                                $lapsoTexto  = (isset($v->lapso) && $v->lapso && isset($lapsosNombres[$v->lapso])) ? $lapsosNombres[$v->lapso] : '';
                                $equipoTexto = $p->equipo_ref ? ($p->equipo_resumen ?? $p->equipo_ref) : '';
                            @endphp
                            @if($lapsoTexto || $equipoTexto)
                                <table style="width:100%;border-collapse:collapse;margin-bottom:2px;margin-top:2px;">
                                    <tr>
                                        @if($lapsoTexto)
                                            <td style="width:50%;font-size:7.5pt;padding:0 4px 0 0;vertical-align:top;">
                                                <span class="lbl">Lapso:</span> {{ $lapsoTexto }}
                                            </td>
                                        @endif
                                        @if($equipoTexto)
                                            <td style="width:50%;font-size:7.5pt;padding:0;vertical-align:top;">
                                                <span class="lbl">Equipo:</span> {{ $equipoTexto }}
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            @endif
                        @endif

                        {{-- Integrantes --}}
                        @if(isset($v->integrantes) && $v->integrantes->isNotEmpty())
                            <div class="lbl" style="margin-top:2px;">Integrantes del equipo</div>
                            <table class="integrantes-table">
                                <thead>
                                    <tr>
                                        <th style="width:18px;text-align:center;">N&deg;</th>
                                        <th>Apellidos y Nombres</th>
                                        <th style="width:78px;text-align:center;">Cedula</th>
                                        <th style="width:56px;text-align:center;">Rol</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($v->integrantes as $integrante)
                                        @php
                                            $fn = trim(($integrante->apellido ?? '') . ', ' . ($integrante->nombre ?? ''));
                                            if (trim($fn) === ',') $fn = trim(($integrante->nombre ?? '') . ' ' . ($integrante->apellido ?? ''));
                                            $rol = ($integrante->rol ?? '') === 'Lider' ? 'Lider' : 'Int.';
                                        @endphp
                                        <tr>
                                            <td style="text-align:center;">{{ $loop->iteration }}</td>
                                            <td>{{ strtoupper($fn) }}</td>
                                            <td style="text-align:center;">{{ $integrante->cedula }}</td>
                                            <td style="text-align:center;{{ $rol === 'Lider' ? 'font-weight:bold;' : '' }}">{{ $rol }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        {{-- Info adicional --}}
                        @if($p)
                            @php
                                $extras = [];
                                if ($p->linea_investigacion) $extras[] = 'Linea: ' . ($p->linea_investigacion->nombre_investigacion ?? '');
                                if ($p->creador_cedula)      $extras[] = 'Creador C.I.: ' . $p->creador_cedula;
                            @endphp
                            @if(!empty($extras))
                                <div class="extra-info">{{ implode('  |  ', $extras) }}</div>
                            @endif
                        @endif
                    </div>
                @endforeach
            @endforeach

            <div class="section-footer">
                {{ $vinculacionesDelGrupo->count() }} proyecto(s) vinculado(s) bajo este titulo
            </div>
        </div>
    @endforeach
@endif

<div class="footer">
    Sistema de Gestion de Proyectos Socio-Tecnologicos - PNFI &nbsp;|&nbsp; Total: {{ $vinculaciones->count() }} proyecto(s)
</div>
</body>
</html>
