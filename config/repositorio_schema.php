<?php

/**
 * Mapeo entre nombres usados en el código Laravel y columnas reales en la BD repositorio.
 */
return [

    'proyectos' => [
        'primary_key' => 'pry_codigo',
        'columns' => [
            'id'                  => 'pry_codigo',
            'resumen'             => 'pry_resumen',
            'linea_investigacion_id' => 'lin_codigo',
            'metodologia_id' => 'mei_codigo',
            'tipo_investigacion_id' => 'tin_codigo',
            'pry_estado' => 'pry_estado',
            'actualizado_por_estudiante' => 'pry_actualizado_por_estudiante',
            'fecha_actualizacion_estudiante' => 'pry_fecha_actualizacion_estudiante',
            'creador_cedula' => 'pry_creador_cedula',
            'objetivo_investigacion_id' => 'obi_codigo',
            'comunidad_id' => 'com_codigo',
            'equipo_ref' => 'pry_direccion_logica',
            'cantidad_beneficiados' => 'pry_cantidad_beneficiados',
        ],
        'values' => [
            'pry_estado' => [
                'pendiente' => 'Pendiente',
                'aprobado' => 'Aprobado',
                'rechazado' => 'Rechazado',
            ],
        ],
    ],

    'comunidades' => [
        'primary_key' => 'com_codigo',
        'columns' => [
            'id'                => 'com_codigo',
            'nombre'            => 'com_nombre',
            'direccion_texto'   => 'com_direccion',
            'rif'               => 'com_rif',
            'correo'            => 'com_correo',
            'direccion_id'      => 'com_dir_codigo',
            'numero_telefono'   => 'com_telefono',
            'estado_logico'     => 'com_estado_logico',
        ],
    ],

    'direcciones' => [
        'primary_key' => 'dir_codigo',
        'columns' => [
            'id'            => 'dir_codigo',
            'municipio_id'  => 'mun_codigo',
            'parroquia'     => 'dir_parroquia',
            'sector'        => 'dir_sector',
            'dir_calle'     => 'dir_calle',
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

    'componente_programa' => [
        'primary_key' => 'cpp_codigo',
        'columns' => [
            'id' => 'cpp_codigo',
            'componente_id' => 'comp_codigo',
            'programa_id' => 'pro_codigo',
            'trayecto_id' => 'tra_codigo',
        ],
    ],

    'involucrados' => [
        'primary_key' => 'inv_codigo',
        'columns' => [
            'id' => 'inv_codigo',
            'nombre' => 'inv_nombre',
            'apellido' => 'inv_apellido',
            'cedula' => 'inv_cedula',
        ],
        'timestamps' => [
            'created_at' => 'inv_created_at',
            'updated_at' => 'inv_updated_at',
        ],
    ],

    'roles_involucrados' => [
        'primary_key' => 'rin_codigo',
        'columns' => [
            'id' => 'rin_codigo',
            'nombre' => 'rin_nombre',
        ],
        'timestamps' => [
            'created_at' => 'rin_created_at',
            'updated_at' => 'rin_updated_at',
        ],
    ],

    'proyecto_involucrado' => [
        'primary_key' => 'pin_codigo',
        'columns' => [
            'id' => 'pin_codigo',
            'proyecto_id' => 'pry_codigo',
            'involucrado_id' => 'inv_codigo',
        ],
    ],

    'detalle_involucrados_rol' => [
        'primary_key' => 'detir_codigo',
        'columns' => [
            'id' => 'detir_codigo',
            'involucrado_id' => 'inv_codigo',
            'rol_id' => 'rin_codigo',
        ],
    ],

    'grupo_proyecto_modulo' => [
        'primary_key' => 'grp_codigo',
        'columns' => [
            'id' => 'grp_codigo',
            'nombre' => 'grp_nombre',
            'contexto' => 'grp_contexto',
            'comunidad_id' => 'grp_com_codigo',
            'creador_cedula' => 'grp_creador_cedula',
            'miembros' => 'grp_miembros',
            'estado_logico' => 'grp_estado_logico',
            'identificador' => 'grp_identificador',
        ],
    ],

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

    'vinculaciones' => [
        'primary_key' => 'vin_codigo',
        'columns' => [
            'id'                   => 'vin_codigo',
            'proyecto_id'          => 'pry_codigo',
            'comunidad_id'         => 'com_codigo',
            'titulo_vinculacion_id' => 'tiv_codigo',
            'vin_descripcion'      => 'vin_descripcion',
            'observaciones'        => 'vin_observaciones',
            'estado_logico'        => 'vin_estado_logico',
        ],
    ],

    'titulos_vinculacion' => [
        'primary_key' => 'tiv_codigo',
        'columns' => [
            'id'            => 'tiv_codigo',
            'tiv_titulo'    => 'tiv_titulo',
            'estado_logico' => 'tiv_estado_logico',
        ],
    ],

    'proyecto_componente' => [
        'primary_key' => 'pom_codigo',
        'columns' => [
            'id'            => 'pom_codigo',
            'proyecto_id'   => 'pry_codigo',
            'componente_id' => 'comp_codigo',
            'archivo_path'  => 'pd_archivo_path',
            'orden'         => 'pd_orden',
            'estado'        => 'pd_estado',
            'observacion'   => 'pd_observacion',
        ],
    ],
];
