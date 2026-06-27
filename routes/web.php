<?php

use App\Http\Controllers\LineaInvestigacionController;
use App\Http\Controllers\MagicLoginController;
use App\Http\Controllers\MetodologiaInvestigacionController;
use App\Http\Controllers\ObjetivoInvestigacionController;
use App\Http\Controllers\TipoInvestigacionController;
use App\Http\Controllers\TipoPublicacionController;
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



Route::middleware(['auth', 'active.role'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/configuracion', 'configuracion.index')->name('configuracion');

    Route::middleware('role:administrador,coordinador,gestionador')->group(function () {
        Route::get('/lineas-investigacion', [LineaInvestigacionController::class, 'index'])->name('lineas-investigacion');
        Route::get('/tipos-investigacion', [TipoInvestigacionController::class, 'index'])->name('tipos-investigacion');
        Route::get('/objetivos-investigacion', [ObjetivoInvestigacionController::class, 'index'])->name('objetivos-investigacion');
        Route::get('/metodologia-investigacion', [MetodologiaInvestigacionController::class, 'index'])->name('metodologia-investigacion');
        Route::get('/tipos-publicacion', [TipoPublicacionController::class, 'index'])->name('tipos-publicacion');
    });

    Route::redirect('/lapsos-academicos', '/dashboard')->name('lapsos-academicos');

    Route::view('/proyectos/buscar', 'proyectos.buscar')->name('proyectos.buscar');

    Route::middleware('role:administrador,coordinador,profesor proyecto,gestionador,estudiante,docente')->group(function () {
        Route::redirect('/proyectos', '/proyectos/gestion')->name('proyectos.index');
        // Comunidades MVC
        Route::controller(\App\Http\Controllers\ComunidadController::class)->group(function () {
            Route::get('/comunidades', 'index')->name('comunidades.index');
            Route::get('/comunidades/crear', 'create')->name('comunidades.create');
            Route::post('/comunidades', 'store')->name('comunidades.store');
            Route::get('/comunidades/{id}/editar', 'edit')->name('comunidades.edit');
            Route::put('/comunidades/{id}', 'update')->name('comunidades.update');
            Route::delete('/comunidades/{id}', 'destroy')->name('comunidades.destroy');
        });
        Route::get('/comunidades/municipios/{estadoId}', [\App\Http\Controllers\ComunidadController::class, 'municipios']);

        // Grupos de Proyecto
        Route::controller(\App\Http\Controllers\GrupoProyectoController::class)->group(function () {
            Route::get('/grupos-proyecto', 'index')->name('grupos-proyecto.index');
            Route::get('/grupos-proyecto/crear', 'create')->name('grupos-proyecto.create');
            Route::post('/grupos-proyecto', 'store')->name('grupos-proyecto.store');
            Route::get('/grupos-proyecto/{id}/editar', 'edit')->name('grupos-proyecto.edit');
            Route::put('/grupos-proyecto/{id}', 'update')->name('grupos-proyecto.update');
            Route::delete('/grupos-proyecto/{id}', 'destroy')->name('grupos-proyecto.destroy');
            // API endpoints JSON para cascading selects
            Route::get('/grupos-proyecto/api/programas/{lapso}', 'getProgramas')->name('grupos-proyecto.api.programas');
            Route::get('/grupos-proyecto/api/secciones/{lapso}/{programa?}', 'getSecciones')->name('grupos-proyecto.api.secciones');
            Route::get('/grupos-proyecto/api/estudiantes/{lapso}/{seccion}', 'getEstudiantes')->name('grupos-proyecto.api.estudiantes');
            Route::get('/grupos-proyecto/api/trayectos/{lapso}/{programa}', 'getTrayectos')->name('grupos-proyecto.api.trayectos');
            // API endpoint para validar disponibilidad de nombre en tiempo real
            Route::get('/grupos-proyecto/api/check-nombre/{lapso}/{nombre}', 'checkNombreDisponible')->name('grupos-proyecto.api.check-nombre');
            // AJAX endpoint para crear comunidad desde el formulario
            Route::post('/grupos-proyecto/api/crear-comunidad', 'crearComunidadAjax')->name('grupos-proyecto.api.crear-comunidad');
        });
    });

    Route::middleware('role:administrador,estudiante,coordinador,profesor proyecto,gestionador,docente')->controller(\App\Http\Controllers\ProyectoController::class)->group(function () {
        Route::get('/proyectos/gestion', 'index')->name('proyectos.gestion');
        Route::get('/proyectos/gestion/{id}/editar', 'edit')->name('proyectos.gestion.edit');
        Route::put('/proyectos/gestion/{id}', 'update')->name('proyectos.gestion.update');
        Route::get('/proyectos/gestion/{id}/toggle', 'toggleStatus')->name('proyectos.gestion.toggle');
        Route::get('/proyectos/gestion/{id}/aprobar', 'approve')->name('proyectos.gestion.approve');
        Route::post('/proyectos/gestion/{id}/rechazar', 'reject')->name('proyectos.gestion.reject');
        Route::get('/proyectos/gestion/{id}/solvencia', 'solvencia')->name('proyectos.gestion.solvencia');
        Route::delete('/proyectos/gestion/{id}', 'destroy')->name('proyectos.gestion.destroy');
        Route::get('/proyectos/gestion/desde-grupo/{grpCodigo}', 'registrarDesdeGrupo')->name('proyectos.gestion.desde-grupo');
        // Involucrados AJAX
        Route::get('/proyectos/gestion/{id}/involucrados/buscar', 'buscarInvolucrados')->name('proyectos.gestion.involucrados.buscar');
        Route::get('/proyectos/gestion/involucrados/buscar-persona', 'buscarPersonaPorCedula')->name('proyectos.gestion.involucrados.buscar-persona');
        Route::get('/proyectos/gestion/{id}/involucrados/roles', 'buscarRoles')->name('proyectos.gestion.involucrados.roles');
        Route::post('/proyectos/gestion/{id}/involucrados', 'agregarInvolucrado')->name('proyectos.gestion.involucrados.agregar');
        Route::post('/proyectos/gestion/{id}/involucrados/crear', 'crearInvolucrado')->name('proyectos.gestion.involucrados.crear');
        Route::delete('/proyectos/gestion/{id}/involucrados/{invId}', 'quitarInvolucrado')->name('proyectos.gestion.involucrados.quitar');
        Route::post('/proyectos/gestion/{id}/involucrados/{invId}/roles', 'agregarRolInvolucrado')->name('proyectos.gestion.involucrados.roles.asignar');
        Route::delete('/proyectos/gestion/{id}/involucrados/roles/{pivotId}/{rolId}', 'quitarRolInvolucrado')->name('proyectos.gestion.involucrados.roles.quitar');
        Route::post('/proyectos/gestion/involucrados/roles/crear', 'crearRol')->name('proyectos.gestion.involucrados.roles.crear');
    });
    Route::view('/publicaciones', 'publicaciones.index')->name('publicaciones.index')->middleware('role:gestionador');

    Route::view('/vinculacion', 'vinculacion.index')->name('vinculacion.index')->middleware('role:gestionador');

    Route::get('/proyectos/crear', function () {
        return redirect()->route('proyectos.gestion', request()->query());
    })->middleware('role:administrador,estudiante,coordinador,profesor proyecto,gestionador')->name('proyectos.crear');

    Route::get('/validaciones', function () {
        return redirect('/proyectos/gestion');
    })->middleware('role:gestionador,administrador,coordinador,profesor proyecto')->name('validaciones.index');

    Route::middleware('role:administrador,coordinador,gestionador')->group(function () {
        Route::view('/configuracion/profesores-proyecto', 'profesores_proyecto.index')->name('profesores-proyecto.index');

        // Componentes MVC
        Route::controller(\App\Http\Controllers\ComponenteController::class)->group(function () {
            Route::get('/configuracion/componentes', 'index')->name('componentes.index');
            Route::get('/configuracion/componentes/crear', 'create')->name('componentes.create');
            Route::post('/configuracion/componentes', 'store')->name('componentes.store');
            Route::get('/configuracion/componentes/{id}/editar', 'edit')->name('componentes.edit');
            Route::put('/configuracion/componentes/{id}', 'update')->name('componentes.update');
            Route::get('/configuracion/componentes/{id}/toggle', 'toggleStatus')->name('componentes.toggle');
            Route::delete('/configuracion/componentes/{id}', 'destroy')->name('componentes.destroy');
            // Redirigir URLs antiguas al index de componentes
            Route::redirect('/configuracion/componentes/gestion', '/configuracion/componentes')->name('componentes.manage');
            // Vinculación múltiple: seleccionar componentes y vincular a PNF + Trayectos
            Route::get('/configuracion/componentes/vinculacion', 'vinculacionGlobal')->name('componentes.vinculacion');
            Route::post('/configuracion/componentes/vinculacion/guardar', 'vinculacionStore')->name('componentes.vinculacion.guardar');
        });
    });

    // === Rutas MVC Tipo Publicación ===
    Route::middleware('role:administrador,coordinador,gestionador')->controller(TipoPublicacionController::class)->group(function () {
        Route::get('/tipos-publicacion/crear', 'create')->name('tipos-publicacion.create');
        Route::post('/tipos-publicacion', 'store')->name('tipos-publicacion.store');
        Route::get('/tipos-publicacion/{id}/editar', 'edit')->name('tipos-publicacion.edit');
        Route::put('/tipos-publicacion/{id}', 'update')->name('tipos-publicacion.update');
        Route::get('/tipos-publicacion/{id}/toggle', 'toggleStatus')->name('tipos-publicacion.toggle');
        Route::delete('/tipos-publicacion/{id}', 'destroy')->name('tipos-publicacion.destroy');
    });

    // === Rutas MVC Tipo Investigación ===
    Route::middleware('role:administrador,coordinador,gestionador')->controller(TipoInvestigacionController::class)->group(function () {
        Route::get('/tipos-investigacion/crear', 'create')->name('tipos-investigacion.create');
        Route::post('/tipos-investigacion', 'store')->name('tipos-investigacion.store');
        Route::get('/tipos-investigacion/{id}/editar', 'edit')->name('tipos-investigacion.edit');
        Route::put('/tipos-investigacion/{id}', 'update')->name('tipos-investigacion.update');
        Route::get('/tipos-investigacion/{id}/toggle', 'toggleStatus')->name('tipos-investigacion.toggle');
        Route::delete('/tipos-investigacion/{id}', 'destroy')->name('tipos-investigacion.destroy');
    });

    // === Rutas MVC Objetivo Investigación ===
    Route::middleware('role:administrador,coordinador,gestionador')->controller(ObjetivoInvestigacionController::class)->group(function () {
        Route::get('/objetivos-investigacion/crear', 'create')->name('objetivos-investigacion.create');
        Route::post('/objetivos-investigacion', 'store')->name('objetivos-investigacion.store');
        Route::get('/objetivos-investigacion/{id}/editar', 'edit')->name('objetivos-investigacion.edit');
        Route::put('/objetivos-investigacion/{id}', 'update')->name('objetivos-investigacion.update');
        Route::get('/objetivos-investigacion/{id}/toggle', 'toggleStatus')->name('objetivos-investigacion.toggle');
        Route::delete('/objetivos-investigacion/{id}', 'destroy')->name('objetivos-investigacion.destroy');
    });

    // === Rutas MVC Metodología Investigación ===
    Route::middleware('role:administrador,coordinador,gestionador')->controller(MetodologiaInvestigacionController::class)->group(function () {
        Route::get('/metodologia-investigacion/crear', 'create')->name('metodologia-investigacion.create');
        Route::post('/metodologia-investigacion', 'store')->name('metodologia-investigacion.store');
        Route::get('/metodologia-investigacion/{id}/editar', 'edit')->name('metodologia-investigacion.edit');
        Route::put('/metodologia-investigacion/{id}', 'update')->name('metodologia-investigacion.update');
        Route::get('/metodologia-investigacion/{id}/toggle', 'toggleStatus')->name('metodologia-investigacion.toggle');
        Route::delete('/metodologia-investigacion/{id}', 'destroy')->name('metodologia-investigacion.destroy');
    });

    // === Rutas MVC Líneas de Investigación ===
    Route::middleware('role:administrador,coordinador,gestionador')->controller(LineaInvestigacionController::class)->group(function () {
        Route::get('/lineas-investigacion/crear', 'create')->name('lineas-investigacion.create');
        Route::post('/lineas-investigacion', 'store')->name('lineas-investigacion.store');
        Route::get('/lineas-investigacion/{id}/editar', 'edit')->name('lineas-investigacion.edit');
        Route::put('/lineas-investigacion/{id}', 'update')->name('lineas-investigacion.update');
        Route::get('/lineas-investigacion/{id}/toggle', 'toggleStatus')->name('lineas-investigacion.toggle');
        Route::delete('/lineas-investigacion/{id}', 'destroy')->name('lineas-investigacion.destroy');
    });

});

Route::get('/documentos/{path}', function (string $path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return Storage::disk('public')->response($path);
})->where('path', '.*')->middleware(['auth', 'active.role'])->name('documentos.serve');

Route::get('/session/keepalive', function () {
    // Forzar escritura de la sesión en BD para refrescar last_activity
    request()->session()->save();
    
    return response()->json([
        'ok' => true,
        'csrf_token' => csrf_token(),
        'time' => now()->timestamp,
    ]);
})->middleware('auth')->name('session.keepalive');

Route::post('/logout', function () {
    $user = Auth::user();
    if ($user) {
        app(App\Services\UserRoleService::class)->clearPersistedActiveRole($user);
    }
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');
