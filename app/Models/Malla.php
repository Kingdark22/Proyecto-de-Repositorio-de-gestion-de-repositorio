<?php

namespace App\Models;

class Malla extends IntranetModel
{
    protected $table = 'malla';
    protected $primaryKey = 'mal_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
}
