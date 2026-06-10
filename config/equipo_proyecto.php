<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Equipo vs grupo de proyecto
    |--------------------------------------------------------------------------
    |
    | Grupo de proyecto: conjunto formal de estudiantes (líder, autores) creado
    | para elaborar un expediente (intranet: grupo_proyecto / grupo_proyecto_estudiante
    | cuando existan en la BD académica).
    |
    | Equipo: encapsulación de ese grupo en el repositorio — la clave que se guarda
    | en proyectos.pry_direccion_logica y con la que se listan integrantes.
    |
    */
    'prefijos_clave' => [
        'seccion' => 'EQSEC',
        'grupo_modulo' => 'EQGRP',  // grupo registrado en repositorio (integrantes elegidos)
    ],

    'roles_grupo' => [
        'lider' => 1,
        'autor' => 2,
    ],

    'inscripcion_estatus_activo' => ['A'],

];
