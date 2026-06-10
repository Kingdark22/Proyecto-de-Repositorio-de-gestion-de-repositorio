<?php

namespace App\Models;

class Estudiante extends IntranetModel
{
    protected $table = 'estudiante';
    protected $primaryKey = 'est_cedula';
    public $incrementing = false;
    protected $keyType = 'string';
}
