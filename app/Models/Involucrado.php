<?php

namespace App\Models;

class Involucrado extends RepositorioModel
{
    protected $table = 'involucrados';
    protected $primaryKey = 'inv_codigo';

    const CREATED_AT = 'inv_created_at';
    const UPDATED_AT = 'inv_updated_at';

    protected $fillable = [
        'nombre',
        'apellido',
        'cedula',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }
}
