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

            <li>
                <a href="{{ route('acceso-rol.index') }}"
                    class="legacy-menu-item {{ request()->routeIs('acceso-rol.index') ? 'active' : '' }}">
                    Acceder al Rol
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
                    <a href="{{ route('tipos-publicacion') }}"
                        class="{{ request()->routeIs('tipos-publicacion') ? 'active-sub' : '' }}">Tipos de
                        publicación</a>
                    <a href="{{ route('objetivos.index') }}"
                        class="{{ request()->routeIs('objetivos.index') ? 'active-sub' : '' }}">Objetivos</a>
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
                    @if ($nav['canRegisterProject'] || $nav['canValidateProjects'])
                    <a href="{{ route('proyectos.gestion') }}"
                        class="{{ request()->routeIs('proyectos.gestion', 'proyectos.crear', 'validaciones.index') ? 'active-sub' : '' }}">Cargar
                        proyecto</a>
                    @endif
                </div>
            </li>

            @if ($nav['canManageSystemConfig'])
            <li>
                <div class="legacy-menu-item has-submenu">
                    Configuración
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('profesores-proyecto.index') }}"
                        class="{{ request()->routeIs('profesores-proyecto.index') ? 'active-sub' : '' }}">Profesores
                        de proyecto</a>
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

            @if ($nav['canViewPublicaciones'] ?? false)
            <li>
                <div class="legacy-menu-item has-submenu">
                    Publicaciones de proyectos
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('publicaciones.index') }}"
                        class="{{ request()->routeIs('publicaciones.index') ? 'active-sub' : '' }}">
                        Proyectos subidos
                    </a>
                    <a href="{{ route('publicaciones.publico') }}"
                        class="{{ request()->routeIs('publicaciones.publico') ? 'active-sub' : '' }}">
                        Vista Pública
                    </a>
                </div>
            </li>
            @endif

            <li>
                <div class="legacy-menu-item has-submenu">
                    Mi cuenta
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('configuracion') }}"
                        class="{{ request()->routeIs('configuracion') ? 'active-sub' : '' }}">Perfil</a>
                </div>
            </li>

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
        <div class="notif-card-header">
            <span class="notif-card-title">Notificaciones</span>
            <button type="button" onclick="toggleNotificaciones()" class="notif-bell-btn {{ $notificacionesCount > 0 ? 'has-notifications' : '' }}">
                <i data-lucide="bell"></i>
                @if ($notificacionesCount > 0)
                <span class="notif-badge">{{ $notificacionesCount }}</span>
                @endif
            </button>
        </div>
        
        <div class="notif-card-body">
            @if ($notificacionesCount > 0)
                <div class="notif-alert-box" onclick="toggleNotificaciones()" style="cursor:pointer;">
                    <i data-lucide="alert-circle" style="width:14px; height:14px; color:#d97706; flex-shrink:0;"></i>
                    <span style="font-size:10px; color:#78350f; font-weight:700;">Tienes {{ $notificacionesCount }} pendiente(s)</span>
                </div>
            @else
                <div class="notif-ok-box">
                    <i data-lucide="check-circle" style="width:14px; height:14px; color:#16a34a; flex-shrink:0;"></i>
                    <span style="font-size:10px; color:#14532d; font-weight:700;">Todo al día</span>
                </div>
            @endif
        </div>

        <div id="notificacionesDropdown" class="notif-dropdown">
            <div class="notif-header">
                <div class="notif-header-title">
                    <i data-lucide="bell" style="width:16px; height:16px; color:#8b0000;"></i>
                    <span>Notificaciones</span>
                </div>
                <span style="background:#f1f5f9; color:#475569; font-size:10px; padding:2px 6px; border-radius:10px; font-weight:600;">
                    {{ $notificacionesCount }}
                </span>
            </div>
            <div class="notif-list">
                @forelse ($notificacionesList as $notif)
                @php
                    $iconName = 'info';
                    $iconClass = 'info';
                    if (($notif['type'] ?? '') === 'warning') {
                        $iconName = 'alert-triangle';
                        $iconClass = 'warning';
                    } elseif (($notif['type'] ?? '') === 'success') {
                        $iconName = 'check-circle';
                        $iconClass = 'success';
                    }
                @endphp
                <a href="{{ $notif['url'] }}" class="notif-item">
                    <div class="notif-item-icon {{ $iconClass }}">
                        <i data-lucide="{{ $iconName }}" style="width:16px; height:16px;"></i>
                    </div>
                    <div class="notif-item-text">
                        <div class="notif-item-title">{{ $notif['title'] ?? 'Aviso' }}</div>
                        <div>{{ $notif['mensaje'] }}</div>
                    </div>
                </a>
                @empty
                <div class="notif-empty">
                    <i data-lucide="bell-off" style="width:32px; height:32px; color:#94a3b8;"></i>
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
</script>