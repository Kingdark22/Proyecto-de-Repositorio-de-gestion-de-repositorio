<?php

return [

    /*
    | Copia automática intranet → simulación al leer datos (respaldo si cae intranet).
    | NO crea tablas: solo escribe en tablas que YA existen en simulación (mismos nombres que intranet).
    | La BD simulación debe ser un respaldo/export de intranet mantenido por el equipo (no migraciones Laravel).
    */
    'enabled' => env('INTRANET_MIRROR_TO_SIMULATION', true),

    'simulation_connection' => 'simulacion',

    /*
    | Si true, solo espeja tablas listadas en dual_database.intranet_tables que existan en simulación.
    */
    'only_existing_simulation_tables' => true,

    /*
    | Clave primaria por tabla (columna física en ambas BDs).
    */
    'primary_keys' => [
        'usuario' => 'usu_cedula',
        'persona' => 'per_cedula',
        'estudiante' => 'est_cedula',
        'lapso_academico' => 'lap_codigo',
        'programa' => 'pro_codigo',
        'trayecto' => 'tra_codigo',
        'malla' => 'mal_codigo',
        'seccion' => 'sec_codigo',
        'unidad_curricular' => 'ucu_codigo',
        'seccion_unidad_docente' => 'sud_codigo',
        'inscripcion' => 'ins_codigo',
        'semestre' => 'sem_codigo',
        'rol' => 'rol_codigo',
        'estado' => 'est_codigo',
        'municipio' => 'mun_codigo',
        'parroquia' => 'par_codigo',
    ],

];
