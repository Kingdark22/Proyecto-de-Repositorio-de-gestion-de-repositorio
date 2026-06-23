<?php

namespace App\Models;

use App\Models\RepositorioModel;

class RolInvolucrado extends RepositorioModel
{
    protected $table = 'roles_involucrados';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
    ];
}
