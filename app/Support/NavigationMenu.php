<?php

namespace App\Support;

use App\Models\User;
use App\Services\NotificacionService;
use App\Services\ProyectoGestionService;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Cache;

/**
 * Permisos de menú según rol activo en sesión (sin depender de tablas roles locales).
 */
class NavigationMenu
{
    protected array $cache = [];

    protected const CACHE_TTL = 3600;

    public function __construct(
        protected UserRoleService $roles,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function flags(?User $user): array
    {
        if ($user === null) {
            return $this->emptyFlags();
        }

        $cacheKey = $user->usu_cedula . '_' . session($this->roles->sessionKey(), 'none');

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sessionKey = 'nav_flags_' . $cacheKey;
        $cached = Cache::get($sessionKey);
        if ($cached !== null) {
            $this->cache[$cacheKey] = $cached;
            return $cached;
        }

        $pendingUpdatesCount = app(NotificacionService::class)->contarPendientes($user);

        $flags = [
            'isAdmin'              => $this->roles->userHasRole($user, 'administrador'),
            'isCoordinator'        => $this->roles->userHasRole($user, 'coordinador'),
            'isTeacher'            => $this->roles->userHasRole($user, 'profesor proyecto'),
            'isStudent'            => $this->roles->userHasRole($user, 'estudiante'),
            'isGestionador'        => $this->roles->userHasRole($user, 'gestionador'),
            'canViewAcademic'      => $this->roles->userHasRole($user, 'administrador', 'coordinador', 'profesor proyecto', 'gestionador'),
            'canViewComunes'       => $this->roles->userHasRole($user, 'administrador', 'coordinador', 'profesor proyecto', 'estudiante', 'gestionador'),
            'canViewGruposProyecto'=> $this->roles->userHasRole($user, 'administrador', 'coordinador', 'profesor proyecto', 'gestionador'),
            'canManageCatalogs'    => $this->roles->userHasRole($user, 'administrador', 'coordinador', 'gestionador'),
            'canManageComponents'  => $this->roles->userHasRole($user, 'administrador', 'coordinador', 'gestionador'),
            'canValidateProjects'  => false,
            'canRegisterProject'   => $this->roles->userHasRole($user, 'administrador', 'gestionador')
                || ($this->roles->userHasRole($user, 'estudiante') && $user->puedeRegistrarProyecto()),
            'canManageSystemConfig'=> $this->roles->userHasRole($user, 'administrador', 'coordinador', 'gestionador'),
            'canViewPublicaciones'   => $this->roles->userHasRole($user, 'gestionador'),
            'pendingUpdatesCount'    => $pendingUpdatesCount,
        ];

        // canValidateProjects usa servicios especializados
        $flags['canValidateProjects'] = app(ProyectoGestionService::class)->usuarioPuedeValidar($user);

        Cache::put($sessionKey, $flags, now()->addSeconds(self::CACHE_TTL));

        $this->cache[$cacheKey] = $flags;

        return $flags;
    }

    /**
     * @return array<string, bool>
     */
    protected function emptyFlags(): array
    {
        return array_fill_keys([
            'isAdmin', 'isCoordinator', 'isTeacher', 'isStudent', 'isGestionador',
            'canViewAcademic', 'canViewComunes', 'canManageCatalogs',
            'canManageComponents', 'canValidateProjects', 'canRegisterProject',
            'canManageSystemConfig', 'canManageCoordinators',
            'canViewPublicaciones',
        ], false);
    }
}
