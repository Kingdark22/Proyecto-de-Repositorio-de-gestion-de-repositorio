<?php

use App\Http\Controllers\MagicLoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::view('/repositorio', 'repositorio')->name('repositorio');

Route::get('/magic-login', [MagicLoginController::class, 'login'])->name('magic-login');

Route::get('/login', function (\Illuminate\Http\Request $request) {
    if ($request->has('payload')) {
        return app(MagicLoginController::class)->login($request);
    }
    if ($request->has('token')) {
        return app(MagicLoginController::class)->login($request);
    }

    // Si ya está autenticado, redirigir al dashboard
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/acceso-por-rol', function () {
        return view('acceso_rol.index', (function () {
            $service = app(App\Services\UserRoleService::class);
            $user = auth()->user();
            return [
                'roleButtons' => $service->moduleRoleButtons($user),
                'activeRoleLabel' => $service->activeRoleLabel($user),
            ];
        })());
    })->name('acceso-rol.index');

    Route::get('/simular-rol/{moduleKey}', function (string $moduleKey) {
        $service = app(App\Services\UserRoleService::class);
        $user = auth()->user();
        if ($service->setActiveRoleByModuleKey($user, $moduleKey)) {
            app(App\Support\NavigationMenu::class)->flags($user);
        }
        return redirect()->route('dashboard');
    })->name('simular-rol');

    Route::redirect('/sesion/rol', '/acceso-por-rol');
});

Route::middleware(['auth', 'active.role'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/configuracion', 'configuracion.index')->name('configuracion');

    Route::middleware('role:administrador,estudiante,coordinador')->group(function () {
        Route::view('/lineas-investigacion', 'lineas.index')->name('lineas-investigacion');
        Route::view('/tipos-investigacion', 'tipo_investigacion.index')->name('tipos-investigacion');
        Route::view('/metodologia-investigacion', 'metodologia_investigacion.index')->name('metodologia-investigacion');
        Route::view('/tipos-publicacion', 'tipo_publicacion.index')->name('tipos-publicacion');
    });

    Route::redirect('/lapsos-academicos', '/dashboard')->name('lapsos-academicos');

    Route::view('/proyectos', 'proyectos.index')->name('proyectos.index');
    Route::view('/proyectos/buscar', 'proyectos.buscar')->name('proyectos.buscar');
    Route::view('/comunidades', 'comunidades.index')->name('comunidades.index');
    Route::view('/grupos-proyecto', 'grupos_proyecto.index')->name('grupos-proyecto.index');

    Route::middleware('role:administrador,estudiante,coordinador,profesor proyecto,gestionador')->group(function () {
        Route::view('/proyectos/gestion', 'proyectos.index')->name('proyectos.gestion');
    });

    Route::view('/publicaciones', 'publicaciones.index')->name('publicaciones.index')->middleware('role:gestionador');
    Route::view('/vinculacion', 'vinculacion.index')->name('vinculacion.index')->middleware('role:gestionador');

    Route::get('/proyectos/crear', function () {
        return redirect()->route('proyectos.gestion', request()->query());
    })->middleware('role:administrador,estudiante,coordinador,profesor proyecto,gestionador')->name('proyectos.crear');

    Route::get('/validaciones', function () {
        return redirect('/proyectos/gestion');
    })->middleware('role:administrador,coordinador,profesor proyecto,gestionador')->name('validaciones.index');

    Route::middleware('role:administrador,coordinador,gestionador')->group(function () {
        Route::view('/configuracion/profesores-proyecto', 'profesores_proyecto.index')->name('profesores-proyecto.index');
        Route::view('/configuracion/componentes', 'componentes.index')->name('componentes.index');
    });


});

Route::get('/documentos/{path}', function (string $path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return Storage::disk('public')->response($path);
})->where('path', '.*')->middleware('auth')->name('documentos.serve');

// Público: proyectos publicados visibles sin autenticación
Route::get('/publicaciones/publico', \App\Livewire\ProyectosPublicosManager::class)->name('publicaciones.publico');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');
