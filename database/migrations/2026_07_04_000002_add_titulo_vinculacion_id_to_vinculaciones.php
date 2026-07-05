<?php

use App\Models\TituloVinculacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vinculaciones', 'tiv_codigo')) {
            Schema::table('vinculaciones', function (Blueprint $table) {
                $table->unsignedBigInteger('tiv_codigo')->nullable()->after('id');
                $table->index('tiv_codigo');
            });
        }

        if (Schema::hasColumn('vinculaciones', 'vin_titulo')) {
            $rows = DB::table('vinculaciones')
                ->whereNotNull('vin_titulo')
                ->where('vin_titulo', '!=', '')
                ->select('vin_codigo', 'vin_titulo')
                ->get();

            $cache = [];

            foreach ($rows as $row) {
                $titulo = trim($row->vin_titulo);
                if ($titulo === '') {
                    continue;
                }
                if (!isset($cache[$titulo])) {
                    $existing = TituloVinculacion::where('tiv_titulo', $titulo)->first();
                    if ($existing) {
                        $cache[$titulo] = $existing->id;
                    } else {
                        $tv = TituloVinculacion::create(['tiv_titulo' => $titulo]);
                        $cache[$titulo] = $tv->id;
                    }
                }
                DB::table('vinculaciones')
                    ->where('vin_codigo', $row->vin_codigo)
                    ->update(['tiv_codigo' => $cache[$titulo]]);
            }

            Schema::table('vinculaciones', function (Blueprint $table) {
                $table->dropColumn('vin_titulo');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('vinculaciones', 'vin_titulo')) {
            Schema::table('vinculaciones', function (Blueprint $table) {
                $table->text('vin_titulo')->nullable()->after('id');
            });
        }

        DB::table('vinculaciones')
            ->whereNotNull('tiv_codigo')
            ->update(['vin_titulo' => DB::raw('(SELECT tiv_titulo FROM titulos_vinculacion WHERE titulos_vinculacion.tiv_codigo = vinculaciones.tiv_codigo)')]);

        if (Schema::hasColumn('vinculaciones', 'tiv_codigo')) {
            Schema::table('vinculaciones', function (Blueprint $table) {
                $table->dropIndex(['tiv_codigo']);
                $table->dropColumn('tiv_codigo');
            });
        }
    }
};
