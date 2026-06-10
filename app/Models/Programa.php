<?php

namespace App\Models;

class Programa extends IntranetModel
{
    protected $table = 'programa';
    protected $primaryKey = 'pro_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
}
