<?php

namespace App\Models;

class UsuarioExterno extends RepositorioModel
{
    protected $table = 'usuarios_externos';

    protected $fillable = [
        'uex_nombre',
        'uex_contrasena',
        'uex_rex_codigo',
        'uex_estado',
    ];

    public function comentarios()
    {
        return $this->hasMany(ComentarioProyecto::class, 'uex_codigo', 'uex_codigo');
    }
}
