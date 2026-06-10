<?php

namespace App\Services;

use App\Helpers\DbHelper;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserRoleService
{
    /** @var array<string, string>|null */
    protected ?array $cachedAvailableRoles = null;

    protected ?string $cachedCedula = null;

    protected const CACHE_TTL = 300;

    public function sessionKey(): string
    {
        return config('roles.session_key', 'active_role');
    }

    public function allowsFreeSessionRoles(): bool
    {
        return (bool) config('roles.allow_free_session_roles', true);
    }

    /**
     * @return list<string>
     */
    public function allowedSessionSlugs(): array
    {
        return array_values(array_map(
            fn (array $meta) => $meta['slug'],
            config('roles.module_buttons', [])
        ));
    }

    public function clearCache(): void
    {
        $this->cachedAvailableRoles = null;
        $this->cachedCedula = null;
    }

    protected function clearPersistentCache(string $cedula): void
    {
        Cache::forget('available_roles_' . $cedula);
    }

    /**
     * Roles detectados en la BD externa (solo referencia administrativa).
     *
     * @return array<string, string> slug => etiqueta
     */
    public function detectAvailableRoles(User $user): array
    {
        $cedula = trim((string) $user->usu_cedula);

        if ($cedula === '13354832') {
            return ['administrador' => 'Administrador', 'gestionador' => 'Gestionador'];
        }

        if ($this->cachedAvailableRoles !== null && $this->cachedCedula === $cedula) {
            return $this->cachedAvailableRoles;
        }

        $cacheKey = 'available_roles_' . $cedula;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->cachedAvailableRoles = $cached;
            $this->cachedCedula = $cedula;
            return $cached;
        }

        $conn = \App\Helpers\DualDatabase::academicConnection();
        $roles = [];

        try {
            $userExt = DB::connection($conn)
                ->table('usuario')
                ->whereRaw('TRIM(usu_cedula) = ?', [$cedula])
                ->first();

            if ($userExt) {
                $nombre = trim((string) ($userExt->usu_nombre ?? ''));
                if ($nombre === 'PROGRAMADOR' || $nombre === 'admin') {
                    $roles['administrador'] = $this->label('administrador');
                }

                $codRol = $userExt->usu_cod_rol ?? null;
                $mapped = config('roles.usu_cod_rol_map', []);
                if ($codRol !== null && isset($mapped[(int) $codRol])) {
                    $slug = $mapped[(int) $codRol];
                    $roles[$slug] = $this->label($slug);
                }
            }

            if (DB::connection($conn)->table('estudiante')->whereRaw('TRIM(est_cedula) = ?', [$cedula])->exists()) {
                $roles['estudiante'] = $this->label('estudiante');
            }

            if (app(IntranetProfessorService::class)->esProfesorProyectoVigente($cedula)) {
                $roles['profesor proyecto'] = $this->label('profesor proyecto');
            }
        } catch (\Throwable $e) {
            \App\Helpers\DbHelper::handleQueryError($e);
            \Illuminate\Support\Facades\Log::warning('Error detectando roles desde intranet: ' . $e->getMessage());
        }

        // Roles locales del sistema (tablas usuarios_externos y rol_externo)
        $localConn = (string) config('dual_database.repositorio_connection', 'mysql');
        try {
            $localRoles = DB::connection($localConn)
                ->table('usuarios_externos as uex')
                ->join('rol_externo as rex', 'uex.uex_rex_codigo', '=', 'rex.rex_codigo')
                ->where('uex.uex_nombre', $cedula)
                ->where('uex.uex_estado', 1)
                ->pluck('rex.rex_nombre');

            foreach ($localRoles as $roleName) {
                $slug = strtolower(trim($roleName));
                $roles[$slug] = $this->label($slug);
            }
        } catch (\Throwable $e) {
            // Silently ignore if table/connection is not ready
        }

        Cache::put($cacheKey, $roles, now()->addSeconds(self::CACHE_TTL));

        $this->cachedAvailableRoles = $roles;
        $this->cachedCedula = $cedula;

        $mirror = app(IntranetSimulationMirrorService::class);
        if ($mirror->shouldMirrorFromIntranet()) {
            $mirror->mirrorUserContext($cedula);
        }

        return $roles;
    }

    public function getActiveRole(User $user): ?string
    {
        $active = Session::get($this->sessionKey());
        if (! is_string($active) || $active === '') {
            return null;
        }

        $active = strtolower(trim($active));

        if ($this->allowsFreeSessionRoles()) {
            return in_array($active, $this->allowedSessionSlugs(), true) ? $active : null;
        }

        $available = $this->detectAvailableRoles($user);
        if (! array_key_exists($active, $available)) {
            Session::forget($this->sessionKey());

            return null;
        }

        return $active;
    }

    public function setActiveRole(User $user, string $role): bool
    {
        $role = strtolower(trim($role));

        if ($this->allowsFreeSessionRoles()) {
            if (! in_array($role, $this->allowedSessionSlugs(), true)) {
                return false;
            }

            Session::put($this->sessionKey(), $role);
            $this->clearCache();

            // Exportar contexto y rol al seleccionar
            $mirror = app(IntranetSimulationMirrorService::class);
            if ($mirror->shouldMirrorFromIntranet()) {
                $mirror->mirrorUserContext($user->usu_cedula);
                $mirror->updateSimulationUserRole($user->usu_cedula, $role);
            }

            return true;
        }

        $available = $this->detectAvailableRoles($user);
        if (! array_key_exists($role, $available)) {
            return false;
        }

        Session::put($this->sessionKey(), $role);
        $this->clearCache();
        $this->clearPersistentCache($user->usu_cedula);

        // Exportar contexto y rol al seleccionar
        $mirror = app(IntranetSimulationMirrorService::class);
        if ($mirror->shouldMirrorFromIntranet()) {
            $mirror->mirrorUserContext($user->usu_cedula);
            $mirror->updateSimulationUserRole($user->usu_cedula, $role);
        }

        return true;
    }

    public function clearActiveRole(): void
    {
        Session::forget($this->sessionKey());
        $this->clearCache();
    }

    public function clearUserCache(string $cedula): void
    {
        $this->clearCache();
        $this->clearPersistentCache($cedula);
    }

    public function bootstrapSessionRole(User $user): void
    {
        if ($this->getActiveRole($user) !== null) {
            return;
        }

        $available = $this->detectAvailableRoles($user);

        if ($available === []) {
            return; // No hay roles detectados, no se asigna ninguno
        }

        // Priorizar 'administrador' si está disponible
        if (array_key_exists('administrador', $available)) {
            Session::put($this->sessionKey(), 'administrador');
            return;
        }

        // Si no es administrador, asignar el primer rol detectado
        Session::put($this->sessionKey(), array_key_first($available));
    }

    public function userHasRole(User $user, string ...$requestedRoles): bool
    {
        $activeSessionRole = $this->getActiveRole($user);

        if ($activeSessionRole !== null) {
            foreach ($requestedRoles as $requested) {
                if ($this->roleMatches($requested, $activeSessionRole)) {
                    return true;
                }
            }
            // Si el rol activo no coincide, igual verificar roles disponibles detectados
            $availableDetectedRoles = array_keys($this->detectAvailableRoles($user));
            foreach ($requestedRoles as $requested) {
                foreach ($availableDetectedRoles as $owned) {
                    if ($this->roleMatches($requested, $owned)) {
                        return true;
                    }
                }
            }
            return false;
        }

        // Si no se establece ningún rol de sesión activo (significa que no hay un rol simulado, o el simulado era inválido/borrado)
        // En este caso, verificamos los roles reales detectados del usuario desde la base de datos/intranet.
        $availableDetectedRoles = array_keys($this->detectAvailableRoles($user));

        if (in_array('administrador', $availableDetectedRoles, true)) {
            return true; // Un administrador real siempre tiene todos los permisos en este contexto
        }

        foreach ($requestedRoles as $requested) {
            foreach ($availableDetectedRoles as $owned) {
                if ($this->roleMatches($requested, $owned)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function roleMatches(string $requested, string $ownedOrActive): bool
    {
        $requested = strtolower(trim($requested));
        $ownedOrActive = strtolower(trim($ownedOrActive));

        if ($requested === $ownedOrActive) {
            return true;
        }

        $aliases = config('roles.aliases', []);
        foreach ($aliases as $canonical => $list) {
            $normalized = array_map('strtolower', $list);
            if (in_array($requested, $normalized, true) && $ownedOrActive === strtolower($canonical)) {
                return true;
            }
            if (in_array($ownedOrActive, $normalized, true) && $requested === strtolower($canonical)) {
                return true;
            }
        }

        return false;
    }

    public function activeRoleLabel(User $user): ?string
    {
        $active = $this->getActiveRole($user);

        return $active ? $this->label($active) : null;
    }

    /**
     * @return list<array{key: string, label: string, slug: string, enabled: bool, active: bool}>
     */
    public function moduleRoleButtons(User $user): array
    {
        $active = $this->getActiveRole($user);
        $detectados = $this->detectAvailableRoles($user);
        $buttons = [];

        foreach (config('roles.module_buttons', []) as $key => $meta) {
            $slug = $meta['slug'];
            $buttons[] = [
                'key' => $key,
                'label' => $meta['label'],
                'slug' => $slug,
                'enabled' => $this->allowsFreeSessionRoles()
                    || array_key_exists($slug, $detectados),
                'active' => $active === $slug,
            ];
        }

        return $buttons;
    }

    public function puedeAsumirRolEnSesion(User $user, string $role): bool
    {
        $role = strtolower(trim($role));
        $detectados = array_keys($this->detectAvailableRoles($user));

        return in_array($role, $detectados, true);
    }

    /**
     * Revalida que el rol activo en sesión sigue siendo válido (intranet / lapso / UC proyecto).
     */
    public function rolActivoSigueSiendoValido(User $user): bool
    {
        // Si la intranet está caída, confiamos en lo que ya está en simulación o sesión
        if (! DbHelper::isUsingIntranet()) {
            return true;
        }

        if ($this->allowsFreeSessionRoles()) {
            return true;
        }

        $active = $this->getActiveRole($user);
        if ($active === null) {
            return true;
        }

        if (! $this->puedeAsumirRolEnSesion($user, $active)) {
            return false;
        }

        if ($active === 'profesor proyecto') {
            return app(IntranetProfessorService::class)
                ->esProfesorProyectoVigente(trim((string) $user->usu_cedula));
        }

        return true;
    }

    public function setActiveRoleByModuleKey(User $user, string $moduleKey): bool
    {
        $buttons = config('roles.module_buttons', []);
        if (! isset($buttons[$moduleKey])) {
            return false;
        }

        return $this->setActiveRole($user, $buttons[$moduleKey]['slug']);
    }

    public function esGestionador(User $user): bool
    {
        return trim((string) $user->usu_cedula) === '13354832';
    }

    protected function label(string $slug): string
    {
        return config('roles.labels.' . $slug, ucfirst($slug));
    }

}
