<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class LineaInvestigacion extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'linea_investigacions';

    protected $primaryKey = 'lin_codigo';

    protected $fillable = [
        'nombre_investigacion',
        'descripcion',
        'area_de_investigacion',
        'programa_id',
        'activo',
    ];

    public function getNombreProgramaAttribute(): string
    {
        if (isset($this->attributes['nombre_programa_cache'])) {
            return $this->attributes['nombre_programa_cache'];
        }

        $id = $this->programa_id;
        if (! $id) {
            return 'N/A';
        }

        return once(function () use ($id) {
            $conn = \App\Helpers\DualDatabase::academicConnection();
            $prog = \Illuminate\Support\Facades\DB::connection($conn)
                ->table('programa')
                ->where('pro_codigo', $id)
                ->first(['pro_nombre', 'pro_siglas']);

            return $prog ? ($prog->pro_siglas ?? $prog->pro_nombre) : "Programa #{$id}";
        });
    }

    public function getActivoAttribute(): bool
    {
        $estado = $this->attributes['lin_estado'] ?? null;

        return $estado === 'Activo' || $estado === 'activo';
    }
}
