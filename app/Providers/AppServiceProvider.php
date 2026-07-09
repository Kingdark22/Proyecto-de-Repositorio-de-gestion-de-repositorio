<?php

namespace App\Providers;

use App\Auth\ResilientUserProvider;
use App\Models\User;
use App\Services\UserRoleService;
use App\Support\NavigationMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRoleService::class);
        $this->app->singleton(NavigationMenu::class);
    }

    public function boot(): void
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            URL::forceRootUrl($scheme . $_SERVER['HTTP_HOST']);
        }

        // Registrar proveedor de auth resiliente
        Auth::provider('resilient', function ($app, array $config) {
            return new ResilientUserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Persiste los datos del usuario autenticado en cache + sesión,
     * para que ResilientUserProvider devuelva la fila exacta (con usu_nombre correcto)
     * en cada request, incluso si User::find() por usu_cedula encuentra otra fila.
     */
    public static function persistUserAuth(User $user): void
    {
        $cedula = trim($user->usu_cedula);
        $cacheKey = 'user_find_persisted_' . $cedula;
        Cache::put($cacheKey, $user->getAttributes(), now()->addHours(24));
        session(['user_backup_data_' . $cedula => $user->getAttributes()]);
        session(['auth_usu_nombre' => trim($user->usu_nombre ?? '')]);
    }
}
