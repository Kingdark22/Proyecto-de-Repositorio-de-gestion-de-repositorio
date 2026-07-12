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
        'grp_identificador',
        'grp_contexto',
        'grp_com_codigo',
        'grp_creador_cedula',
        'grp_miembros',
        'estado_logico',
        'updated_at',
        'created_at',
    ];

    protected $casts = [
        'grp_contexto' => AsArrayObject::class,
        'grp_miembros' => AsArrayObject::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

        public static function porIdentificador(string $identificador): ?self
    {
        return \Illuminate\Support\Facades\Cache::remember("grp:idf:{$identificador}", 300, function () use ($identificador) {
            return static::where('grp_identificador', $identificador)->first();
        });
    }

    public static function forgetIdentificador(string $identificador): void
    {
        \Illuminate\Support\Facades\Cache::forget("grp:idf:{$identificador}");
    }

    protected static function booted(): void
    {
        static::saved(function ($grupo) {
            if ($grupo->grp_identificador) {
                static::forgetIdentificador($grupo->grp_identificador);
            }
        });
        static::deleted(function ($grupo) {
            if ($grupo->grp_identificador) {
                static::forgetIdentificador($grupo->grp_identificador);
            }
        });
    }
}
