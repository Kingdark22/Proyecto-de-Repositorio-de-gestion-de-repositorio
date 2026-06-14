<?php

namespace App\Models;

class Vinculacion extends RepositorioModel
{
    protected $table = 'vinculaciones';

    protected $fillable = [
        'proyecto_id',
        'tipo',
        'observaciones',
        'comunidad_id',
        'estado_logico',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id', 'pry_codigo');
    }

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id', 'com_codigo');
    }
}
