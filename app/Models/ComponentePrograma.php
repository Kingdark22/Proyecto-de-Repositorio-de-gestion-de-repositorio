<?php

namespace App\Models;

class ComponentePrograma extends RepositorioModel
{
    protected $table = 'componente_programa';
    protected $primaryKey = 'cpp_codigo';

    protected $fillable = [
        'comp_codigo',
        'pro_codigo',
        'tra_codigo',
        'cantidad',
    ];

    public function componente()
    {
        return $this->belongsTo(Componente::class, 'comp_codigo', 'comp_codigo');
    }

    public function getProgramaNombreAttribute(): string
    {
        if (isset($this->attributes['programa_nombre_cache'])) {
            return $this->attributes['programa_nombre_cache'];
        }

        $id = $this->pro_codigo;
        if (! $id) {
            return 'N/A';
        }

        return once(function () use ($id) {
            $conn = \App\Helpers\DualDatabase::academicConnection();
            $prog = \Illuminate\Support\Facades\DB::connection($conn)
                ->table('programa')
                ->where('pro_codigo', $id)
                ->first(['pro_nombre', 'pro_siglas']);

            return $prog ? ($prog->pro_siglas ?? $prog->pro_nombre) : "PNF #{$id}";
        });
    }

    public function getTrayectoNombreAttribute(): string
    {
        if (isset($this->attributes['trayecto_nombre_cache'])) {
            return $this->attributes['trayecto_nombre_cache'];
        }

        $id = $this->tra_codigo;
        if (! $id) {
            return 'Todos los trayectos';
        }

        return once(function () use ($id) {
            $conn = \App\Helpers\DualDatabase::academicConnection();
            $tray = \Illuminate\Support\Facades\DB::connection($conn)
                ->table('trayecto')
                ->where('tra_codigo', $id)
                ->first(['tra_nombre']);

            return $tray ? $tray->tra_nombre : "Trayecto #{$id}";
        });
    }
}
