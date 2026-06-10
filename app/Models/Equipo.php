<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @deprecated El equipo del módulo es encapsulación intranet (EQSEC / grupo_proyecto).
 *             No usar tabla local equipos; ver GrupoProyectoService.
 */
class Equipo extends RepositorioModel
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'anio',
        'seccion',
        'comunidad_id',
        'estado_logico',
    ];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(User::class, 'equipo_estudiante', 'equipo_id', 'persona_id', 'id', 'usu_cedula')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }
}
