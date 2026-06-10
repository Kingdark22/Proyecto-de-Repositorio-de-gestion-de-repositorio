<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Models\RepositorioModel;

class GrupoProyectoModulo extends RepositorioModel
{
    protected $table = 'grupo_proyecto_modulo';
    protected $primaryKey = 'grp_codigo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'grp_nombre',
        'grp_contexto',
        'grp_com_codigo',
        'grp_creador_cedula',
        'grp_miembros',
        'updated_at',
        'created_at',
    ];

    protected $casts = [
        'grp_contexto' => AsArrayObject::class,
        'grp_miembros' => AsArrayObject::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Puedes añadir relaciones o scopes aquí si es necesario más adelante
}
