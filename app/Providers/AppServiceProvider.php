<?php

namespace App\Providers;

use App\Services\UserRoleService;
use App\Support\NavigationMenu;
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

        // (Global auto-uppercase removed per user request — values store as typed)
    }
}
