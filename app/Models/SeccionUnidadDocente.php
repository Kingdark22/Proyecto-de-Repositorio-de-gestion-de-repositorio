<?php

namespace App\Models;

class SeccionUnidadDocente extends IntranetModel
{
    protected $table = 'seccion_unidad_docente';
    protected $primaryKey = 'sud_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
}
