<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Repositorio UPTP - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('imagenes/uptp-logo.png')); ?>">
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

        /* ─── Modal de confirmación de registro ─── */
        .confirm-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.55);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            animation: confirmFadeIn 0.2s ease;
        }
        .confirm-modal-overlay.show { display: flex; }
        @keyframes confirmFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .confirm-modal-content {
            background: #fff;
            border-radius: 12px;
            max-width: 480px;
            width: 92%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: confirmSlideIn 0.25s ease;
            overflow: hidden;
        }
        @keyframes confirmSlideIn {
            from { transform: translateY(-20px) scale(0.97); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        .confirm-modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px 0;
        }
        .confirm-modal-icon {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: #fef3c7;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .confirm-modal-header h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #1e293b;
        }
        .confirm-modal-body {
            padding: 16px 24px 20px;
            font-size: 14px;
            color: #475569;
            line-height: 1.5;
        }
        .confirm-modal-body p { margin: 0 0 12px; }
        .confirm-modal-detail-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #19692e;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 10px 0 0;
        }
        .confirm-modal-detail-box .detail-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .confirm-modal-detail-box .detail-value {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }
        .confirm-modal-footer {
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 16px 24px 24px;
            border-top: 1px solid #f1f5f9;
        }

        /* Forzar mayúsculas en entradas de texto */
        input[type="text"], textarea {
            text-transform: uppercase;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div id="contenedor">
        <!-- Capa de Arriba -->
        <div id="arriba">
            <img src="<?php echo e(asset('imagenes/barras.jpeg')); ?>" alt="Encabezado Institucional" style="width: 100%; height: 100%; object-fit: fill; display: block;">
        </div>

        <!-- Menu Lateral (Sidebar) -->
        <div id="menu_lateral">
            <?php if (isset($component)) { $__componentOriginal2880b66d47486b4bfeaf519598a469d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2880b66d47486b4bfeaf519598a469d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $attributes = $__attributesOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $component = $__componentOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__componentOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>
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
            <?php if (! empty(trim($__env->yieldContent('header')))): ?>
            <div style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;">
                <h2 style="font-size: 20px; font-weight: bold; color: #333; margin: 0; text-align: left;"><?php echo $__env->yieldContent('header'); ?></h2>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        
        <div id="confirmModal" class="confirm-modal-overlay">
            <div class="confirm-modal-content">
                <div class="confirm-modal-header">
                    <div class="confirm-modal-icon" id="confirmModalIcon">📋</div>
                    <h3 id="confirmModalTitle">Confirmar acción</h3>
                </div>
                <div class="confirm-modal-body">
                    <p id="confirmModalMessage">¿Está seguro de realizar esta acción?</p>
                    <p id="confirmModalHint" style="font-size: 13px; color: #94a3b8;"></p>
                    <div class="confirm-modal-detail-box" id="confirmModalDetail">
                        <div class="detail-label" id="confirmDetailLabel">Nombre</div>
                        <div class="detail-value" id="confirmDetailValue"></div>
                    </div>
                </div>
                <div class="confirm-modal-footer">
                    <button type="button" onclick="cerrarModalConfirmacion()" class="cm-btn cm-btn-secondary" style="min-width: 120px;">Cancelar</button>
                    <button type="button" id="confirmActionBtn" class="cm-btn" style="min-width: 160px;">
                        <span id="confirmBtnText">Confirmar</span>
                    </button>
                </div>
            </div>
        </div>

        
        <?php
            $_flashMsg = session('message');
            $_flashErr = session('message_error');
            $_flashErr2 = session('error');
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($_flashMsg || $_flashErr || $_flashErr2): ?>
        <script>
        (function() {
            var type = 'info', msg = '';
            <?php if($_flashMsg): ?>
                type = 'success';
                msg = '<?php echo e(addslashes($_flashMsg)); ?>';
            <?php elseif($_flashErr): ?>
                type = 'error';
                msg = '<?php echo e(addslashes($_flashErr)); ?>';
            <?php elseif($_flashErr2): ?>
                type = 'error';
                msg = '<?php echo e(addslashes($_flashErr2)); ?>';
            <?php endif; ?>
            if (msg) {
                if (typeof showNotifyToast === 'function') {
                    setTimeout(function() { showNotifyToast(type, msg); }, 100);
                }
            }
        })();
        </script>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Capa de Abajo -->
        <div id="abajo">
            Todos los Derechos Reservados 2014 UPTP - Créditos Unidad de Sistemas / Desarrollo de Software.
        </div>
    </div>

    <script>
        // Heartbeat para mantener la sesión activa (cada 30 segundos)
        (function() {
            var keepaliveUrl = '<?php echo e(route('session.keepalive')); ?>';
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
    <script>
        function validarCorreo(el) {
            el.style.borderColor = el.value.includes('@') ? '#ccc' : 'red';
        }
        function validarNombre(el) {
            el.value = el.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ0-9\s]/g, '');
            var span = document.getElementById('nombreStatus');
            if (!span) return;
            var val = el.value.trim();
            if (val.length === 0) {
                span.style.display = 'inline'; span.style.color = '#dc3545'; span.textContent = 'El nombre no puede estar vacío';
                el.dataset.nombreOk = 'false';
                return;
            }
            if (val.length < 3) {
                span.style.display = 'inline'; span.style.color = 'orange'; span.textContent = 'Mínimo 3 caracteres';
                el.dataset.nombreOk = 'false';
                return;
            }
            span.style.display = 'inline'; span.style.color = '#888'; span.textContent = 'Verificando disponibilidad...';
            el.dataset.nombreOk = 'checking';
            var url = (el.dataset.checkUrl || '/comunidades/check-nombre') + '?nombre=' + encodeURIComponent(val);
            var ignore = el.form ? el.form.querySelector('[name="id_edit"]') : null;
            if (ignore && ignore.value) url += '&ignore_id=' + ignore.value;
            fetch(url).then(function(r) { return r.json(); }).then(function(data) {
                if (data.disponible) {
                    span.style.color = '#28a745'; span.textContent = '✓ Nombre disponible';
                    el.dataset.nombreOk = 'true';
                } else {
                    span.style.color = '#dc3545'; span.textContent = 'Este nombre ya está en uso';
                    el.dataset.nombreOk = 'false';
                }
            }).catch(function() {
                span.style.color = '#28a745'; span.textContent = '✓ Nombre disponible';
                el.dataset.nombreOk = 'true';
            });
        }
        function validarFormularioComunidad(form) {
            if (!form.reportValidity()) return false;
            var nombre = form.querySelector('[name="nombre"]');
            var span = document.getElementById('nombreStatus');
            if (!nombre || !nombre.value.trim()) {
                if (span) { span.style.display = 'inline'; span.style.color = '#dc3545'; span.textContent = 'El nombre es obligatorio'; }
                if (nombre) nombre.focus();
                return false;
            }
            if (nombre.dataset.nombreOk === 'false') {
                if (span) { span.style.display = 'inline'; span.style.color = '#dc3545'; span.textContent = 'Corrige el nombre antes de guardar'; }
                nombre.focus();
                return false;
            }
            if (nombre.dataset.nombreOk === 'checking') {
                if (span) { span.style.display = 'inline'; span.style.color = '#888'; span.textContent = 'Espera a que se verifique el nombre...'; }
                return false;
            }
            var correo = form.querySelector('[name="correo"]');
            var correoStatus = document.getElementById('correoStatus');
            if (correo && correo.value.trim().length >= 5) {
                if (correo.dataset.correoOk === 'false') {
                    if (correoStatus) { correoStatus.style.display = 'inline'; correoStatus.style.color = '#dc3545'; correoStatus.textContent = 'Corrige el correo antes de guardar'; }
                    correo.focus();
                    return false;
                }
                if (correo.dataset.correoOk === 'checking') {
                    if (correoStatus) { correoStatus.style.display = 'inline'; correoStatus.style.color = '#888'; correoStatus.textContent = 'Espera a que se verifique el correo...'; }
                    return false;
                }
            }
            var rifInput = form.querySelector('[name="rif_numero"]');
            var rifStatus = document.getElementById('rifStatus');
            if (rifInput && rifInput.value.trim().length > 0) {
                if (rifInput.dataset.rifOk === 'false') {
                    if (rifStatus) { rifStatus.style.display = 'inline'; rifStatus.style.color = '#dc3545'; rifStatus.textContent = 'Corrige el RIF antes de guardar'; }
                    rifInput.focus();
                    return false;
                }
                if (rifInput.dataset.rifOk === 'checking') {
                    if (rifStatus) { rifStatus.style.display = 'inline'; rifStatus.style.color = '#888'; rifStatus.textContent = 'Espera a que se verifique el RIF...'; }
                    return false;
                }
            }
            return true;
        }
        function validarCorreoRemoto(el) {
            var span = document.getElementById(el.dataset.statusSpan || 'correoStatus');
            if (!span) return;
            var val = el.value.trim();
            if (val.length === 0) {
                span.style.display = 'none';
                el.dataset.correoOk = '';
                return;
            }
            span.style.display = 'inline';
            if (val.includes('!')) {
                span.style.color = '#dc3545'; span.textContent = 'El correo no puede contener signos de exclamación';
                el.dataset.correoOk = 'false';
                return;
            }
            if (val.length < 5) {
                span.style.color = 'orange'; span.textContent = 'Mínimo 5 caracteres';
                el.dataset.correoOk = '';
                return;
            }
            if (!val.includes('@')) {
                span.style.color = 'orange'; span.textContent = 'Debe contener el símbolo @';
                el.dataset.correoOk = '';
                return;
            }
            if (val.indexOf('@') !== val.lastIndexOf('@')) {
                span.style.color = '#dc3545'; span.textContent = 'Solo debe haber un símbolo @';
                el.dataset.correoOk = 'false';
                return;
            }
            span.style.color = '#888';
            span.textContent = 'Verificando correo...';
            el.dataset.correoOk = 'checking';
            var url = (el.dataset.checkUrl || '/comunidades/check-email') + '?correo=' + encodeURIComponent(val);
            fetch(url).then(function(r) { return r.json(); }).then(function(data) {
                if (data.valido) {
                    span.style.color = '#28a745'; span.textContent = '✓ Correo válido';
                    el.dataset.correoOk = 'true';
                } else {
                    span.style.color = '#dc3545'; span.textContent = '✗ ' + (data.error || 'Correo inválido');
                    el.dataset.correoOk = 'false';
                }
            }).catch(function() {
                span.style.color = '#888'; span.textContent = 'No se pudo verificar';
                el.dataset.correoOk = '';
            });
        }
        function validarRif(el) {
            var span = document.getElementById(el.dataset.statusSpan || 'rifStatus');
            if (!span) return;
            var digitoSpan = el.dataset.digitoSpan ? document.getElementById(el.dataset.digitoSpan) : null;
            var letraSelect;
            if (el.dataset.selectId) {
                letraSelect = document.getElementById(el.dataset.selectId);
            } else {
                var form = el.closest('form');
                letraSelect = form ? form.querySelector('[name="rif_letra"]') : document.querySelector('[name="rif_letra"]');
            }
            if (!letraSelect) return;
            var numero = el.value.trim();
            var letra = letraSelect.value;
            if (numero.length === 0) {
                span.style.display = 'none';
                el.dataset.rifOk = '';
                if (digitoSpan) digitoSpan.textContent = '?';
                return;
            }
            span.style.display = 'inline';
            if (numero.length < 9) {
                span.style.color = '#dc3545'; span.textContent = 'RIF no válido';
                el.dataset.rifOk = 'false';
                if (digitoSpan) digitoSpan.textContent = '?';
                return;
            }
            span.style.color = '#888';
            span.textContent = 'Verificando RIF...';
            el.dataset.rifOk = 'checking';
            if (digitoSpan) digitoSpan.textContent = '...';
            var url = (el.dataset.checkUrl || '/comunidades/check-rif') + '?letra=' + encodeURIComponent(letra) + '&numero=' + encodeURIComponent(numero);
            fetch(url).then(function(r) { return r.json(); }).then(function(data) {
                if (data.valido) {
                    span.style.color = '#28a745'; span.textContent = '✓ RIF válido';
                    el.dataset.rifOk = 'true';
                    if (digitoSpan) digitoSpan.textContent = data.digito;
                } else {
                    span.style.color = '#dc3545'; span.textContent = '✗ ' + (data.error || 'RIF inválido');
                    el.dataset.rifOk = 'false';
                    if (digitoSpan) digitoSpan.textContent = '?';
                }
            }).catch(function() {
                span.style.color = '#888'; span.textContent = 'No se pudo verificar';
                el.dataset.rifOk = '';
                if (digitoSpan) digitoSpan.textContent = '?';
            });
        }
        function validarFormulario(form) {
            return form.reportValidity();
        }
        var _debounceTimers = {};
        function buscarConDebounce(el) {
            var form = el.closest('form') || document.getElementById('searchForm');
            var key = form.id || 'search';
            clearTimeout(_debounceTimers[key]);
            _debounceTimers[key] = setTimeout(function() {
                var form = el.closest('form') || document.getElementById('searchForm');
                if (!form) return;
                var url = new URL(form.action, window.location.origin);
                var fd = new FormData(form);
                fd.forEach(function(v, k) { url.searchParams.set(k, v); });
                url.searchParams.set('page', '1');
                fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.text(); })
                    .then(function(html) {
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        var newResults = doc.getElementById('searchResults');
                        if (newResults) {
                            var oldResults = document.getElementById('searchResults');
                            if (oldResults) oldResults.innerHTML = newResults.innerHTML;
                        }
                    });
            }, 50);
        }

        // Eliminación vía AJAX con modal de confirmación visual
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-ajax-delete]');
            if (!btn) return;
            e.preventDefault();
            var form = btn.closest('form');
            if (!form) return;

            var nombre = btn.getAttribute('data-delete-name') || 'este registro';
            var tr = btn.closest('tr');
            var originalText = btn.textContent;

            mostrarModalAccion({
                icon: '🗑️',
                title: 'Eliminar registro',
                message: '¿Está seguro de eliminar <strong>' + nombre + '</strong>?',
                hint: 'Esta acción no se puede deshacer.',
                detailLabel: 'Registro a eliminar',
                detailValue: nombre,
                confirmText: 'Sí, eliminar',
                confirmClass: 'cm-btn-danger',
                onConfirm: function() {
                    btn.disabled = true;
                    btn.textContent = '...';

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(function(r) {
                        var ct = r.headers.get('content-type') || '';
                        if (ct.indexOf('application/json') === -1) {
                            throw new Error('El servidor no devolvió JSON. Recargue la página e intente de nuevo.');
                        }
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            if (typeof showNotifyToast === 'function') {
                                showNotifyToast('success', data.message || 'Registro eliminado correctamente.');
                            }
                            if (tr) {
                                tr.style.transition = 'opacity 0.3s';
                                tr.style.opacity = '0';
                                setTimeout(function() {
                                    if (tr.parentNode) tr.parentNode.removeChild(tr);
                                }, 300);
                            }
                        } else {
                            btn.disabled = false;
                            btn.textContent = originalText;
                            if (typeof showNotifyToast === 'function') {
                                showNotifyToast('error', data.message || 'No se pudo eliminar el registro.');
                            }
                        }
                    })
                    .catch(function(err) {
                        console.error('Error en eliminación AJAX:', err);
                        btn.disabled = false;
                        btn.textContent = originalText;
                        if (typeof showNotifyToast === 'function') {
                            showNotifyToast('error', 'Error al eliminar: ' + err.message);
                        }
                    });
                }
            });
        });

        // ─── Modal de confirmación dinámico (reutilizable) ───
        // Uso: mostrarModalAccion({ icon, title, message, hint, detailLabel, detailValue, confirmText, confirmClass, onConfirm })
        function mostrarModalAccion(config) {
            var modal = document.getElementById('confirmModal');
            if (!modal) return;

            document.getElementById('confirmModalIcon').textContent = config.icon || '📋';
            document.getElementById('confirmModalTitle').textContent = config.title || 'Confirmar acción';
            document.getElementById('confirmModalMessage').innerHTML = config.message || '¿Está seguro de realizar esta acción?';
            document.getElementById('confirmModalHint').textContent = config.hint || '';
            document.getElementById('confirmDetailLabel').textContent = config.detailLabel || 'Nombre';
            document.getElementById('confirmDetailValue').textContent = config.detailValue || '';
            document.getElementById('confirmBtnText').textContent = config.confirmText || 'Confirmar';

            var confirmBtn = document.getElementById('confirmActionBtn');
            confirmBtn.className = 'cm-btn ' + (config.confirmClass || 'cm-btn-primary');

            modal._onConfirm = config.onConfirm || null;
            modal.classList.add('show');
        }

        function cerrarModalConfirmacion() {
            var modal = document.getElementById('confirmModal');
            if (modal) {
                modal.classList.remove('show');
                modal._onConfirm = null;
                modal._targetForm = null;
            }
        }

        // Evento para el botón confirmar del modal (acciones generales)
        document.addEventListener('click', function(e) {
            var confirmBtn = e.target.closest('#confirmActionBtn');
            if (!confirmBtn) return;

            var modal = document.getElementById('confirmModal');
            if (!modal || !modal.classList.contains('show')) return;

            // Si tiene un callback registrado (delete/toggle), ejecutarlo
            if (typeof modal._onConfirm === 'function') {
                modal._onConfirm();
                modal.classList.remove('show');
                modal._onConfirm = null;
                return;
            }

            // Si tiene un formulario pendiente (registro), enviarlo
            var form = modal._targetForm;
            if (form) {
                form.dataset.confirmedByModal = 'true';
                var origBtn = form.querySelector('[data-confirm-register]');
                if (origBtn) {
                    origBtn.disabled = true;
                    origBtn.textContent = 'Guardando...';
                }
                form.submit();
            }
            modal.classList.remove('show');
            modal._targetForm = null;
        });

        // Delegación de submit para formularios con data-confirm-register
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (form.tagName !== 'FORM') return;

            var btn = form.querySelector('[data-confirm-register]');
            if (!btn) return;

            if (form.dataset.confirmedByModal === 'true') {
                delete form.dataset.confirmedByModal;
                return;
            }

            if (e.defaultPrevented) return;

            e.preventDefault();

            var entityType = btn.getAttribute('data-entity-type') || 'registro';

            // Buscar el nombre del registro
            var nameInput = form.querySelector('[name="nombre"], [name="nombre_investigacion"], [name="titulo"]');
            var nameVal = '';
            if (nameInput && nameInput.value.trim()) {
                nameVal = nameInput.value.trim();
            } else {
                nameVal = '(nuevo ' + entityType.toLowerCase() + ')';
            }

            var modal = document.getElementById('confirmModal');
            modal._targetForm = form;

            mostrarModalAccion({
                icon: '📋',
                title: 'Guardar ' + entityType.toLowerCase(),
                message: '¿Está seguro de registrar <strong>' + nameVal + '</strong>?',
                hint: 'Revise los datos antes de confirmar. Una vez guardado podrá editarlo después.',
                detailLabel: entityType,
                detailValue: nameVal,
                confirmText: 'Sí, guardar',
                confirmClass: 'cm-btn-primary',
            });
        });

        // Cerrar modal al hacer clic fuera del contenido
        document.addEventListener('click', function(e) {
            var modal = document.getElementById('confirmModal');
            if (modal && modal.classList.contains('show') && e.target === modal) {
                cerrarModalConfirmacion();
            }
        });

        // Cerrar con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalConfirmacion();
            }
        });

        // ─── Toggle status via AJAX ───
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-ajax-toggle]');
            if (!btn) return;
            e.preventDefault();

            var url = btn.getAttribute('data-ajax-toggle');
            var name = btn.getAttribute('data-toggle-name') || 'registro';
            var actionLabel = btn.textContent.trim();
            var originalText = btn.textContent;

            mostrarModalAccion({
                icon: '🔄',
                title: 'Cambiar estado',
                message: '¿Está seguro de ' + actionLabel.toLowerCase() + ' <strong>' + name + '</strong>?',
                hint: 'El estado del registro se actualizará inmediatamente.',
                detailLabel: 'Registro',
                detailValue: name,
                confirmText: 'Sí, ' + actionLabel.toLowerCase(),
                confirmClass: 'cm-btn-warning',
                onConfirm: function() {
                    btn.disabled = true;
                    btn.textContent = '...';

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(function(r) {
                        var ct = r.headers.get('content-type') || '';
                        if (ct.indexOf('application/json') === -1) {
                            throw new Error('Respuesta inesperada del servidor.');
                        }
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            if (typeof showNotifyToast === 'function') {
                                showNotifyToast('success', data.message || 'Estado actualizado.');
                            }
                            // Refrescar los resultados de búsqueda vía AJAX
                            var searchForm = document.getElementById('searchForm');
                            if (searchForm) {
                                var inputs = searchForm.querySelectorAll('input, select');
                                var lastInput = null;
                                inputs.forEach(function(inp) {
                                    if (inp.name === 'search' || inp.name === 'buscar') {
                                        lastInput = inp;
                                    }
                                });
                                if (lastInput && typeof buscarConDebounce === 'function') {
                                    buscarConDebounce(lastInput);
                                } else {
                                    searchForm.submit();
                                }
                            } else {
                                window.location.reload();
                            }
                        } else {
                            btn.disabled = false;
                            btn.textContent = originalText;
                            if (typeof showNotifyToast === 'function') {
                                showNotifyToast('error', data.message || 'No se pudo cambiar el estado.');
                            }
                        }
                    })
                    .catch(function(err) {
                        console.error('Error en toggle AJAX:', err);
                        btn.disabled = false;
                        btn.textContent = originalText;
                        if (typeof showNotifyToast === 'function') {
                            showNotifyToast('error', 'Error: ' + err.message);
                        }
                    });
                }
            });
        });

        // ─── Paginación AJAX dentro de #searchResults ───
        document.addEventListener('click', function(e) {
            var link = e.target.closest('#searchResults a[href*="page="]');
            if (!link) return;
            // No interceptar botones de acción dentro de searchResults
            if (link.classList.contains('cm-btn') || link.closest('.cm-btn-group')) return;

            e.preventDefault();
            var url = link.href;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newResults = doc.getElementById('searchResults');
                if (newResults) {
                    var oldResults = document.getElementById('searchResults');
                    if (oldResults) oldResults.innerHTML = newResults.innerHTML;
                }
            })
            .catch(function() {
                window.location.href = url;
            });
        });
    </script>

    <script>
    // Convertir texto a mayúsculas en tiempo real
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[type="text"], textarea')) {
            var start = e.target.selectionStart;
            var end = e.target.selectionEnd;
            e.target.value = e.target.value.toUpperCase();
            e.target.setSelectionRange(start, end);
        }
    });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>

<?php /**PATH C:\Users\Emanuel\Desktop\Sistemax\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/layouts/app.blade.php ENDPATH**/ ?>