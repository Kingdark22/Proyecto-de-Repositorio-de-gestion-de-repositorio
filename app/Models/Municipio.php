<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $primaryKey = 'mun_codigo';

    protected $fillable = ['mun_nombre', 'est_codigo'];

    protected $table = 'municipios';

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'est_codigo');
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'mun_codigo');
    }
}
