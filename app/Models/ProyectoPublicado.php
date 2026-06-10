<?php

namespace App\Models;

class ProyectoPublicado extends RepositorioModel
{
    protected $table = 'proyectos_publicados';

    protected $schemaKey = 'proyectos_publicados';

    protected $fillable = [
        'proyecto_id',
        'archivo_path',
        'estado',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'pry_codigo', 'pry_codigo');
    }

    public function comentarios()
    {
        return $this->hasMany(ComentarioProyecto::class, 'pry_codigo', 'pry_codigo');
    }
}
