<?php

namespace App\Models;

class RolInvolucrado extends RepositorioModel
{
    protected $table = 'roles_involucrados';
    protected $primaryKey = 'rin_codigo';

    const CREATED_AT = 'rin_created_at';
    const UPDATED_AT = 'rin_updated_at';

    protected $fillable = [
        'nombre',
    ];
}
