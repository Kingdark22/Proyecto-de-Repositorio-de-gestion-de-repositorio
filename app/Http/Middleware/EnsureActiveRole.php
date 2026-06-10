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

        if ($roleService->getActiveRole($user) !== null) {
            if (! $roleService->rolActivoSigueSiendoValido($user)) {
                $roleService->clearActiveRole();

                return redirect()
                    ->route('acceso-rol.index')
                    ->with('message_error', 'Su rol en sesión ya no es válido (revise inscripción o asignación docente en intranet).');
            }

            return $next($request);
        }

        if ($roleService->allowsFreeSessionRoles()) {
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

        return redirect()->route('acceso-rol.index');
    }
}
