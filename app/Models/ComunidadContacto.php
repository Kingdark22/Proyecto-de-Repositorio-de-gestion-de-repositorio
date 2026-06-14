<?php

namespace App\Models;

class ComunidadContacto extends RepositorioModel
{
    protected $table = 'comunidad_contactos';
    protected $primaryKey = 'ccom_codigo';

    protected $fillable = [
        'com_codigo',
        'ccon_nombre',
        'ccon_apellido',
        'ccon_correo',
        'ccon_telefono',
        'ccon_cargo',
    ];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'com_codigo', 'com_codigo');
    }
}
