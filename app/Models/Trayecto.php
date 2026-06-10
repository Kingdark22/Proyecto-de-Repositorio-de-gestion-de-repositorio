<?php

namespace App\Models;

class Trayecto extends IntranetModel
{
    protected $table = 'trayecto';
    protected $primaryKey = 'tra_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
}
