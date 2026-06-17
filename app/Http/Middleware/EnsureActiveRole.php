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

        // Intentar obtener el rol activo — ahora con restauración desde cache persistente
        $activeRole = $roleService->getActiveRole($user);

        if ($activeRole !== null) {
            if (! $roleService->rolActivoSigueSiendoValido($user)) {
                $roleService->clearActiveRole();
                $roleService->clearPersistedActiveRole($user);

                return redirect()
                    ->route('acceso-rol.index')
                    ->with('message_error', 'Su rol en sesión ya no es válido (revise inscripción o asignación docente en intranet).');
            }

            return $next($request);
        }

        // No hay rol activo en sesión ni en cache — es un usuario nuevo o sesión expiró
        if ($roleService->allowsFreeSessionRoles()) {
            // Con free session roles, permitir acceso a la pantalla de selección de rol
            // pero no redirigir si es una petición AJAX (Livewire) para evitar UI rota
            if ($request->ajax() || $request->wantsJson()) {
                return $next($request);
            }

            return redirect()->route('acceso-rol.index');
        }

        $available = $roleService->detectAvailableRoles($user);

        if ($available === []) {
            return $next($request);
        }

        if (count($available) === 1) {
            $roleService->setActiveRole($user, array_key_first($available));

            return $next($request);
        }

        // Múltiples roles disponibles — redirigir a selección (excepto AJAX)
        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        return redirect()->route('acceso-rol.index');
    }
}
