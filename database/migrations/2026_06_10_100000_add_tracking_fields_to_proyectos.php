<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->boolean('pry_actualizado_por_estudiante')->default(false)->after('pry_estado_validacion');
            $table->timestamp('pry_fecha_actualizacion_estudiante')->nullable()->after('pry_actualizado_por_estudiante');
            $table->string('pry_creador_cedula', 20)->nullable()->after('pry_fecha_actualizacion_estudiante');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['pry_actualizado_por_estudiante', 'pry_fecha_actualizacion_estudiante', 'pry_creador_cedula']);
        });
    }
};
