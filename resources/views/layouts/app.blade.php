<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        /* Estilos globales para errores de validación visuales */
        .validation-error {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            padding: 6px 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #dc2626;
            border-radius: 4px;
            color: #991b1b;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.4;
            box-shadow: 0 1px 3px rgba(220,38,38,0.06);
        }
        .validation-error::before {
            content: "⚠";
            font-size: 12px;
            flex-shrink: 0;
        }

        /* Alertas en línea reutilizables */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.5;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: relative;
        }
        .alert-icon {
            font-size: 20px;
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .alert-text {
            flex: 1;
        }
        .alert-close {
            flex-shrink: 0;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.6;
            padding: 0 4px;
            line-height: 1;
            transition: opacity 0.15s;
        }
        .alert-close:hover {
            opacity: 1;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            color: #166534;
        }
        .alert-success .alert-icon {
            background: #dcfce7;
            color: #16a34a;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        .alert-error .alert-icon {
            background: #fee2e2;
            color: #dc2626;
        }
        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #d97706;
            color: #92400e;
        }
        .alert-warning .alert-icon {
            background: #fef3c7;
            color: #d97706;
        }
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            color: #1e40af;
        }
        .alert-info .alert-icon {
            background: #dbeafe;
            color: #2563eb;
        }
    </style>
    @stack('styles')
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

        <!-- Toast de notificaciones -->
        <style>
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast {
            pointer-events: auto;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff;
            border-radius: 10px;
            padding: 14px 16px;
            min-width: 320px;
            max-width: 420px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            border-left: 4px solid #888;
            animation: toastIn 0.35s ease forwards;
            position: relative;
            overflow: hidden;
        }
        .toast-removing {
            animation: toastOut 0.3s ease forwards !important;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(60px) scale(0.95); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0) scale(1); }
            to { opacity: 0; transform: translateX(60px) scale(0.95); }
        }
        .toast-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            margin-top: 2px;
        }
        .toast-body { flex: 1; min-width: 0; }
        .toast-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .toast-msg {
            font-size: 13px;
            color: #475569;
            line-height: 1.4;
        }
        .toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #94a3b8;
            padding: 0 2px;
            line-height: 1;
            transition: color 0.15s;
        }
        .toast-close:hover { color: #475569; }
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            border-radius: 0 0 0 10px;
            animation: toastProgress 4s linear forwards;
        }
        @keyframes toastProgress {
            from { width: 100%; }
            to { width: 0%; }
        }
        .toast.toast-success { border-left-color: #16a34a; }
        .toast.toast-success .toast-icon { background: #dcfce7; color: #16a34a; }
        .toast.toast-success .toast-progress { background: #16a34a; }
        .toast.toast-error { border-left-color: #dc2626; }
        .toast.toast-error .toast-icon { background: #fee2e2; color: #dc2626; }
        .toast.toast-error .toast-progress { background: #dc2626; }
        .toast.toast-warning { border-left-color: #d97706; }
        .toast.toast-warning .toast-icon { background: #fef3c7; color: #d97706; }
        .toast.toast-warning .toast-progress { background: #d97706; }
        .toast.toast-info { border-left-color: #2563eb; }
        .toast.toast-info .toast-icon { background: #dbeafe; color: #2563eb; }
        </style>
        <div id="toast-container"></div>
        <script>
        function showNotifyToast(type, message) {
            type = type || 'info';
            const container = document.getElementById('toast-container');
            const icons = { success: '✔', error: '✘', warning: '⚠', info: 'ℹ' };
            const titles = { success: 'Operaci\u00f3n exitosa', error: 'Error', warning: 'Advertencia', info: 'Informaci\u00f3n' };
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.innerHTML =
                '<div class="toast-icon">' + (icons[type] || icons.info) + '</div>' +
                '<div class="toast-body">' +
                    '<div class="toast-title">' + (titles[type] || titles.info) + '</div>' +
                    '<div class="toast-msg">' + message + '</div>' +
                '</div>' +
                '<button class="toast-close" onclick="dismissToast(this.parentElement)">&times;</button>' +
                '<div class="toast-progress"></div>';
            container.appendChild(toast);
            var autoTimer = setTimeout(function() { dismissToast(toast); }, 4200);
            toast._autoTimer = autoTimer;
            toast.addEventListener('mouseenter', function() {
                clearTimeout(toast._autoTimer);
                var progress = toast.querySelector('.toast-progress');
                if (progress) { progress.style.animationPlayState = 'paused'; }
            });
            toast.addEventListener('mouseleave', function() {
                var progress = toast.querySelector('.toast-progress');
                if (progress) { progress.style.animationPlayState = 'running'; }
                toast._autoTimer = setTimeout(function() { dismissToast(toast); }, 2000);
            });
        }
        function dismissToast(toast) {
            if (!toast || toast.classList.contains('toast-removing')) return;
            clearTimeout(toast._autoTimer);
            toast.classList.add('toast-removing');
            setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
        }
        function closeNotifyModal() {} // stub legacy

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

        {{-- Auto-detectar session flashes y mostrarlos como modal visual --}}
        @php
            $_flashMsg = session('message');
            $_flashErr = session('message_error');
            $_flashErr2 = session('error');
        @endphp
        @if($_flashMsg || $_flashErr || $_flashErr2)
        <script>
        (function() {
            var type = 'info', msg = '';
            @if($_flashMsg)
                type = 'success';
                msg = '{{ addslashes($_flashMsg) }}';
            @elseif($_flashErr)
                type = 'error';
                msg = '{{ addslashes($_flashErr) }}';
            @elseif($_flashErr2)
                type = 'error';
                msg = '{{ addslashes($_flashErr2) }}';
            @endif
            if (msg) {
                if (typeof showNotifyToast === 'function') {
                    setTimeout(function() { showNotifyToast(type, msg); }, 100);
                }
            }
        })();
        </script>
        @endif

        <!-- Capa de Abajo -->
        <div id="abajo">
            Todos los Derechos Reservados 2014 UPTP - Créditos Unidad de Sistemas / Desarrollo de Software.
        </div>
    </div>

    <script>
        // Heartbeat para mantener la sesión activa (cada 30 segundos)
        (function() {
            var keepaliveUrl = '{{ route('session.keepalive') }}';
            var keepaliveInterval = 30000; // 30 segundos
            var keepaliveRetryDelay = 5000; // 5 segundos si falla

            function sessionKeepalive() {
                fetch(keepaliveUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                }).then(function(response) {
                    if (!response.ok) {
                        console.warn('Keepalive responded with', response.status);
                    }
                }).catch(function(err) {
                    console.warn('Keepalive falló, reintentando en 5s:', err);
                    // Reintentar más rápido si falló
                    setTimeout(sessionKeepalive, keepaliveRetryDelay);
                });
            }

            // Iniciar heartbeat
            window._keepaliveInterval = setInterval(sessionKeepalive, keepaliveInterval);

            // Reiniciar timer en cada interacción del usuario
            document.addEventListener('click', function() {
                if (window._keepaliveInterval) {
                    clearInterval(window._keepaliveInterval);
                }
                window._keepaliveInterval = setInterval(sessionKeepalive, keepaliveInterval);
            });

            // También reiniciar en teclas y scroll
            document.addEventListener('keydown', function() {
                if (window._keepaliveInterval) {
                    clearInterval(window._keepaliveInterval);
                }
                window._keepaliveInterval = setInterval(sessionKeepalive, keepaliveInterval);
            });

            // Detectar cuando la página recupera el foco (vuelta de inactividad larga)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    // La página está visible otra vez - hacer keepalive inmediato
                    sessionKeepalive();
                    if (window._keepaliveInterval) {
                        clearInterval(window._keepaliveInterval);
                    }
                    window._keepaliveInterval = setInterval(sessionKeepalive, keepaliveInterval);
                }
            });
        })();

        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>

