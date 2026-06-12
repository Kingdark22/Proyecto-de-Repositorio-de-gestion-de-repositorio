@php
$nav = app(\App\Support\NavigationMenu::class)->flags(auth()->user());
$notificacionesList = app(\App\Services\NotificacionService::class)->listar(auth()->user());
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
                        class="{{ request()->routeIs('proyectos.gestion', 'proyectos.crear', 'validaciones.index') ? 'active-sub' : '' }}">Gestión
                        de proyectos</a>
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
            
            @if ($nav['canManageOrganizaciones'] ?? false)
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
                    Publicaciones
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('publicaciones.index') }}"
                        class="{{ request()->routeIs('publicaciones.index') ? 'active-sub' : '' }}">
                        Proyectos Publicados
                    </a>
                    <a href="{{ route('publicaciones.publico') }}"
                        class="{{ request()->routeIs('publicaciones.publico') ? 'active-sub' : '' }}">
                        Vista P&uacute;blica
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

    <div id="notificacionesContainer" style="position:relative; padding:8px; border-top:1px solid rgba(255,255,255,0.15); text-align:center;">
        <button type="button" onclick="toggleNotificaciones()" style="background:none; border:none; cursor:pointer; position:relative; padding:6px;">
            <i data-lucide="bell" style="width:20px; height:20px; color:rgba(255,255,255,0.8);"></i>
            @if ($nav['pendingUpdatesCount'] > 0)
            <span style="position:absolute; top:-2px; right:-2px; background:#dc3545; color:#fff; border-radius:50%; min-width:16px; height:16px; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:bold; line-height:1; padding:0 4px; box-shadow:0 1px 3px rgba(0,0,0,0.3);">{{ $nav['pendingUpdatesCount'] }}</span>
            @endif
        </button>
        <div id="notificacionesDropdown" style="display:none; position:absolute; bottom:100%; left:50%; transform:translateX(-50%); margin-bottom:8px; background:#fff; border:1px solid #ccc; border-radius:8px; box-shadow:0 -4px 16px rgba(0,0,0,0.2); min-width:300px; max-width:320px; z-index:9999; font-size:12px;">
            <div style="padding:10px 14px; border-bottom:1px solid #eee; font-weight:bold; background:#f5f5f5; border-radius:8px 8px 0 0; display:flex; align-items:center; gap:6px;">
                <i data-lucide="bell" style="width:14px; height:14px;"></i>
                Notificaciones
            </div>
            <div style="max-height:320px; overflow-y:auto;">
                @forelse ($notificacionesList as $notif)
                <a href="{{ $notif['url'] }}" style="display:block; padding:10px 14px; text-decoration:none; color:#333; border-bottom:1px solid #f0f0f0; transition:background 0.15s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                    <div style="font-size:12px;">{{ $notif['mensaje'] }}</div>
                </a>
                @empty
                <div style="padding:16px; color:#999; text-align:center;">Sin notificaciones</div>
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