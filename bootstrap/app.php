<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'active.role' => \App\Http\Middleware\EnsureActiveRole::class,
        ]);

        $middleware->encryptCookies(except: [
            'laravel-session',
        ]);

        $middleware->validateCsrfTokens(except: [
            'livewire/*',
            'livewire/message/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Illuminate\Database\QueryException $e) {
            \App\Helpers\DbHelper::handleQueryError($e);
        });
    })->create();
