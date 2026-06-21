<?php
$nav = app(\App\Support\NavigationMenu::class)->flags(auth()->user());
$notificacionesList = app(\App\Services\NotificacionService::class)->listar(auth()->user());
$notificacionesCount = count($notificacionesList);
?>

<link rel="stylesheet" href="<?php echo e(asset('css/legacy-sidebar.css')); ?>">

<aside class="legacy-sidebar" id="menu_lateral">
    <nav class="legacy-nav">
        <ul>
            <li>
                <a href="<?php echo e(route('dashboard')); ?>"
                    class="legacy-menu-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    Inicio
                </a>
            </li>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canViewAcademic']): ?>
            <li>
                <div class="legacy-menu-item has-submenu">
                    Gestión académica
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canViewComunes']): ?>
                    <a href="<?php echo e(route('comunidades.index')); ?>"
                        class="<?php echo e(request()->routeIs('comunidades.index') ? 'active-sub' : ''); ?>">Comunidades</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canViewGruposProyecto']): ?>
                    <a href="<?php echo e(route('grupos-proyecto.index')); ?>"
                        class="<?php echo e(request()->routeIs('grupos-proyecto.index') ? 'active-sub' : ''); ?>">Equipos de
                        proyecto</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canManageCatalogs']): ?>
                    <a href="<?php echo e(route('lineas-investigacion')); ?>"
                        class="<?php echo e(request()->routeIs('lineas-investigacion') ? 'active-sub' : ''); ?>">Líneas de
                        investigación</a>
                    <a href="<?php echo e(route('tipos-investigacion')); ?>"
                        class="<?php echo e(request()->routeIs('tipos-investigacion') ? 'active-sub' : ''); ?>">Tipos de
                        investigación</a>
                    <a href="<?php echo e(route('metodologia-investigacion')); ?>"
                        class="<?php echo e(request()->routeIs('metodologia-investigacion') ? 'active-sub' : ''); ?>">Metodologías</a>
                    <a href="<?php echo e(route('tipos-publicacion')); ?>"
                        class="<?php echo e(request()->routeIs('tipos-publicacion') ? 'active-sub' : ''); ?>">Tipos de
                        publicación</a>
                    <a href="<?php echo e(route('objetivos-investigacion')); ?>"
                        class="<?php echo e(request()->routeIs('objetivos-investigacion') ? 'active-sub' : ''); ?>">Objetivos de
                        investigación</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canManageComponents']): ?>
                    <a href="<?php echo e(route('componentes.index')); ?>"
                        class="<?php echo e(request()->routeIs('componentes.index') ? 'active-sub' : ''); ?>">Componentes</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <li>
                <div class="legacy-menu-item has-submenu">
                    Proyectos
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="<?php echo e(route('proyectos.buscar')); ?>"
                        class="<?php echo e(request()->routeIs('proyectos.buscar') ? 'active-sub' : ''); ?>">Explorar proyectos</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canRegisterProject'] || $nav['canValidateProjects']): ?>
                    <a href="<?php echo e(route('proyectos.gestion')); ?>"
                        class="<?php echo e(request()->routeIs('proyectos.gestion', 'proyectos.crear', 'validaciones.index') ? 'active-sub' : ''); ?>">Deposito
                        proyecto</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </li>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canManageSystemConfig']): ?>
            <li>
                <div class="legacy-menu-item has-submenu">
                    Configuración
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="<?php echo e(route('profesores-proyecto.index')); ?>"
                        class="<?php echo e(request()->routeIs('profesores-proyecto.index') ? 'active-sub' : ''); ?>">Profesores
                        de proyecto</a>
                </div>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['isGestionador'] ?? false): ?>
            <li>
                <div class="legacy-menu-item has-submenu">
                    Vinculación
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="<?php echo e(route('vinculacion.index')); ?>"
                        class="<?php echo e(request()->routeIs('vinculacion.index') ? 'active-sub' : ''); ?>">
                        Vincular Proyectos
                    </a>
                </div>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nav['canViewPublicaciones'] ?? false): ?>
            <li>
                <div class="legacy-menu-item has-submenu">
                    Publicaciones
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="<?php echo e(route('publicaciones.index')); ?>"
                        class="<?php echo e(request()->routeIs('publicaciones.index') ? 'active-sub' : ''); ?>">
                        Proyectos Publicados
                    </a>
                    <a href="<?php echo e(route('publicaciones.publico')); ?>"
                        class="<?php echo e(request()->routeIs('publicaciones.publico') ? 'active-sub' : ''); ?>">
                        Vista P&uacute;blica
                    </a>
                </div>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <li>
                <div class="legacy-menu-item" style="padding: 6px 12px;">
                    Mi cuenta
                </div>
            </li>

            <li>
                <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin:0;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="legacy-menu-item" style="width: 100%; text-align: left;">
                        Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <div id="notificacionesContainer" class="notif-container">
        <div class="notif-card-header">
            <span class="notif-card-title">Notificaciones</span>
            <button type="button" onclick="toggleNotificaciones()" class="notif-bell-btn <?php echo e($notificacionesCount > 0 ? 'has-notifications' : ''); ?>">
                <i data-lucide="bell"></i>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notificacionesCount > 0): ?>
                <span class="notif-badge"><?php echo e($notificacionesCount); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
        </div>
        
        <div class="notif-card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notificacionesCount > 0): ?>
                <div class="notif-alert-box" onclick="toggleNotificaciones()" style="cursor:pointer;">
                    <i data-lucide="alert-circle" style="width:14px; height:14px; color:#d97706; flex-shrink:0;"></i>
                    <span style="font-size:10px; color:#78350f; font-weight:700;">Tienes <?php echo e($notificacionesCount); ?> pendiente(s)</span>
                </div>
            <?php else: ?>
                <div class="notif-ok-box">
                    <i data-lucide="check-circle" style="width:14px; height:14px; color:#16a34a; flex-shrink:0;"></i>
                    <span style="font-size:10px; color:#14532d; font-weight:700;">Todo al día</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div id="notificacionesDropdown" class="notif-dropdown">
            <div class="notif-header">
                <div class="notif-header-title">
                    <i data-lucide="bell" style="width:16px; height:16px; color:#8b0000;"></i>
                    <span>Notificaciones</span>
                </div>
                <span style="background:#f1f5f9; color:#475569; font-size:10px; padding:2px 6px; border-radius:10px; font-weight:600;">
                    <?php echo e($notificacionesCount); ?>

                </span>
            </div>
            <div class="notif-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notificacionesList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php
                    $iconName = 'info';
                    $iconClass = 'info';
                    if (($notif['type'] ?? '') === 'warning') {
                        $iconName = 'alert-triangle';
                        $iconClass = 'warning';
                    } elseif (($notif['type'] ?? '') === 'success') {
                        $iconName = 'check-circle';
                        $iconClass = 'success';
                    }
                ?>
                <a href="<?php echo e($notif['url']); ?>" class="notif-item">
                    <div class="notif-item-icon <?php echo e($iconClass); ?>">
                        <i data-lucide="<?php echo e($iconName); ?>" style="width:16px; height:16px;"></i>
                    </div>
                    <div class="notif-item-text">
                        <div class="notif-item-title"><?php echo e($notif['title'] ?? 'Aviso'); ?></div>
                        <div><?php echo e($notif['mensaje']); ?></div>
                    </div>
                </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="notif-empty">
                    <i data-lucide="bell-off" style="width:32px; height:32px; color:#94a3b8;"></i>
                    <span>No tienes notificaciones pendientes</span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</aside>

<script>
    function toggleNotificaciones() {
        var el = document.getElementById('notificacionesDropdown');
        var aberto = el.style.display !== 'none';
        el.style.display = aberto ? 'none' : 'block';
        var btn = el.parentNode.querySelector('button');
        if (btn) {
            var icon = btn.querySelector('[data-lucide]');
            if (icon) icon.style.transform = aberto ? 'rotate(0deg)' : 'rotate(15deg)';
        }
    }
    document.addEventListener('click', function(e) {
        var container = document.getElementById('notificacionesContainer');
        if (container && !container.contains(e.target)) {
            var dd = document.getElementById('notificacionesDropdown');
            if (dd) dd.style.display = 'none';
        }
    });

    function initSidebarAccordion() {
        document.querySelectorAll('.has-submenu').forEach(header => {
            const clone = header.cloneNode(true);
            header.parentNode.replaceChild(clone, header);
            clone.addEventListener('click', function() {
                const body = this.nextElementSibling;
                const arrow = this.querySelector('.arrow-icon');
                const open = body.style.maxHeight && body.style.maxHeight !== '0px';
                document.querySelectorAll('.legacy-submenu').forEach(b => {
                    b.style.maxHeight = '0px';
                    const a = b.previousElementSibling?.querySelector('.arrow-icon');
                    if (a) a.style.transform = 'rotate(0deg)';
                });
                if (!open) {
                    body.style.maxHeight = body.scrollHeight + 'px';
                    if (arrow) arrow.style.transform = 'rotate(90deg)';
                }
            });
        });
        const active = document.querySelector('.active-sub');
        if (active) {
            const body = active.closest('.legacy-submenu');
            const header = body?.previousElementSibling;
            if (header?.classList.contains('has-submenu')) {
                body.style.maxHeight = body.scrollHeight + 'px';
                header.querySelector('.arrow-icon')?.style.setProperty('transform', 'rotate(90deg)');
            }
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarAccordion);
    } else {
        initSidebarAccordion();
    }
    document.addEventListener('livewire:navigated', initSidebarAccordion);
</script><?php /**PATH C:\Users\tu hermana\Downloads\proyecto\Proyecto-de-Repositorio-de-gestion-de-repositorio\resources\views/components/sidebar.blade.php ENDPATH**/ ?>