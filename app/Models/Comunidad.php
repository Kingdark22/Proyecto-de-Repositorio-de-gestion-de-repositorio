<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class Comunidad extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'comunidades';

    protected $primaryKey = 'com_codigo';

    protected $fillable = [
        'nombre',
        'rif',
        'correo',
        'numero_telefono',
        'direccion',
        'dir_codigo',
        'anio',
    ];

    public function contactos()
    {
        return $this->hasMany(ComunidadContacto::class, 'com_codigo', 'com_codigo');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'dir_codigo');
    }
}
