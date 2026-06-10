<?php

namespace App\Models;

class Seccion extends IntranetModel
{
    protected $table = 'seccion';
    protected $primaryKey = 'sec_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
}
