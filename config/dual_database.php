<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conexión del módulo repositorio (proyectos, comunidades, etc.)
    |--------------------------------------------------------------------------
    */
    'repositorio_connection' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Tablas que viven en intranet — datos académicos
    |--------------------------------------------------------------------------
    */
    'intranet_tables' => [
        'usuario',
        'persona',
        'estudiante',
        'programa',
        'seccion',
        'seccion_unidad_docente',
        'inscripcion',
        'lapso_academico',
        'malla',
        'programa',
        'trayecto',
        'semestre',
        'unidad_curricular',
        'rol',
        'grupo_proyecto_estudiante',
        'grupo_proyecto',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tablas del repositorio — gestión del sistema de proyectos
    |--------------------------------------------------------------------------
    */
    'repositorio_tables' => [
        'proyectos',
        'proyecto_documentos',
        'comunidades',
        'comunidad_estudiante',
        'linea_investigacions',
        'metodologia_investigacions',
        'tipo_investigacions',
        'tipo_publicacions',
        'grupo_proyecto_modulo',
        'profesor_proyecto_modulo',
        'roles',
        'direcciones',
        'componentes',
        'auditorias',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas de conexión (sin FK entre bases)
    |--------------------------------------------------------------------------
    | - intranet_tables: solo SELECT desde el módulo.
    | - repositorio_tables: INSERT/UPDATE del módulo (proyectos, grupo_proyecto_modulo, etc.).
    | - Espejo: solo intranet → simulación (IntranetSimulationMirrorService), bajo demanda.
    | - Relación lapso/sección/cédula/EQGRP: en PHP (ConexionDualService), no en SQL entre BDs.
    |--------------------------------------------------------------------------
    */

];
