<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repositorio UPTP - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('imagenes/uptp-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            /* Mismo fondo base o reset para emular el original */
            background: #ffffff;
            margin: 0;
            padding: 20px 0;
            display: flex;
            justify-content: center;
        }

        /*-----------------------------------------------------------------------
            Definicion de espacios de las capas proporcionadas por el usuario
        -----------------------------------------------------------------------*/
        #contenedor { /* Contenedor de las capas */
            border: 0 px #000000 dashed;
            height: 100%;
            width: 1010px;
            padding: 0px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 0px;
            margin-bottom: 0px;
            box-shadow: 0px 0px 15px #000000;
            -moz-box-shadow: 0px 0px 15px #000000;
            -moz-border-radius: 15px;
            border-radius: 15px;
            font-family: "Verdana";
            font-size: 14px;
        }

        #arriba { /* Capa superior, donde se coloca el logo de la empresa */
            /* En lugar del background, mantendremos la etiqueta img que pusimos antes para que funcione con el servidor local */
            width: 1010px;
            height: 90px; /* Original era 90px */
            float: left;
            border-radius: 15px;
            overflow: hidden; /* Para que la imagen respete el borde */
        }

        #menu_lateral {
            background-color: #FFFFFF;
            border: 2px solid #DADADA;
            border-radius: 10px;
            float: left;
            min-height: 400px;
            width: 230px;
            /* Opcional */
            margin-top: 5px; /* Bajado un poco mas */
            overflow: hidden;
        }

        #centro{/* Capa central donde se ubica los datos del sistema */
            color: black;
            background-color: transparent;
            float: left;
            width: 770px; /* 1010 total - 230 menu = 780, menos margenes da 770 approx */
            border: 2px solid #DADADA;
            border-radius: 10px;
            /* Opcional */
            margin-top: 5px; /* Alineado con el sidebar */
            margin-left: 5px; /* Separacion del menu */
            padding: 10px;
            box-sizing: border-box; /* Importante para que el padding no rompa el width */
        }

        #abajo {
            font-size: 14px;
            clear:both;
            text-align: center;
            background-color:#E0ECF8;
            border-radius:0px 0px 15px 15px;
            width:1010px;
            padding: 6px;
            box-sizing: border-box;
            border-top: 1px solid #b1b9c1;
            margin-top: 15px; /* Espacio extra luego del centro antes abajo, similar al padding de legacy */
        }

        /* ----- AGREGADOS DEL LEGACY CSS ESTILOS.CSS (2014) ----- */
        .titulo { text-align:center; font-size:20px; margin:0px; }
        legend { font-weight:bold; font-style:italic; }
        a { text-decoration: none; }
        td a:visited { color:#00E; }
        fieldset {
            border: 2px solid #A00;
            -moz-border-radius:5px;
            border-radius: 10px;
            -webkit-border-radius: 5px;
        }
        .obligatorio { color: #FF0000; font-weight: bold; width:auto; }

        /* Estilo que se le da a todos los combos de las vistas */
        select {
            display: inline-block;
            margin-top: 8px;
            padding: 3px;
            width: 190px;
            font-weight:normal;
            height: 28px;
            background-color: #FFFFFF;
            border: 1px solid #A9A9A9;
            color: #000000;
            border-radius: 2px;
        }
        select:focus{ background: #FFFFFF; border:1px solid #F00; box-shadow: 0 0 3px #aaa; outline: none; }

        /* Estilo que se le da a todos los input de las vistas */
        input, textarea {
            background-color: #FFFFFF;
            border: 1px solid #A9A9A9;
            color: #000000;
            padding: 3px 5px;
            margin-top: 8px;
            height: 28px;
            width: 170px;
            border-radius: 2px;
        }
        textarea { height: auto; }
        
        input:focus, textarea:focus { background: #fff; border:1px solid #F00; box-shadow: 0 0 3px #aaa; outline: none; }

        input[type=checkbox], input[type=radio] { 
            height: auto; 
            width: auto; 
            padding: 0; 
            margin-top: 0;
            border: none;
            background: transparent;
        }
        input[type=file] {
            border: none;
            background: transparent;
            padding: 0;
            height: auto;
        }

        .Texto tr:hover { background-color:#CCC; color: #000000; cursor:pointer; }
        
        .sobreado_filas a { color:#00F; text-decoration:underline; font-weight:bolder; }
        .sobreado_filas tr:hover a { cursor:pointer; text-decoration:underline; }

        .titulo_listado { font-weight:bold; }

        h5{ margin-left:15px; font-weight:normal; }

        .boton{
            height:30px;
            background-color: #0072C6;
            color: #FFFFFF;
            border: 0 none;
            padding: 0 15px;
            cursor: pointer;
            font-weight: bold;
        }

        .tabla tr:hover { background-color:#CCC; color: #000000; }
        a:hover { text-decoration:underline; cursor:pointer; }

        .columna_color_oscuro{ background-color:#C3E0E4; }
        .columna_color_claro{ background-color:#8FC4CB; }
    </style>
    @stack('styles')
    @livewireStyles
</head>
<body>
    <div id="contenedor">
        <!-- Capa de Arriba -->
        <div id="arriba">
            <img src="{{ asset('imagenes/barras.jpeg') }}" alt="Encabezado Institucional" style="width: 100%; height: 100%; object-fit: fill; display: block;">
        </div>

        <!-- Menu Lateral (Sidebar) -->
        <div id="menu_lateral">
            <x-sidebar />
        </div>

        <!-- Modal de notificaciones -->
        <style>
        @keyframes notifModalFadeIn {
            from { opacity: 0; transform: scale(0.92) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes notifModalOverlayIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        #notifyModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.45);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            animation: notifModalOverlayIn 0.2s ease;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
        }
        #notifyModalContent {
            background: #fff;
            border-radius: 12px;
            padding: 30px 35px 25px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0,0,0,0.25);
            position: relative;
            animation: notifModalFadeIn 0.25s ease;
        }
        #notifyModalIcon {
            font-size: 48px;
            line-height: 1;
            margin-bottom: 12px;
        }
        #notifyModalTitle {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1e293b;
        }
        #notifyModalMessage {
            font-size: 14px;
            color: #475569;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        #notifyModalCloseBtn {
            background: #8b0000;
            color: #fff;
            border: none;
            padding: 10px 32px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.15s ease, transform 0.15s ease;
        }
        #notifyModalCloseBtn:hover {
            background: #a00000;
            transform: translateY(-1px);
        }
        #notifyModalCloseBtn:active {
            transform: translateY(0);
        }
        .notif-modal-top-bar {
            position: absolute;
            top: 0; left: 20px; right: 20px;
            height: 4px;
            border-radius: 0 0 4px 4px;
        }
        </style>
        <div id="notifyModal">
            <div id="notifyModalContent">
                <div id="notifyModalTopBar" class="notif-modal-top-bar" style="background:#17a2b8;"></div>
                <div id="notifyModalIcon"></div>
                <div id="notifyModalTitle">Información</div>
                <div id="notifyModalMessage"></div>
                <button id="notifyModalCloseBtn" onclick="closeNotifyModal()">Aceptar</button>
            </div>
        </div>
        <script>
        function showNotifyModal(type, message) {
            const modal = document.getElementById('notifyModal');
            const icon = document.getElementById('notifyModalIcon');
            const title = document.getElementById('notifyModalTitle');
            const msg = document.getElementById('notifyModalMessage');
            const topBar = document.getElementById('notifyModalTopBar');
            modal.style.display = 'flex';
            let iconHtml, iconColor, barColor, titleText;
            switch(type) {
                case 'success':
                    iconHtml = '✔';
                    iconColor = '#16a34a';
                    barColor = '#16a34a';
                    titleText = 'Operación exitosa';
                    break;
                case 'error':
                    iconHtml = '✘';
                    iconColor = '#dc2626';
                    barColor = '#dc2626';
                    titleText = 'Error';
                    break;
                case 'warning':
                    iconHtml = '⚠';
                    iconColor = '#d97706';
                    barColor = '#d97706';
                    titleText = 'Advertencia';
                    break;
                default:
                    iconHtml = 'ℹ';
                    iconColor = '#2563eb';
                    barColor = '#2563eb';
                    titleText = 'Información';
            }
            icon.innerHTML = iconHtml;
            icon.style.color = iconColor;
            topBar.style.background = barColor;
            title.textContent = titleText;
            msg.innerHTML = message;
        }
        function closeNotifyModal() {
            document.getElementById('notifyModal').style.display = 'none';
        }
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (data) => {
                showNotifyModal(data.type || 'info', data.message || '');
            });
        });
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('notify', (data) => {
                showNotifyModal(data.type || 'info', data.message || '');
            });
        });
        </script>

        <!-- Main Content (Centro) -->
        <main id="centro">
            @hasSection('header')
            <div style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;">
                <h2 style="font-size: 20px; font-weight: bold; color: #333; margin: 0; text-align: left;">@yield('header')</h2>
            </div>
            @endif
            
            @yield('content')
        </main>

        <!-- Capa de Abajo -->
        <div id="abajo">
            Todos los Derechos Reservados 2014 UPTP - Créditos Unidad de Sistemas / Desarrollo de Software.
        </div>
    </div>

    @livewireScripts
    <script>
        // Heartbeat para mantener la sesión activa (cada 60 segundos)
        (function() {
            var keepaliveUrl = '{{ route('session.keepalive') }}';
            function sessionKeepalive() {
                fetch(keepaliveUrl, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store'
                }).catch(function() {
                    // Si falla el keepalive, reintentar en el próximo ciclo
                });
            }
            window._keepaliveInterval = setInterval(sessionKeepalive, 60000);
            document.addEventListener('click', function() {
                clearInterval(window._keepaliveInterval);
                window._keepaliveInterval = setInterval(sessionKeepalive, 60000);
            });
        })();

        lucide.createIcons();
        document.addEventListener('livewire:navigated', () => {
            lucide.createIcons();
        });
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                lucide.createIcons();
            });
        });

    </script>
</body>
</html>

