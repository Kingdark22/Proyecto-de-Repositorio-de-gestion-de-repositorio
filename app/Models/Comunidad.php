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
        'direccion',
        'direccion_id',
        'anio',
    ];

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'com_dir_codigo');
    }

    public function contactos()
    {
        return $this->hasMany(ComunidadContacto::class, 'com_codigo', 'com_codigo');
    }
}
