<?php

namespace App\Models;

class Vinculacion extends RepositorioModel
{
    protected $table = 'vinculaciones';

    protected $fillable = [
        'proyecto_id',
        'tipo',
        'vin_titulo',
        'vin_descripcion',
        'observaciones',
        'comunidad_id',
    ];

    protected $casts = [
        'vin_titulo' => 'string',
        'vin_descripcion' => 'string',
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
        return $this->belongsTo(Comunidad::class, 'comunidad_id', 'com_codigo');
    }
}
