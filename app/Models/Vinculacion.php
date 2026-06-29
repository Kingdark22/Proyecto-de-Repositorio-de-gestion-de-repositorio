<?php

namespace App\Models;

class Vinculacion extends RepositorioModel
{
    protected $table = 'vinculaciones';

    protected $fillable = [
        'proyecto_id',
        'vin_titulo',
        'vin_descripcion',
        'com_codigo',
        'tipo',
    ];

    public function getTituloAttribute(): string
    {
        return $this->vin_titulo ?? '';
    }

    public function getDescripcionAttribute(): string
    {
        return $this->vin_descripcion ?? '';
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id', 'pry_codigo');
    }

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'com_codigo', 'com_codigo');
    }
}
