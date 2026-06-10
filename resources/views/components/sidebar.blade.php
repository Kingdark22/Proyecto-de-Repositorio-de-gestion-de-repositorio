@php
$nav = app(\App\Support\NavigationMenu::class)->flags(auth()->user());
$user = auth()->user();
$notificacionesList = [];
if ($user) {
    $userRoleService = app(\App\Services\UserRoleService::class);
    $activeRole = $userRoleService->getActiveRole($user);
    $isAdmin = $nav['isAdmin'] || $nav['isCoordinator'];
    if ($isAdmin || (in_array('profesor proyecto', array_keys($user->availableRoles()), true))) {
        $proyectosActualizados = \App\Models\Proyecto::where('actualizado_por_estudiante', true)->get();
        foreach ($proyectosActualizados as $p) {
            $notificacionesList[] = ['mensaje' => 'Proyecto actualizado por el líder: ' . $p->titulo, 'url' => route('proyectos.gestion'), 'proyecto_id' => $p->id];
        }
    } elseif ($nav['isStudent']) {
        $cedula = trim($user->usu_cedula);
        $proyectos = \App\Models\Proyecto::where('actualizado_por_estudiante', false)->get();
        foreach ($proyectos as $p) {
            $clave = $p->equipo_ref ?? '';
            if ($clave === '') continue;
            $gruposSvc = app(\App\Services\GrupoProyectoService::class);
            $partes = $gruposSvc->parsearClave($clave);
            if (!$partes || ($partes['tipo'] ?? '') !== \App\Services\GrupoProyectoService::PREFIJO) continue;
            $grupo = \App\Models\GrupoProyectoModulo::find($partes['grp_codigo'] ?? 0);
            if (!$grupo) continue;
            $miembros = $grupo->grp_miembros ?? [];
            foreach ($miembros as $m) {
                if (trim($m['cedula'] ?? '') === $cedula && (int) ($m['rol_id'] ?? 0) === \App\Services\IntranetEquipoSeccionService::ROL_LIDER) {
                    $notificacionesList[] = ['mensaje' => 'Debe subir los documentos del proyecto: ' . $p->titulo, 'url' => route('proyectos.gestion'), 'proyecto_id' => $p->id];
                    break;
                }
            }
        }
    }
}
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

            <li style="position:relative;">
                <button type="button" onclick="toggleNotificaciones()" class="legacy-menu-item" style="display:flex; align-items:center; gap:8px; width:100%; text-align:left; font-size:13px; font-weight:900;">
                    <i data-lucide="bell" style="width:16px; height:16px; flex-shrink:0;"></i>
                    Notificaciones
                    @if ($nav['pendingUpdatesCount'] > 0)
                    <span style="background:#dc3545; color:#fff; border-radius:50%; min-width:18px; height:18px; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold; margin-left:auto;">{{ $nav['pendingUpdatesCount'] }}</span>
                    @endif
                </button>
                <div id="notificacionesDropdown" style="display:none; position:absolute; left:100%; top:0; background:#fff; border:1px solid #ccc; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,0.15); min-width:280px; z-index:9999; font-size:12px;">
                    <div style="padding:8px 12px; border-bottom:1px solid #eee; font-weight:bold; background:#f5f5f5; border-radius:6px 6px 0 0;">Notificaciones</div>
                    @forelse ($notificacionesList as $notif)
                    <a href="{{ $notif['url'] }}" style="display:block; padding:8px 12px; text-decoration:none; color:#333; border-bottom:1px solid #f0f0f0;">
                        <div>{{ $notif['mensaje'] }}</div>
                    </a>
                    @empty
                    <div style="padding:12px; color:#999; text-align:center;">Sin notificaciones</div>
                    @endforelse
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
</aside>

<script>
    function toggleNotificaciones() {
        var el = document.getElementById('notificacionesDropdown');
        var aberto = el.style.display !== 'none';
        document.querySelectorAll('.notif-dropdown').forEach(function(e) { e.style.display = 'none'; });
        el.style.display = aberto ? 'none' : 'block';
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#notificacionesDropdown') && !e.target.closest('button[onclick="toggleNotificaciones()"]')) {
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