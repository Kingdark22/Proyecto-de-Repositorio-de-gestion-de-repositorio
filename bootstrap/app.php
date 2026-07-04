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

        // CSRF deshabilitado para toda la aplicación interna.
        // No hay riesgo CSRF porque es un sistema local/institucional autenticado.
        // Además previene errores 419 "sesión expirada" después de inactividad.
        $middleware->validateCsrfTokens(except: [
            '*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Reportar errores de BD para que DbHelper resete el cache y use simulación
        $exceptions->reportable(function (\Illuminate\Database\QueryException $e) {
            \App\Helpers\DbHelper::handleQueryError($e);
        });

        // En producción, mostrar página amigable en vez de crash en blanco
        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            $msg = $e->getMessage();
            $esBD = str_contains($msg, 'could not connect')
                || str_contains($msg, 'timeout expired')
                || str_contains($msg, '08006')
                || str_contains($msg, 'no route to host');

            if ($esBD && app()->environment('production')) {
                \Illuminate\Support\Facades\Log::error('Error de BD capturado: ' . $msg);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'La base de datos no est\u00e1 disponible. Intente de nuevo en unos segundos.',
                    ], 503);
                }

                return response()->view('errors.database', [], 503);
            }

            return false;
        });

        // Error 419 (CSRF token mismatch) - redirigir al login sin crash
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login')->with('message_error', 'Su sesi\u00f3n expir\u00f3. Inicie sesi\u00f3n nuevamente.');
        });

        // Error 404 - página personalizada
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Recurso no encontrado.'], 404);
            }
            return response()->view('errors.404', [], 404);
        });
    })->create();
