@php
$nav = app(\App\Support\NavigationMenu::class)->flags(auth()->user());
$notificacionesList = app(\App\Services\NotificacionService::class)->listar(auth()->user());
$notificacionesCount = count($notificacionesList);
@endphp

<link rel="stylesheet" href="{{ asset('css/legacy-sidebar.css') }}">

<aside class="legacy-sidebar" id="menu_lateral">
    <nav class="legacy-nav">
        <ul>
            <li>
                <a href="{{ route('dashboard') }}"
                    class="legacy-menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Inicio
                </a>
            </li>

            @if ($nav['canViewAcademic'])
            <li>
                <div class="legacy-menu-item has-submenu">
                    Gestión académica
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    @if ($nav['canViewComunes'])
                    <a href="{{ route('comunidades.index') }}"
                        class="{{ request()->routeIs('comunidades.index') ? 'active-sub' : '' }}">Comunidades</a>
                    @endif
                    @if ($nav['canViewGruposProyecto'])
                    <a href="{{ route('grupos-proyecto.index') }}"
                        class="{{ request()->routeIs('grupos-proyecto.index') ? 'active-sub' : '' }}">Equipos de
                        proyecto</a>
                    @endif

                    @if ($nav['canManageCatalogs'])
                    <a href="{{ route('lineas-investigacion') }}"
                        class="{{ request()->routeIs('lineas-investigacion') ? 'active-sub' : '' }}">Líneas de
                        investigación</a>
                    <a href="{{ route('tipos-investigacion') }}"
                        class="{{ request()->routeIs('tipos-investigacion') ? 'active-sub' : '' }}">Tipos de
                        investigación</a>
                    <a href="{{ route('metodologia-investigacion') }}"
                        class="{{ request()->routeIs('metodologia-investigacion') ? 'active-sub' : '' }}">Metodologías</a>
                    <a href="{{ route('objetivos-investigacion') }}"
                        class="{{ request()->routeIs('objetivos-investigacion') ? 'active-sub' : '' }}">Objetivos de
                        investigación</a>
                    @endif

                    @if ($nav['canManageComponents'])
                    <a href="{{ route('componentes.index') }}"
                        class="{{ request()->routeIs('componentes.index') ? 'active-sub' : '' }}">Componentes</a>
                    @endif
                </div>
            </li>
            @endif

            <li>
                <div class="legacy-menu-item has-submenu">
                    Proyectos
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('proyectos.buscar') }}"
                        class="{{ request()->routeIs('proyectos.buscar') ? 'active-sub' : '' }}">Explorar proyectos</a>
                    @if ($nav['canRegisterProject'] || $nav['canValidateProjects'] || ($nav['isCoordinator'] ?? false))
                    <a href="{{ route('proyectos.gestion') }}"
                        class="{{ request()->routeIs('proyectos.gestion', 'proyectos.crear', 'validaciones.index') ? 'active-sub' : '' }}">Depósito de proyectos</a>
                    @endif
                </div>
            </li>

            @if ($nav['canManageSystemConfig'])
            <li>
                <div class="legacy-menu-item has-submenu">
                    Gestión de Profesores
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('profesores-proyecto.index') }}"
                        class="{{ request()->routeIs('profesores-proyecto.index') ? 'active-sub' : '' }}">Profesores
                        de Proyecto</a>
                </div>
            </li>
            @endif
            
            @if ($nav['isGestionador'] ?? false)
            <li>
                <div class="legacy-menu-item has-submenu">
                    Vinculación
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('vinculacion.index') }}"
                        class="{{ request()->routeIs('vinculacion.index') ? 'active-sub' : '' }}">
                        Vincular Proyectos
                    </a>
                </div>
            </li>
            @endif

            <li>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="legacy-menu-item" style="width: 100%; text-align: left;">
                        Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <div id="notificacionesContainer" class="notif-container">
        <button type="button" onclick="toggleNotificaciones()" class="notif-bell-btn {{ $notificacionesCount > 0 ? 'has-notifications' : '' }}" id="notifBellBtn" title="Notificaciones">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            @if ($notificacionesCount > 0)
            <span class="notif-badge">{{ $notificacionesCount }}</span>
            @endif
        </button>
        
        <div id="notificacionesDropdown" class="notif-dropdown" style="display: none;">
            <div class="notif-header">
                <div class="notif-header-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;display:block;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span>Notificaciones</span>
                </div>
                <span class="notif-header-count">{{ $notificacionesCount }}</span>
            </div>
            <div class="notif-list">
                @forelse ($notificacionesList as $notif)
                @php
                    $dismissId = md5(($notif['proyecto_id'] ?? '') . '|' . ($notif['title'] ?? '') . '|' . ($notif['mensaje'] ?? ''));
                    $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
                    $itemType = 'item-info';
                    if (($notif['type'] ?? '') === 'warning') {
                        $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                        $itemType = 'item-warning';
                    } elseif (($notif['type'] ?? '') === 'success') {
                        $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                        $itemType = 'item-success';
                    } elseif (($notif['type'] ?? '') === 'danger') {
                        $itemType = 'item-danger';
                    }
                @endphp
                <a href="{{ $notif['url'] }}" class="notif-item {{ $itemType }}" data-dismiss-id="{{ $dismissId }}">
                    <div class="notif-item-icon">
                        {!! $iconSvg !!}
                    </div>
                    <div class="notif-item-text">
                        <div class="notif-item-title">{{ $notif['title'] ?? 'Aviso' }}</div>
                        <div class="notif-item-msg">{{ $notif['mensaje'] }}</div>
                    </div>
                    <button type="button" class="notif-dismiss" onclick="event.preventDefault();event.stopPropagation();dismissNotif('{{ $dismissId }}')" title="Descartar notificación">&times;</button>
                </a>
                @empty
                <div class="notif-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;opacity:0.35;"><path d="M13.73 21a2 2 0 0 1-3.46 0"/><path d="M18.63 13A17.89 17.89 0 0 1 18 8"/><path d="M6.26 6.26A5.86 5.86 0 0 0 6 8c0 7-3 9-3 9h14"/><path d="M18 8a6 6 0 0 0-9.33-5"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    <span>No tienes notificaciones pendientes</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</aside>

<script>
    function toggleNotificaciones() {
        var el = document.getElementById('notificacionesDropdown');
        var isOpen = el.style.display !== 'none';
        el.style.display = isOpen ? 'none' : 'block';
        var btn = document.getElementById('notifBellBtn');
        if (btn) {
            btn.classList.toggle('is-open', !isOpen);
        }
    }
    document.addEventListener('click', function(e) {
        var container = document.getElementById('notificacionesContainer');
        if (container && !container.contains(e.target)) {
            var dd = document.getElementById('notificacionesDropdown');
            if (dd) dd.style.display = 'none';
        }
    });

    // ─── Descartar notificaciones con localStorage ───
    function dismissNotif(id) {
        try {
            var dismissed = JSON.parse(localStorage.getItem('dismissedNotifs') || '[]');
            if (dismissed.indexOf(id) === -1) {
                dismissed.push(id);
                localStorage.setItem('dismissedNotifs', JSON.stringify(dismissed));
            }
        } catch(e) {}
        var items = document.querySelectorAll('[data-dismiss-id="' + id + '"]');
        items.forEach(function(item) {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(20px)';
            item.style.maxHeight = '0';
            item.style.padding = '0 14px';
            item.style.overflow = 'hidden';
            setTimeout(function() { item.style.display = 'none'; actualizarConteoNotifs(); }, 350);
        });
    }

    function actualizarConteoNotifs() {
        var visible = document.querySelectorAll('.notif-item[style*="display: none"]');
        var total = document.querySelectorAll('.notif-item');
        var restantes = total.length - visible.length;
        var badge = document.querySelector('.notif-badge');
        var headerCount = document.querySelector('.notif-header-count');
        var bellBtn = document.getElementById('notifBellBtn');

        if (restantes > 0) {
            if (badge) {
                badge.textContent = restantes;
            } else if (bellBtn) {
                var newBadge = document.createElement('span');
                newBadge.className = 'notif-badge';
                newBadge.textContent = restantes;
                bellBtn.appendChild(newBadge);
            }
            if (bellBtn) {
                bellBtn.classList.add('has-notifications');
            }
        } else {
            if (badge) badge.remove();
            if (bellBtn) {
                bellBtn.classList.remove('has-notifications');
            }
        }
        if (headerCount) headerCount.textContent = restantes;
    }

    // Limpiar notificaciones descartadas al cargar la página
    (function limpiarDescartadas() {
        try {
            var dismissed = JSON.parse(localStorage.getItem('dismissedNotifs') || '[]');
            if (dismissed.length > 0) {
                dismissed.forEach(function(id) {
                    var items = document.querySelectorAll('[data-dismiss-id="' + id + '"]');
                    items.forEach(function(item) { item.style.display = 'none'; });
                });
                actualizarConteoNotifs();
            }
        } catch(e) {}
    })();

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
</script>