<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');

        // Index for proyectos.creador_cedula (used in filters)
        try {
            Schema::connection($connection)->table('proyectos', function ($table) {
                $table->index('pry_creador_cedula', 'idx_proyectos_creador');
            });
        } catch (\Throwable) {
        }

        // Index for proyectos.comunidad_id (used in paginate filter)
        try {
            Schema::connection($connection)->table('proyectos', function ($table) {
                $table->index('comunidad_id', 'idx_proyectos_comunidad');
            });
        } catch (\Throwable) {
        }

        // Composite index for equipo_ref lookups
        try {
            Schema::connection($connection)->table('proyectos', function ($table) {
                $table->index('pry_direccion_logica', 'idx_proyectos_equipo_ref');
            });
        } catch (\Throwable) {
        }

        // Index for involucrados.cedula (autocomplete lookups)
        try {
            Schema::connection($connection)->table('involucrados', function ($table) {
                $table->index('cedula', 'idx_involucrados_cedula');
            });
        } catch (\Throwable) {
        }

        // Composite index for proyecto_involucrado lookups
        try {
            Schema::connection($connection)->table('proyecto_involucrado', function ($table) {
                $table->index(['proyecto_id', 'involucrado_id'], 'idx_proy_inv_proyecto_involucrado');
            });
        } catch (\Throwable) {
        }

        // Index for involucrado_rol lookups
        try {
            Schema::connection($connection)->table('involucrado_rol', function ($table) {
                $table->index('proyecto_involucrado_id', 'idx_inv_rol_pivot');
            });
        } catch (\Throwable) {
        }

        // Full-text indexes for catalog search fields (pg_trgm required)
        $trgmIndexes = [
            'idx_lineas_nombre_trgm' => 'CREATE INDEX IF NOT EXISTS idx_lineas_nombre_trgm ON linea_investigacions USING gin (lin_nombre_investigacion gin_trgm_ops)',
            'idx_metodologias_nombre_trgm' => 'CREATE INDEX IF NOT EXISTS idx_metodologias_nombre_trgm ON metodologia_investigacions USING gin (mei_nombre gin_trgm_ops)',
            'idx_tipos_inv_nombre_trgm' => 'CREATE INDEX IF NOT EXISTS idx_tipos_inv_nombre_trgm ON tipo_investigacions USING gin (tin_nombre gin_trgm_ops)',
            'idx_objetivos_nombre_trgm' => 'CREATE INDEX IF NOT EXISTS idx_objetivos_nombre_trgm ON objetivo_investigacions USING gin (obi_nombre gin_trgm_ops)',
            'idx_tipos_pub_nombre_trgm' => 'CREATE INDEX IF NOT EXISTS idx_tipos_pub_nombre_trgm ON tipo_publicacions USING gin (tpu_nombre gin_trgm_ops)',
        ];

        foreach ($trgmIndexes as $name => $sql) {
            try {
                DB::connection($connection)->statement($sql);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("No se pudo crear el índice trigram {$name}: " . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        $connection = config('dual_database.repositorio_connection', 'pgsql');

        try {
            Schema::connection($connection)->table('proyectos', function ($table) {
                $table->dropIndex('idx_proyectos_creador');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::connection($connection)->table('proyectos', function ($table) {
                $table->dropIndex('idx_proyectos_comunidad');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::connection($connection)->table('proyectos', function ($table) {
                $table->dropIndex('idx_proyectos_equipo_ref');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::connection($connection)->table('involucrados', function ($table) {
                $table->dropIndex('idx_involucrados_cedula');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::connection($connection)->table('proyecto_involucrado', function ($table) {
                $table->dropIndex('idx_proy_inv_proyecto_involucrado');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::connection($connection)->table('involucrado_rol', function ($table) {
                $table->dropIndex('idx_inv_rol_pivot');
            });
        } catch (\Throwable) {
        }

        $indexes = [
            'idx_lineas_nombre_trgm',
            'idx_metodologias_nombre_trgm',
            'idx_tipos_inv_nombre_trgm',
            'idx_objetivos_nombre_trgm',
            'idx_tipos_pub_nombre_trgm',
        ];

        foreach ($indexes as $index) {
            try {
                DB::connection($connection)->statement("DROP INDEX IF EXISTS {$index}");
            } catch (\Throwable) {
            }
        }
    }
};
