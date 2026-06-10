<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $primaryKey = 'est_codigo';

    protected $fillable = ['est_nombre'];

    protected $table = 'estados';

    public function municipios()
    {
        return $this->hasMany(Municipio::class, 'est_codigo');
    }
}
