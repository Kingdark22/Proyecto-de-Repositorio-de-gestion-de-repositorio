<?php

namespace App\Models;

class Vinculacion extends RepositorioModel
{
    protected $table = 'vinculaciones';

    protected $fillable = [
        'proyecto_id',
        'titulo_vinculacion_id',
        'vin_descripcion',
        'com_codigo',
        'tipo',
    ];

    protected $with = ['tituloVinculacion'];

    public function getTituloAttribute(): string
    {
        return $this->tituloVinculacion?->titulo ?? '';
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

    public function tituloVinculacion()
    {
        return $this->belongsTo(TituloVinculacion::class, 'tiv_codigo', 'tiv_codigo');
    }
}
