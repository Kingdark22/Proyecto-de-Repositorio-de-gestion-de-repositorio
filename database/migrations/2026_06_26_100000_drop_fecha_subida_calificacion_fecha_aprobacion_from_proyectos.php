<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropFechaSubidaCalificacionFechaAprobacionFromProyectos extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('proyectos', 'pry_fecha_subida')) {
            Schema::table('proyectos', function ($table) {
                $table->dropColumn('pry_fecha_subida');
            });
        }
        if (Schema::hasColumn('proyectos', 'pry_calificacion')) {
            Schema::table('proyectos', function ($table) {
                $table->dropColumn('pry_calificacion');
            });
        }
        if (Schema::hasColumn('proyectos', 'pry_fecha_aprobacion')) {
            Schema::table('proyectos', function ($table) {
                $table->dropColumn('pry_fecha_aprobacion');
            });
        }
    }

    public function down(): void
    {
        Schema::table('proyectos', function ($table) {
            $table->date('pry_fecha_subida')->nullable()->after('pry_resumen');
        });
        Schema::table('proyectos', function ($table) {
            $table->integer('pry_calificacion')->nullable()->after('pry_fecha_subida');
        });
        Schema::table('proyectos', function ($table) {
            $table->date('pry_fecha_aprobacion')->nullable()->after('pry_calificacion');
        });
    }
}
