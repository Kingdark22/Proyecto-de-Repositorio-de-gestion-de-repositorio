<?php

namespace App\Models;

class Direccion extends RepositorioModel
{
    protected $primaryKey = 'dir_codigo';

    protected $fillable = ['dir_calle', 'mun_codigo', 'dir_parroquia', 'dir_sector'];

    protected $table = 'direcciones';

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'mun_codigo');
    }

    public function comunidad()
    {
        return $this->hasOne(Comunidad::class, 'dir_codigo');
    }
}
