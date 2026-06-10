<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComunidadContacto extends Model
{
    use HasFactory;

    protected $primaryKey = 'ccom_codigo';

    protected $fillable = ['com_codigo', 'ccon_nombre', 'ccon_apellido', 'ccon_correo', 'ccon_telefono', 'ccon_cargo'];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'com_codigo');
    }
}
