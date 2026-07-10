<?php

namespace App\Services;

use App\Helpers\DbHelper;
use App\Models\Proyecto;
use App\Repositories\GrupoProyectoRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de exportación del Depósito de Proyectos.
 *
 * Extrae la información de cada proyecto registrado en el repositorio,
 * complementándola con datos académicos (Sede, PNF, Trayecto, Sección, Lapso)
 * y con los integrantes del equipo e involucrados (Tutor Académico).
 *
 * El número de integrantes por equipo puede variar; el método genera una
 * estructura dinámica para acomodar esa variabilidad en el Excel.
 */
class ReporteDepositoService
{
    public function __construct(
        protected IntranetEquipoSeccionService $equipoSeccion,
        protected GrupoProyectoRepository $grupoRepo,
    ) {}

    /**
     * Construye un array de filas para el reporte Excel.
     * Cada fila es un array asociativo con claves normalizadas.
     *
     * @param  array<string, mixed>  $filtros  (estado, comunidad, lapso_codigo, pro_siglas)
     * @return array{maxIntegrantes: int, filas: list<array<string, mixed>>, lapsoMasActual: string, pnfPredominante: string}
     */
    public function construirFilasReporte(array $filtros = []): array
    {
        $proyectos = $this->obtenerProyectos($filtros);
        $lapsoFiltroCodigo = isset($filtros['lapso_codigo']) && $filtros['lapso_codigo'] !== '' ? (int) $filtros['lapso_codigo'] : null;
        $pnfFiltroSiglas   = isset($filtros['pro_siglas']) && $filtros['pro_siglas'] !== '' ? mb_strtoupper(trim($filtros['pro_siglas'])) : null;

        $filas = [];
        $maxIntegrantes = 1; // mínimo 1 para que siempre haya al menos una columna

        // Para construir el nombre del archivo con el lapso más actual y PNF
        $lapsoMasActual   = '';
        $lapsoCodigoMax   = 0;
        $pnfConteo        = [];

        foreach ($proyectos as $index => $proyecto) {
            $contexto = $this->resolverContextoEquipo($proyecto);

            // ── Filtrar por lapso ─────────────────────────────────────────
            $lapCodigo = (int) ($contexto['lap_codigo'] ?? 0);
            if ($lapsoFiltroCodigo !== null && $lapCodigo !== $lapsoFiltroCodigo) {
                continue;
            }

            // ── Filtrar por PNF ───────────────────────────────────────────
            $pnfSigla = mb_strtoupper(trim((string) ($contexto['pro_siglas'] ?? '')));
            if ($pnfFiltroSiglas !== null && $pnfSigla !== $pnfFiltroSiglas) {
                continue;
            }

            $integrantes = $this->resolverIntegrantes($proyecto);
            $tutor = $this->resolverTutorAcademico($proyecto->id);
            $sede = $this->resolverSede($contexto);

            $maxIntegrantes = max($maxIntegrantes, count($integrantes));

            // ── Rastrear lapso más reciente ────────────────────────────────
            $lapNombre = trim((string) ($contexto['lap_nombre'] ?? ''));
            if ($lapCodigo > 0 && $lapCodigo > $lapsoCodigoMax) {
                $lapsoCodigoMax = $lapCodigo;
                $lapsoMasActual = $lapNombre;
            }

            // ── Rastrear PNF más frecuente ─────────────────────────────────
            if ($pnfSigla !== '') {
                $pnfConteo[$pnfSigla] = ($pnfConteo[$pnfSigla] ?? 0) + 1;
            }

            $proNombre = $contexto['pro_nombre'] ?? '';
            $proSiglas = $contexto['pro_siglas'] ?? '';

            $filas[] = [
                'numero'            => count($filas) + 1,
                'sede'              => $sede,
                'pnf'               => $proNombre !== '' ? $proNombre : $proSiglas,
                'trayecto'          => $contexto['trayecto_nombre'] ?? '',
                'seccion'           => $contexto['sec_nombre'] ?? '',
                'lapso'             => $contexto['lap_nombre'] ?? '',
                'titulo'            => $proyecto->titulo ?? '',
                'comunidad'         => $proyecto->comunidad?->nombre ?? '',
                'equipo'            => $contexto['nombre_equipo'] ?? '',
                'integrantes'       => $integrantes,
                'tutor_academico'   => $tutor,
                'cumplio_requisitos' => $this->etiquetaCumplimiento($proyecto),
                'cant_beneficiados' => $proyecto->pry_cantidad_beneficiados ?? '',
            ];
        }

        // PNF con mayor número de proyectos en el conjunto
        $pnfPredominante = '';
        if (!empty($pnfConteo)) {
            arsort($pnfConteo);
            $pnfPredominante = (string) array_key_first($pnfConteo);
        }

        // Si se filtró por PNF, usarlo como predominante
        if ($pnfFiltroSiglas !== null) {
            $pnfPredominante = $pnfFiltroSiglas;
        }

        return [
            'maxIntegrantes'  => $maxIntegrantes,
            'filas'           => $filas,
            'lapsoMasActual'  => $lapsoMasActual,
            'pnfPredominante' => $pnfPredominante,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Privados / Protegidos
    // ─────────────────────────────────────────────────────────────

    /**
     * Obtiene todos los proyectos respetando los filtros del sistema.
     */
    protected function obtenerProyectos(array $filtros): Collection
    {
        return Proyecto::with(['comunidad'])
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true)
            ->when(($filtros['comunidad'] ?? '') !== '', fn ($q) => $q->where('comunidad_id', $filtros['comunidad']))
            ->orderBy('id')
            ->get();
    }

    /**
     * Resuelve el contexto académico (Sede, PNF, Trayecto, Sección, Lapso, Nombre del equipo)
     * desde la clave de equipo_ref del proyecto.
     *
     * @return array<string, string>
     */
    protected function resolverContextoEquipo(Proyecto $proyecto): array
    {
        $clave = $proyecto->equipo_ref ?? '';

        $defaults = [
            'lap_nombre'      => '',
            'sec_nombre'      => '',
            'pro_siglas'      => '',
            'pro_nombre'      => '',
            'tra_codigo'      => null,
            'trayecto_nombre' => '',
            'sed_nombre'      => '',
            'sed_siglas'      => '',
            'sec_codigo'      => null,
            'lap_codigo'      => null,
            'nombre_equipo'   => '',
        ];

        if ($clave === '') {
            return $defaults;
        }

        // ── Caso 1: Grupo registrado por identificador ──
        $gruposSvc = app(GrupoProyectoService::class);

        // Try lookup by identificador or EQGRP: id
        $grupo = null;
        if (!str_starts_with($clave, 'EQSEC:')) {
            $grupo = \App\Models\GrupoProyectoModulo::where('grp_identificador', $clave)->first();
        }
        if (!$grupo) {
            $partes = $gruposSvc->parsearClave($clave);
            if (($partes['tipo'] ?? '') === GrupoProyectoService::PREFIJO) {
                $grupo = $this->grupoRepo->find($partes['grp_codigo'] ?? 0);
            }
        }

        if ($grupo) {
            $ctx = $grupo->grp_contexto;
            if ($ctx) {
                $lapCodigo = (int) ($ctx['lap_codigo'] ?? 0);
                $secCodigo = (int) ($ctx['sec_codigo'] ?? 0);
                $sedNombre = '';
                $sedSiglas = '';

                if ($secCodigo > 0) {
                    [$sedNombre, $sedSiglas] = $this->obtenerSedeDeSeccion($secCodigo);
                }

                return array_merge($defaults, [
                    'lap_nombre'      => trim((string) ($ctx['lap_nombre'] ?? '')),
                    'sec_nombre'      => trim((string) ($ctx['sec_nombre'] ?? '')),
                    'pro_siglas'      => trim((string) ($ctx['pro_siglas'] ?? '')),
                    'pro_nombre'      => trim((string) ($ctx['pro_nombre'] ?? '')),
                    'trayecto_nombre' => trim((string) ($ctx['trayecto_nombre'] ?? $ctx['tra_nombre'] ?? '')),
                    'sed_nombre'      => $sedNombre,
                    'sed_siglas'      => $sedSiglas,
                    'sec_codigo'      => $secCodigo > 0 ? $secCodigo : null,
                    'lap_codigo'      => $lapCodigo > 0 ? $lapCodigo : null,
                    'nombre_equipo'   => trim((string) ($grupo->grp_nombre ?? '')),
                ]);
            }
        }

        // ── Caso 2: Clave EQSEC:lapso:seccion ──
        $partesEq = $this->equipoSeccion->parsearClave($clave);
        if ($partesEq) {
            $lapCodigo = (int) $partesEq['lap_codigo'];
            $secCodigo = (int) $partesEq['sec_codigo'];

            $etiquetas = $this->equipoSeccion->etiquetasContexto($lapCodigo, $secCodigo);
            [$sedNombre, $sedSiglas] = $this->obtenerSedeDeSeccion($secCodigo);
            \Illuminate\Support\Facades\Log::info("Contexto para sec $secCodigo: Sede=$sedNombre, Trayecto=".($etiquetas['trayecto_nombre'] ?? 'vacio'));

            return array_merge($defaults, [
                'lap_nombre'      => $etiquetas['lap_nombre'] ?? '',
                'sec_nombre'      => $etiquetas['sec_nombre'] ?? '',
                'pro_siglas'      => $etiquetas['pro_siglas'] ?? '',
                'pro_nombre'      => $etiquetas['pro_nombre'] ?? '',
                'trayecto_nombre' => $etiquetas['trayecto_nombre'] ?? '',
                'sed_nombre'      => $sedNombre,
                'sed_siglas'      => $sedSiglas,
                'sec_codigo'      => $secCodigo,
                'lap_codigo'      => $lapCodigo,
                'nombre_equipo'   => 'Sección ' . ($etiquetas['sec_nombre'] ?? $secCodigo),
            ]);
        }

        return $defaults;
    }

    /**
     * Obtiene el nombre de la sede a partir del código de sección.
     *
     * Busca en la BD académica la relación seccion → sede.
     * Si no existe la tabla sede, retorna cadenas vacías.
     *
     * @return array{0: string, 1: string}  [sed_nombre, sed_siglas]
     */
    protected function obtenerSedeDeSeccion(int $secCodigo): array
    {
        try {
            $conn = $this->equipoSeccion->academicConnection();

            $row = DB::connection($conn)
                ->table('seccion as sec')
                ->leftJoin('sede as sed', 'sed.sed_codigo', '=', 'sec.sec_cod_sede')
                ->where('sec.sec_codigo', $secCodigo)
                ->select([
                    DB::raw("TRIM(COALESCE(sed.sed_nombre, '')) as sed_nombre"),
                    DB::raw("TRIM(COALESCE(sed.sed_siglas, '')) as sed_siglas"),
                ])
                ->first();

            if ($row) {
                return [trim((string) $row->sed_nombre), trim((string) $row->sed_siglas)];
            }
        } catch (\Throwable) {
            // La columna sec_cod_sede o la tabla sede puede no existir en simulación
        }

        return ['', ''];
    }

    /**
     * Resuelve la lista de integrantes del equipo del proyecto.
     * La cantidad puede variar entre equipos.
     *
     * @return list<array{nombre: string, cedula: string}>
     */
    protected function resolverIntegrantes(Proyecto $proyecto): array
    {
        $clave = $proyecto->equipo_ref ?? '';
        if ($clave === '') {
            return [];
        }

        try {
            $miembros = $this->equipoSeccion->integrantes($clave);

            return $miembros->map(fn ($m) => [
                'nombre' => trim(($m->nombre ?? '') . ' ' . ($m->apellido ?? '')),
                'cedula' => trim((string) ($m->cedula ?? '')),
            ])->values()->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Busca el tutor académico entre los involucrados del proyecto.
     * Se considera tutor quien tenga un rol cuyo nombre contenga "tutor" (case-insensitive).
     */
    protected function resolverTutorAcademico(int $proyectoId): string
    {
        try {
            $conn = (string) config('dual_database.repositorio_connection', 'pgsql');

            $rows = DB::connection($conn)
                ->table('proyecto_involucrado as pi')
                ->join('involucrados as i', 'i.id', '=', 'pi.involucrado_id')
                ->join('involucrado_rol as ir', 'ir.proyecto_involucrado_id', '=', 'pi.id')
                ->join('roles_involucrados as ri', 'ri.id', '=', 'ir.rol_id')
                ->where('pi.proyecto_id', $proyectoId)
                ->whereRaw("LOWER(ri.nombre) LIKE ?", ['%tutor%'])
                ->select([
                    DB::raw("TRIM(i.nombre) as nombre"),
                    DB::raw("TRIM(i.apellido) as apellido"),
                    DB::raw("TRIM(i.cedula) as cedula"),
                ])
                ->distinct()
                ->get();

            if ($rows->isEmpty()) {
                return '';
            }

            return $rows->map(fn ($r) => trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')))->implode(', ');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Resuelve la sede a mostrar en el reporte (nombre o siglas si existe).
     */
    protected function resolverSede(array $contexto): string
    {
        $nombre = $contexto['sed_nombre'] ?? '';
        $siglas = $contexto['sed_siglas'] ?? '';

        if ($nombre !== '') {
            return $siglas !== '' ? "{$siglas} - {$nombre}" : $nombre;
        }

        return $siglas !== '' ? $siglas : '—';
    }

    /**
     * Indica si el estudiante cumplió con los requisitos del registro del proyecto.
     * Retorna "Sí" si el estudiante completó y subió los documentos del proyecto
     * (actualizado_por_estudiante = true), "—" en caso contrario.
     */
    protected function etiquetaCumplimiento(Proyecto $proyecto): string
    {
        return ($proyecto->actualizado_por_estudiante ?? false) ? 'Sí' : '—';
    }
}
