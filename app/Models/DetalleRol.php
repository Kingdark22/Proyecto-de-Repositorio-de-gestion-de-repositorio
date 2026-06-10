<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleRol extends Model
{
    protected $table = 'detalle_rol';
    protected $fillable = ['persona_id', 'id_rol', 'estado_logico'];
}
