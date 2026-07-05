<?php

namespace App\Models;

class TituloVinculacion extends RepositorioModel
{
    protected $table = 'titulos_vinculacion';

    protected $fillable = [
        'tiv_titulo',
    ];

    public function getTituloAttribute(): string
    {
        return $this->tiv_titulo ?? '';
    }

    public function vinculaciones()
    {
        return $this->hasMany(Vinculacion::class, 'tiv_codigo', 'tiv_codigo');
    }
}
