<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trayectos mostrados al habilitar profesor en el módulo repositorio
    |--------------------------------------------------------------------------
    */
    'trayectos' => [
        'Año I',
        'Año II',
        'Año III',
        'Año IV',
        'Año V',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estatus activos en tablas académicas intranet
    |--------------------------------------------------------------------------
    */
    'lapso_estatus_activo' => 'A',
    'sud_estatus_activo' => 'A',

    /*
    | Solo la asignación docente más reciente y activa por sección + UC de proyecto
    | (mayor sud_codigo con sud_estatus activo en el lapso filtrado).
    */
    'filtrar_sud_vigente_reciente' => true,

    'sud_vigente_grupo' => [
        'sud_cod_seccion',
        'sud_cod_unidad',
    ],

    /*
    |--------------------------------------------------------------------------
    | Unidades curriculares que identifican "Proyecto" (PNF Informática, etc.)
    | Se valida contra intranet: sud → sec → malla (año/trayecto) + lapso activo.
    |--------------------------------------------------------------------------
    */
    'unidad_siglas_prefijos' => [
        'AGPF',
        'PRO',
        'PSI',
        'PRF',
        'PTP',
        'PIPT',
        'MEPS',
        'MTPR',
        'MTPS',
        'LDPS',
        'LSPS',
        'PNN',
        'PNC',
        'PRS',
        'PRY',
        'PACPA',
        'FSCPN',
        'LDPN',
    ],

    'unidad_nombre_patrones' => [
        'PROYECTO FORMATIVO',
        'PROYECTO SOCIO',
        'PROYECTO NACIONAL',
        'PROYECTO TECNOL',
        'PROYECTO I',
        'PROYECTO II',
        'PROYECTO III',
        'PROYECTO IV',
        'PROYECTO V',
    ],

];
