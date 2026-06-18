<?php

namespace App\Http\Middleware;

use App\Services\UserRoleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $roleService = app(UserRoleService::class);

        // Si es una petición AJAX/Livewire, nunca redirigir (rompe la UI)
        $isAjax = $request->ajax() || $request->wantsJson();

        // Intentar obtener el rol activo — ahora con restauración desde cache persistente
        try {
            $activeRole = $roleService->getActiveRole($user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('EnsureActiveRole: error obteniendo rol activo: ' . $e->getMessage());
            if ($isAjax) {
                return $next($request);
            }
            return redirect()->route('acceso-rol.index');
        }

        if ($activeRole !== null) {
            try {
                if (! $roleService->rolActivoSigueSiendoValido($user)) {
                    $roleService->clearActiveRole();
                    $roleService->clearPersistedActiveRole($user);

                    if ($isAjax) {
                        return $next($request);
                    }

                    return redirect()
                        ->route('acceso-rol.index')
                        ->with('message_error', 'Su rol en sesión ya no es válido (revise inscripción o asignación docente en intranet).');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('EnsureActiveRole: error validando rol: ' . $e->getMessage());
                // Si falla la validación, permitir continuar con el rol actual
            }

            return $next($request);
        }

        // No hay rol activo en sesión ni en cache — es un usuario nuevo o sesión expiró
        if ($roleService->allowsFreeSessionRoles()) {
            // Con free session roles, permitir acceso a la pantalla de selección de rol
            // pero no redirigir si es una petición AJAX (Livewire) para evitar UI rota
            if ($isAjax) {
                return $next($request);
            }

            // Evitar redirect loop: si ya estamos en acceso-rol, no redirigir
            if ($request->routeIs('acceso-rol.index')) {
                return $next($request);
            }

            return redirect()->route('acceso-rol.index');
        }

        try {
            $available = $roleService->detectAvailableRoles($user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('EnsureActiveRole: error detectando roles: ' . $e->getMessage());
            if ($isAjax) {
                return $next($request);
            }
            return redirect()->route('acceso-rol.index');
        }

        if ($available === []) {
            return $next($request);
        }

        if (count($available) === 1) {
            try {
                $roleService->setActiveRole($user, array_key_first($available));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('EnsureActiveRole: error asignando rol: ' . $e->getMessage());
            }
            return $next($request);
        }

        // Múltiples roles disponibles — redirigir a selección (excepto AJAX)
        if ($isAjax) {
            return $next($request);
        }

        if ($request->routeIs('acceso-rol.index')) {
            return $next($request);
        }

        return redirect()->route('acceso-rol.index');
    }
}
