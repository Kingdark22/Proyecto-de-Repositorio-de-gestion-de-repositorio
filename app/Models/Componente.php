<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class Componente extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'componentes';

    protected $fillable = [
        'nombre',
        'programa_id',
        'es_obligatorio',
        'estado_logico',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'estado_logico' => 'boolean',
    ];

    /**
     * Guarda múltiples componentes.
     */
    public static function guardarMuchos(array $rows, string $programa_id): void
    {
        foreach ($rows as $row) {
            self::create([
                'nombre' => $row['nombre'],
                'programa_id' => $programa_id,
                'es_obligatorio' => $row['es_obligatorio'],
                'estado_logico' => true,
            ]);
        }
    }

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
}
