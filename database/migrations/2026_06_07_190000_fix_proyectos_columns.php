<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class FixProyectosColumns extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('proyectos', 'pry_estado_validacion')) {
            Schema::table('proyectos', function ($table) {
                $table->enum('pry_estado_validacion', ['Aprobado', 'Pendiente', 'Rechazado'])
                    ->default('Pendiente')
                    ->after('pry_estado_logico');
            });
        }
        if (!Schema::hasColumn('proyectos', 'pry_documentos')) {
            Schema::table('proyectos', function ($table) {
                $table->text('pry_documentos')->nullable()->after('pry_archivo_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('proyectos', 'pry_estado_validacion')) {
            Schema::table('proyectos', function ($table) {
                $table->dropColumn('pry_estado_validacion');
            });
        }
        if (Schema::hasColumn('proyectos', 'pry_documentos')) {
            Schema::table('proyectos', function ($table) {
                $table->dropColumn('pry_documentos');
            });
        }
    }
}
