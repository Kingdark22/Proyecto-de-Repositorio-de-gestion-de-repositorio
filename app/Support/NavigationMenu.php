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

        try {
            return $this->buildFlags($user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error building nav flags: ' . $e->getMessage());
            return $this->emptyFlags();
        }
    }

    /**
     * @return array<string, bool>
     */
    protected function buildFlags(User $user): array
    {
        $cacheKey = $user->usu_cedula . '_' . session($this->roles->sessionKey(), 'none');

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sessionKey = 'nav_flags_' . $cacheKey;
        try {
            $cached = Cache::get($sessionKey);
            if ($cached !== null) {
                $this->cache[$cacheKey] = $cached;
                return $cached;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NavFlags cache read error: ' . $e->getMessage());
        }

        $activeRole = $this->roles->getActiveRole($user);
        $isStudent = $activeRole === 'estudiante';

        $pendingUpdatesCount = 0;
        try {
            $pendingUpdatesCount = app(NotificacionService::class)->contarPendientes($user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NavFlags notificaciones error: ' . $e->getMessage());
        }

        $flags = [
            'isAdmin'              => $activeRole === 'administrador',
            'isCoordinator'        => $activeRole === 'coordinador',
            'isTeacher'            => $activeRole === 'profesor proyecto',
            'isDocente'            => $activeRole === 'docente',
            'isStudent'            => $isStudent,
            'isGestionador'        => $activeRole === 'gestionador',
            'canViewAcademic'      => false,
            'canViewComunes'       => false,
            'canManageCatalogs'    => false,
            'canManageComponents'  => false,
            'canValidateProjects'  => false,
            'canRegisterProject'   => false,
            'canManageSystemConfig'=> false,
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

                case 'docente':
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
                    $flags['canRegisterProject'] = true;
                    $flags['canViewPublicaciones'] = true;
                    break;
            }
        }

        // canValidateProjects y canRegisterProject usan servicios especializados
        try {
            $flags['canValidateProjects'] = app(ProyectoGestionService::class)->usuarioPuedeValidar($user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NavFlags canValidateProjects error: ' . $e->getMessage());
            $flags['canValidateProjects'] = false;
        }
        if ($flags['canRegisterProject'] === false) {
            try {
                $flags['canRegisterProject'] = $user->puedeRegistrarProyecto();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('NavFlags canRegisterProject error: ' . $e->getMessage());
            }
        }

        try {
            Cache::put($sessionKey, $flags, now()->addSeconds(self::CACHE_TTL));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NavFlags cache write error: ' . $e->getMessage());
        }

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
            'canManageSystemConfig',
            'canViewPublicaciones',
            'pendingUpdatesCount' => 0,
        ], false);
    }
}
