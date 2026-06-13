<?php

namespace App\Services;

use App\Helpers\DualDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicCatalog
{
    /**
     * Estudiantes desde intranet (persona + estudiante).
     */
    public function estudiantesForSelect(?string $search = null, int $limit = 50): Collection
    {
        $conn = DualDatabase::academicConnection();

        $query = DB::connection($conn)
            ->table('estudiante as e')
            ->join('persona as p', DB::raw('TRIM(e.est_cedula)'), '=', DB::raw('TRIM(p.per_cedula)'))
            ->select([
                DB::raw('TRIM(e.est_cedula) as cedula'),
                DB::raw("TRIM(COALESCE(p.per_nombres, '')) as nombres"),
                DB::raw("TRIM(COALESCE(p.per_apellidos, '')) as apellidos"),
            ])
            ->limit($limit);

        if ($search) {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('TRIM(e.est_cedula) ILIKE ?', [$term])
                    ->orWhereRaw('TRIM(p.per_nombres) ILIKE ?', [$term])
                    ->orWhereRaw('TRIM(p.per_apellidos) ILIKE ?', [$term]);
            });
        }

        $rows = $query->get();

        // Espejar usuarios, estudiantes y personas de forma inteligente
        if (DualDatabase::academicConnection() === 'intranet') {
            // Aumentar tiempo de ejecución para evitar el error de 60s
            if (!ini_get('safe_mode')) {
                set_time_limit(120);
            }
            
            $mirror = app(IntranetSimulationMirrorService::class);
            foreach ($rows as $row) {
                $mirror->mirrorUserContext($row->cedula);
            }
        }

        return collect($rows)->map(function ($row) {
            $row->nombre_completo = trim($row->nombres . ' ' . $row->apellidos);

            return $row;
        });
    }

    /**
     * Programas (PNFs) desde intranet.
     */
    public function programasForSelect(): Collection
    {
        $conn = DualDatabase::academicConnection();
        $cacheKey = 'academic_programas_v2_' . $conn;

        return Cache::remember($cacheKey, 3600, function () use ($conn) {
            $rows = DB::connection($conn)
                ->table('programa')
                ->where('pro_estatus', 'A')
                ->select([
                    'pro_codigo as id',
                    'pro_nombre as nombre',
                    'pro_siglas as siglas',
                ])
                ->orderBy('pro_nombre')
                ->get();

            if ($conn === 'intranet') {
                app(IntranetSimulationMirrorService::class)->mirrorAllPrograms();
            }

            return collect($rows);
        });
    }

    /**
     * Deshabilitado: no copiar tablas académicas a repositorio (solo espejo intranet→simulación).
     */
    public function mirrorTableToRepositorio(string $table): int
    {
        return 0;
    }
}
