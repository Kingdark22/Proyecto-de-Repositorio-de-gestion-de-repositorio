<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Roles del sistema (clave => etiqueta visible)
    |--------------------------------------------------------------------------
    */
    'labels' => [
        'administrador'   => 'Administrador',
        'estudiante'      => 'Estudiante',
        'profesor proyecto' => 'Docente',
        'docente'          => 'Docente Académico',
        'coordinador'     => 'Coordinación',
        'gestionador'     => 'Gestionador',
    ],

    /*
    |--------------------------------------------------------------------------
    | Botones del módulo "Acceso por Rol"
    |--------------------------------------------------------------------------
    */
    'module_buttons' => [
        'estudiante'   => ['slug' => 'estudiante',        'label' => 'Estudiante'],
        'administrador'=> ['slug' => 'administrador',     'label' => 'Administrador'],
        'coordinacion' => ['slug' => 'coordinador',       'label' => 'Coordinación'],
        'docente'       => ['slug' => 'profesor proyecto',  'label' => 'Docente'],
        'docente_academico' => ['slug' => 'docente',      'label' => 'Docente Académico'],

    ],

    /*
    |--------------------------------------------------------------------------
    | Alias usados en rutas/vistas legacy
    |--------------------------------------------------------------------------
    */
    'aliases' => [
        'coordinador'      => ['coordinador', 'coordinacion', 'coordinador coordinacion'],
        'profesor proyecto' => ['profesor proyecto', 'profesor', 'docente'],
        'docente'          => ['docente', 'docente academico'],
        'administrador'    => ['administrador', 'admin'],
        'gestionador'      => ['gestionador'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo usu_cod_rol (BD externa) => rol del sistema
    |--------------------------------------------------------------------------
    */
    'usu_cod_rol_map' => [
        1 => 'administrador',
        2 => 'coordinador',
    ],

    'session_key' => 'active_role',

    /*
    |--------------------------------------------------------------------------
    | Simula en sesión el acceso del rol elegido
    |--------------------------------------------------------------------------
    */
    'allow_free_session_roles' => env('ROLES_ALLOW_FREE_SESSION', true),

];
