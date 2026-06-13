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

        $activeRole = $this->roles->getActiveRole($user);
        $isStudent = $activeRole === 'estudiante';

        $pendingUpdatesCount = app(NotificacionService::class)->contarPendientes($user);

        $flags = [
            'isAdmin'              => $activeRole === 'administrador',
            'isCoordinator'        => $activeRole === 'coordinador',
            'isTeacher'            => $activeRole === 'profesor proyecto',
            'isStudent'            => $isStudent,
            'canViewAcademic'      => false,
            'canViewComunes'       => false,
            'canManageCatalogs'    => false,
            'canManageComponents'  => false,
            'canValidateProjects'  => false,
            'canRegisterProject'   => false,
            'canManageSystemConfig'=> false,
            'canManageOrganizaciones' => false,
            'canViewPublicaciones'   => false,
            'pendingUpdatesCount'    => $pendingUpdatesCount,
        ];

        if ($activeRole !== null) {
            switch ($activeRole) {
                case 'administrador':
                    $flags['canViewAcademic'] = true;
                    $flags['canViewComunes'] = true;
                    $flags['canManageCatalogs'] = true;
                    $flags['canManageComponents'] = true;
                    $flags['canManageSystemConfig'] = true;
                    $flags['canManageOrganizaciones'] = true;
                    $flags['canViewPublicaciones'] = true;
                    $flags['canRegisterProject'] = true;
                    break;

                case 'coordinador':
                    $flags['canViewAcademic'] = true;
                    $flags['canViewComunes'] = true;
                    $flags['canManageCatalogs'] = true;
                    $flags['canManageComponents'] = true;
                    $flags['canManageSystemConfig'] = true;
                    break;

                case 'profesor proyecto':
                    $flags['canViewAcademic'] = true;
                    $flags['canViewComunes'] = true;
                    break;

                case 'estudiante':
                    if ($user->perteneceAEquipo()) {
                        $flags['canViewAcademic'] = true;
                        $flags['canViewComunes'] = true;
                        $flags['canRegisterProject'] = true;
                    }
                    break;

                case 'gestionador':
                    $flags['canViewAcademic'] = true;
                    $flags['canViewComunes'] = true;
                    $flags['canManageCatalogs'] = true;
                    $flags['canManageComponents'] = true;
                    $flags['canManageSystemConfig'] = true;
                    $flags['canManageOrganizaciones'] = true;
                    $flags['canViewPublicaciones'] = true;
                    $flags['canRegisterProject'] = true;
                    break;
            }
        }

        // canValidateProjects y canRegisterProject usan servicios especializados
        $flags['canValidateProjects'] = app(ProyectoGestionService::class)->usuarioPuedeValidar($user);
        if ($flags['canRegisterProject'] === false) {
            $flags['canRegisterProject'] = $user->puedeRegistrarProyecto();
        }

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
            'isAdmin', 'isCoordinator', 'isTeacher', 'isStudent',
            'canViewAcademic', 'canViewComunes', 'canManageCatalogs',
            'canManageComponents', 'canValidateProjects', 'canRegisterProject',
            'canManageSystemConfig', 'canManageCoordinators',
            'canManageOrganizaciones',
            'canViewPublicaciones',
        ], false);
    }
}
