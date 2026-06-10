<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends IntranetModel
{
    protected $table = 'rol';
    protected $fillable = ['nombre'];
}
