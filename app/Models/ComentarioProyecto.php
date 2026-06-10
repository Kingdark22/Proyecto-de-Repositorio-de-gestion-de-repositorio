<?php

namespace App\Models;

class ComentarioProyecto extends RepositorioModel
{
    protected $table = 'comentarios_proyecto';

    protected $schemaKey = 'comentarios_proyecto';

    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'proyecto_id',
        'usuario_externo_id',
        'nombre_contacto',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'pry_codigo', 'pry_codigo');
    }

    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class, 'uex_codigo', 'uex_codigo');
    }
}
