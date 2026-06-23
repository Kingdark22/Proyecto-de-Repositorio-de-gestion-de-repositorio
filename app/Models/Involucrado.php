<?php

namespace App\Models;

use App\Models\RepositorioModel;

class Involucrado extends RepositorioModel
{
    protected $table = 'involucrados';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

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
