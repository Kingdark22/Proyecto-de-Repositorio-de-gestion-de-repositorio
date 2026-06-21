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

        // Obtener o re-asignar rol automáticamente
        $activeRole = $roleService->getActiveRole($user);

        if ($activeRole !== null) {
            if (! $roleService->rolActivoSigueSiendoValido($user)) {
                $roleService->clearActiveRole();
                $roleService->clearPersistedActiveRole($user);
                $roleService->bootstrapSessionRole($user);
            }
        } else {
            $roleService->bootstrapSessionRole($user);
        }

        return $next($request);
    }
}
