<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Seleccionar Rol - Repositorio UPTP</title>
    <link rel="icon" type="image/png" href="{{ asset('imagenes/uptp-logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #ffffff;
            font-family: "Verdana", Arial, sans-serif;
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }
        #contenedor {
            width: 650px;
            box-shadow: 0px 0px 15px #000000;
            border-radius: 15px;
            overflow: hidden;
        }
        #arriba {
            width: 100%;
            height: 90px;
            overflow: hidden;
        }
        #arriba img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }
        #cuerpo {
            padding: 40px 30px;
            text-align: center;
        }
        #cuerpo h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 8px;
        }
        #cuerpo p {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        .rol-card {
            display: block;
            width: 100%;
            padding: 18px 24px;
            margin-bottom: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
            font-size: 15px;
            font-family: inherit;
        }
        .rol-card:hover {
            border-color: #0072C6;
            background: #f0f7ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,114,198,0.15);
        }
        .rol-card:active {
            transform: translateY(0);
        }
        .rol-card .rol-label {
            font-weight: 700;
            color: #222;
            font-size: 17px;
        }
        .rol-card .rol-desc {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }
        .rol-card.selected {
            border-color: #0072C6;
            background: #e8f4fd;
        }
        #abajo {
            font-size: 14px;
            text-align: center;
            background-color: #E0ECF8;
            padding: 6px;
            border-top: 1px solid #b1b9c1;
        }
        .btn-continuar {
            display: none;
            margin: 20px auto 0;
            padding: 14px 40px;
            background: #0072C6;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-continuar:hover {
            background: #005a9e;
        }
        .btn-continuar.visible {
            display: inline-block;
        }
        .validation-error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div id="contenedor">
        <div id="arriba">
            <img src="{{ asset('imagenes/barras.jpeg') }}" alt="Encabezado Institucional">
        </div>
        <div id="cuerpo">
            <h2>Bienvenido al Sistema</h2>
            <p>Tu cédula tiene acceso con múltiples roles. Selecciona con cuál deseas ingresar:</p>

            <form id="rol-form" method="POST" action="{{ route('magic-login.aplicar-rol') }}">
                @csrf
                @foreach ($roles as $slug => $label)
                    <button type="button" class="rol-card" data-role="{{ $slug }}" onclick="seleccionarRol(this)">
                        <div class="rol-label">{{ $label }}</div>
                        <div class="rol-desc">
                            @switch($slug)
                                @case('estudiante')
                                    Acceso como estudiante - proyectos, grupos y seguimiento académico
                                    @break
                                @case('administrador')
                                    Acceso como administrador - configuración general del sistema
                                    @break
                                @case('profesor proyecto')
                                    Acceso como docente - tutoría de proyectos y grupos
                                    @break
                                @case('docente')
                                    Acceso como docente académico - vinculación académica general
                                    @break
                                @case('coordinador')
                                    Acceso como coordinador - supervisión y gestión académica
                                    @break
                                @default
                                    Acceso como {{ $label }}
                            @endswitch
                        </div>
                    </button>
                @endforeach

                <input type="hidden" name="role" id="selected-role" value="">

                @if ($errors->any())
                    <div class="validation-error">{{ $errors->first() }}</div>
                @endif

                <button type="submit" class="btn-continuar" id="btn-continuar">Continuar</button>
            </form>
        </div>
        <div id="abajo">
            Todos los Derechos Reservados 2014 UPTP - Créditos Unidad de Sistemas / Desarrollo de Software.
        </div>
    </div>

    <script>
        function seleccionarRol(btn) {
            document.querySelectorAll('.rol-card').forEach(function(c) {
                c.classList.remove('selected');
            });
            btn.classList.add('selected');
            document.getElementById('selected-role').value = btn.getAttribute('data-role');
            document.getElementById('btn-continuar').classList.add('visible');
        }
    </script>
</body>
</html>
