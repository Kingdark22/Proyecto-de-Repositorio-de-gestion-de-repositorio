<?php

namespace App\Models;

class UnidadCurricular extends IntranetModel
{
    protected $table = 'unidad_curricular';
    protected $primaryKey = 'ucu_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
}
