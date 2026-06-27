<?php

/**
 * Mapeo entre nombres usados en el código Laravel y columnas reales en la BD repositorio.
 */
return [

    'proyectos' => [
        'primary_key' => 'pry_codigo',
        'columns' => [
            'id'                  => 'pry_codigo',
            // titulo es accessor derivado de equipo_ref
            'resumen'             => 'pry_resumen',
            'linea_investigacion_id' => 'lin_codigo',
            'metodologia_id' => 'mei_codigo',
            'tipo_publicacion_id' => 'tpu_codigo',
            'tipo_investigacion_id' => 'tin_codigo',
            'estado_logico' => 'pry_estado_logico',
            'estado_validacion' => 'pry_estado_validacion',
            'motivo_rechazo' => 'pry_motivo_rechazo',
            'actualizado_por_estudiante' => 'pry_actualizado_por_estudiante',
            'fecha_actualizacion_estudiante' => 'pry_fecha_actualizacion_estudiante',
            'creador_cedula' => 'pry_creador_cedula',
            'objetivo_id' => 'obj_codigo',
            'comunidad_id' => 'com_codigo',
            'equipo_ref' => 'pry_direccion_logica',
        ],
        'values' => [
            'estado_validacion' => [
                'aprobado' => 'Aprobado',
                'pendiente' => 'Pendiente',
                'rechazado' => 'Rechazado',
                'completado' => 'Completado',
            ],
        ],
    ],

    'comunidades' => [
        'primary_key' => 'com_codigo',
        'columns' => [
            'id'                => 'com_codigo',
            'nombre'            => 'com_nombre',
            'rif'               => 'com_rif',
            'correo'            => 'com_correo',
            'direccion_id'      => 'com_dir_codigo',
            'numero_telefono'   => 'com_telefono',
        ],
    ],

    'linea_investigacions' => [
        'primary_key' => 'lin_codigo',
        'columns' => [
            'id' => 'lin_codigo',
            'nombre_investigacion' => 'lin_nombre_investigacion',
            'descripcion' => 'lin_descripcion',
            'area_de_investigacion' => 'lin_area_de_investigacion',
            'programa_id' => 'coord_codigo',
            'activo' => 'lin_estado',
        ],
        'values' => [
            'activo' => [
                true => 'Activo',
                false => 'Inactivo',
                1 => 'Activo',
                0 => 'Inactivo',
            ],
        ],
    ],

    'tipo_publicacions' => [
        'primary_key' => 'tpu_codigo',
        'columns' => [
            'id' => 'tpu_codigo',
            'nombre' => 'tpu_nombre',
            'mencion_honorifica' => 'tpu_mencion_honorifica',
            'estado_logico' => 'tpu_estado_logico',
        ],
    ],

    'metodologia_investigacions' => [
        'primary_key' => 'mei_codigo',
        'columns' => [
            'id' => 'mei_codigo',
            'nombre' => 'mei_nombre',
            'descripcion' => 'mei_descripcion',
            'estado_logico' => 'mei_estado_logico',
        ],
    ],

    'tipo_investigacions' => [
        'primary_key' => 'tin_codigo',
        'columns' => [
            'id' => 'tin_codigo',
            'nombre' => 'tin_nombre',
            'descripcion' => 'tin_descripcion',
            'estado_logico' => 'tin_estado_logico',
        ],
    ],

    'objetivo_investigacions' => [
        'primary_key' => 'obi_codigo',
        'columns' => [
            'id' => 'obi_codigo',
            'nombre' => 'obi_nombre',
            'descripcion' => 'obi_descripcion',
            'estado_logico' => 'obi_estado_logico',
        ],
    ],

    'componentes' => [
        'primary_key' => 'comp_codigo',
        'columns' => [
            'id' => 'comp_codigo',
            'nombre' => 'comp_nombre',
            'programa_id' => 'coord_codigo',
            'es_obligatorio' => 'comp_es_obligatorio',
            'estado_logico' => 'comp_estado_logico',
            'tipo_archivo' => 'comp_tipo_archivo',
            'tamano_maximo_mb' => 'comp_tamano_maximo_mb',
        ],
        'values' => [
            'es_obligatorio' => [
                true => 1,
                false => 0,
                1 => 1,
                0 => 0,
            ],
            'estado_logico' => [
                true => 1,
                false => 0,
                1 => 1,
                0 => 0,
            ],
        ],
    ],

    'objetivos' => [
        'primary_key' => 'obj_codigo',
        'columns' => [
            'id'            => 'obj_codigo',
            'nombre'        => 'obj_nombre',
            'descripcion'   => 'obj_descripcion',
            'estado_logico' => 'obj_estado_logico',
        ],
        'values' => [
            'estado_logico' => [
                true => 1,
                false => 0,
                1 => 1,
                0 => 0,
            ],
        ],
    ],

    'departamento' => [
        'primary_key' => 'dep_codigo',
        'columns' => [
            'id'               => 'dep_codigo',
            'nombre'           => 'dep_nombre',
            'cargo'            => 'dep_cargo',
            'uex_codigo'       => 'dep_uex_codigo',
        ],
    ],

    // ---------------------------------------------------------------
    // Módulo Roles del Sistema (Tablas del usuario)
    // ---------------------------------------------------------------

    'rol_externo' => [
        'primary_key' => 'rex_codigo',
        'columns' => [
            'id'     => 'rex_codigo',
            'nombre' => 'rex_nombre',
        ],
    ],

    'usuarios_externos' => [
        'primary_key' => 'uex_codigo',
        'columns' => [
            'id'         => 'uex_codigo',
            'nombre'     => 'uex_nombre',
            'contrasena' => 'uex_contrasena',
            'rex_codigo' => 'uex_rex_codigo',
            'estado'     => 'uex_estado',
        ],
    ],

    // ---------------------------------------------------------------
    // Módulo Publicaciones
    // ---------------------------------------------------------------

    'proyectos_publicados' => [
        'primary_key' => 'pub_codigo',
        'columns' => [
            'id'           => 'pub_codigo',
            'proyecto_id'  => 'pry_codigo',
            'archivo_path' => 'pub_archivo_path',
            'estado'       => 'pub_estado',
        ],
    ],

    'vinculaciones' => [
        'primary_key' => 'vin_codigo',
        'columns' => [
            'id'              => 'vin_codigo',
            'proyecto_id'     => 'pry_codigo',
            'comunidad_id'    => 'com_codigo',
            'vin_titulo'      => 'vin_titulo',
            'vin_descripcion' => 'vin_descripcion',
            'tipo'            => 'vin_tipo',
            'observaciones'   => 'vin_observaciones',
            'estado_logico'   => 'vin_estado_logico',
        ],
    ],

    'comentarios_proyecto' => [
        'primary_key' => 'cop_codigo',
        'columns' => [
            'id'               => 'cop_codigo',
            'descripcion'      => 'cop_descripcion',
            'proyecto_id'      => 'pry_codigo',
            'usuario_externo_id' => 'uex_codigo',
            'nombre_contacto'  => 'cop_nombre_contacto',
            'fecha_creacion'   => 'cop_fecha_creacion',
        ],
    ],
];
