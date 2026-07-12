<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    protected $connection;

    public function __construct()
    {
        $this->connection = (string) config('dual_database.repositorio_connection', 'pgsql');
    }

    public function up(): void
    {
        $conn = $this->connection;

        // 1. org_contactos (tiene FK a organizacion y departamento)
        if (Schema::connection($conn)->hasTable('org_contactos')) {
            Schema::connection($conn)->dropIfExists('org_contactos');
        }

        // 2. comunidad_contactos (tiene FK a comunidades)
        if (Schema::connection($conn)->hasTable('comunidad_contactos')) {
            Schema::connection($conn)->dropIfExists('comunidad_contactos');
        }

        // 3. organizacion (ya nadie la referencia)
        if (Schema::connection($conn)->hasTable('organizacion')) {
            Schema::connection($conn)->dropIfExists('organizacion');
        }

        // 4. departamento (ya nadie la referencia)
        if (Schema::connection($conn)->hasTable('departamento')) {
            Schema::connection($conn)->dropIfExists('departamento');
        }

        // 5. tipo_publicacions - primero dropear FK desde proyectos
        try {
            DB::connection($conn)->statement(
                'ALTER TABLE ONLY public.proyectos DROP CONSTRAINT IF EXISTS proyectos_tipo_publicacion_id_foreign'
            );
        } catch (\Throwable) {}

        // 5b. Opcional: dropear columna tpu_codigo de proyectos (nunca se usa)
        if (Schema::connection($conn)->hasColumn('proyectos', 'tpu_codigo')) {
            Schema::connection($conn)->table('proyectos', function (Blueprint $table) {
                $table->dropColumn('tpu_codigo');
            });
        }

        // 5c. Dropear tipo_publicacions
        if (Schema::connection($conn)->hasTable('tipo_publicacions')) {
            Schema::connection($conn)->dropIfExists('tipo_publicacions');
        }

        // 6. proyecto_involucrado_rol (reemplazada por involucrado_rol)
        if (Schema::connection($conn)->hasTable('proyecto_involucrado_rol')) {
            Schema::connection($conn)->dropIfExists('proyecto_involucrado_rol');
        }

        // 7. involucrados_comunidad (huérfana total)
        if (Schema::connection($conn)->hasTable('involucrados_comunidad')) {
            Schema::connection($conn)->dropIfExists('involucrados_comunidad');
        }
    }

    public function down(): void
    {
        // No revertir — estas tablas son basura
    }
};
