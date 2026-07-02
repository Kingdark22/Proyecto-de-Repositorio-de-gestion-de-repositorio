<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Services\ProyectoGestionService;
use App\Services\GrupoProyectoService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\ReporteDepositoService;
use App\Services\SpreadsheetMlWriter;
use App\Services\UserRoleService;
use App\Repositories\CatalogoRepository;
use App\Repositories\ComunidadRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProyectoController extends Controller
{
    public function __construct(
        protected ProyectoGestionService $gestion,
        protected GrupoProyectoService $grupos,
        protected IntranetEquipoSeccionService $equipoSeccion,
        protected ReporteDepositoService $reporteDeposito,
        protected UserRoleService $userRoleService,
        protected CatalogoRepository $catalogoRepo,
        protected ComunidadRepository $comunidadRepo,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esGestionador = $this->userRoleService->roleMatches('gestionador', $activeRole);

        $search = $request->get('search', '');
        $filterEstado = $request->get('estado', '');
        $filterComunidad = $request->get('comunidad', '');
        $filterLapso = $request->get('lapso', '');
        $page = (int) $request->get('page', 1);

        // Grupos del docente
        $gruposDocente = [];
        if (in_array($activeRole, ['profesor proyecto', 'administrador', 'gestionador', 'coordinador'])) {
            $filtrosGrupos = [];
            if ($filterLapso !== '') $filtrosGrupos['lapso'] = (int) $filterLapso;
            if (!in_array($activeRole, ['profesor proyecto'], true)) {
                $filterPrograma = $request->get('programa', '');
                $filterTrayecto = $request->get('trayecto', '');
                if ($filterPrograma !== '') $filtrosGrupos['programa'] = (int) $filterPrograma;
                if ($filterTrayecto !== '') $filtrosGrupos['trayecto'] = $filterTrayecto;
            }
            $gruposDocente = $this->gestion->gruposDelDocente($user, $filtrosGrupos)->toArray();
        }

        // Proyectos líder (estudiante)
        $esEstudianteLider = false;
        $proyectosLider = collect();
        if ($user && !$esProfesor) {
            if (!$this->userRoleService->roleMatches('administrador', $activeRole)
                && !$this->userRoleService->roleMatches('coordinador', $activeRole)
                && !$this->userRoleService->roleMatches('gestionador', $activeRole)) {
                $esEstudianteLider = true;
                $proyectosLider = $this->gestion->proyectosLider($user);
            }
        }

        // Listado general (todos menos estudiantes líder)
        $datosListado = [];
        $mostrarListado = !$esEstudianteLider;
        if ($mostrarListado) {
            $datosListado = $this->gestion->datosVistaListado([
                'search' => $search,
                'estado' => $filterEstado,
                'comunidad' => $filterComunidad,
                'lapso' => $filterLapso,
            ], $page, $user);
        }

        // Catálogos para filtros de grupos
        $lapsosFiltro = Cache::remember(
            'proyecto_manager_lapsos',
            now()->addMinutes(10),
            fn() => \App\Models\LapsoAcademico::activos()->orderByDesc('lap_codigo')->get()
        );

        $programasFiltro = collect();
        $trayectosFiltro = collect();
        $lapsoFiltro = $filterLapso !== '' ? (int) $filterLapso : null;
        if ($lapsoFiltro) {
            $programasFiltro = $this->equipoSeccion->programasEnLapso($lapsoFiltro);
        }

        $canValidate = $user ? $this->gestion->usuarioPuedeValidar($user) : false;
        $proyectosLiderIds = $this->gestion->proyectosDondeEsMiembro($user);

        return view('proyectos.index', compact(
            'search', 'filterEstado', 'filterComunidad', 'filterLapso',
            'esProfesor', 'esGestionador', 'esEstudianteLider',
            'gruposDocente', 'proyectosLider', 'proyectosLiderIds',
            'datosListado', 'mostrarListado', 'canValidate',
            'lapsosFiltro', 'programasFiltro', 'trayectosFiltro',
        ));
    }

    public function edit($id)
    {
        $user = auth()->user();
        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esGestionador = $this->userRoleService->roleMatches('gestionador', $activeRole);

        $proyecto = Proyecto::findOrFail($id);
        $esLider = $this->gestion->usuarioEsLiderDelProyecto($user, $proyecto);
        $modoActualizacion = $esLider && !$this->gestion->usuarioEsAdminEnSistema($user);
        $canValidate = $user ? $this->gestion->usuarioPuedeValidar($user) : false;

        $datosForm = $this->gestion->cargarParaEdicion($id);
        $estadoForm = $this->buildEstadoFromDatos($datosForm);
        $catalogosForm = $this->gestion->datosVistaFormulario($estadoForm);

        // Involucrados
        $involucradosProyecto = $this->gestion->involucradosDelProyecto($id)->toArray();

        // Miembros del grupo
        $miembrosGrupo = [];
        $clave = $datosForm['equipo_seccion_clave'] ?? '';
        if (str_starts_with($clave, 'EQGRP:')) {
            $this->cargarMiembrosGrupo($clave, $miembrosGrupo);
        }

        return view('proyectos.registro', compact(
            'proyecto', 'datosForm', 'catalogosForm',
            'esProfesor', 'esGestionador', 'esLider', 'modoActualizacion',
            'involucradosProyecto', 'miembrosGrupo', 'clave',
            'canValidate',
        ));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $proyecto = Proyecto::findOrFail($id);

        $activeRole = $this->userRoleService->getActiveRole($user);
        $esProfesor = $this->userRoleService->roleMatches('profesor proyecto', $activeRole);
        $esLider = $this->gestion->usuarioEsLiderDelProyecto($user, $proyecto);
        $esAdmin = $this->gestion->usuarioEsAdminEnSistema($user);
        $modoActualizacion = $esLider && !$esAdmin;

        $estadoForm = [
            'resumen' => $request->input('resumen', $proyecto->resumen ?? ''),
            'linea_investigacion_id' => $request->input('linea_investigacion_id'),
            'metodologia_id' => $request->input('metodologia_id'),
            'tipo_publicacion_id' => $request->input('tipo_publicacion_id'),
            'tipo_investigacion_id' => $request->input('tipo_investigacion_id'),
            'objetivo_investigacion_id' => $request->input('objetivo_investigacion_id'),
            'titulo' => $proyecto->titulo,
            'comunidad_id' => $request->input('comunidad_id', $proyecto->comunidad_id),
            'equipo_seccion_clave' => $request->input('equipo_seccion_clave', $proyecto->equipo_ref),
            'filterLapsoEquipo' => $request->input('filterLapsoEquipo', ''),
            'filterProgramaEquipo' => $request->input('filterProgramaEquipo', ''),
            'filterSeccionEquipo' => $request->input('filterSeccionEquipo', ''),
            'programa_id' => $request->input('programa_id_derived'),
            'trayecto' => $request->input('trayecto_derived', ''),
            'trayecto_codigo' => $request->input('trayecto_derived_codigo', ''),
        ];

        if ($modoActualizacion) {
            $request->validate([
                'documentos' => 'nullable|array',
            ]);
        } else {
            $rules = $this->gestion->reglasValidacion($estadoForm, $user, true);
            $request->validate($rules, [
                'titulo.required' => 'El título del proyecto es obligatorio.',
                'resumen.required' => 'El resumen es obligatorio para los estudiantes.',
                'comunidad_id.required' => 'La comunidad es obligatoria.',
            ]);
        }

        $documentos = $esProfesor ? [] : $request->file('documentos', []);

        $this->gestion->guardar(
            (int) $id,
            $estadoForm,
            $user,
            $documentos,
            [],
        );

        if ($modoActualizacion) {
            $proyecto->update([
                'actualizado_por_estudiante' => true,
                'fecha_actualizacion_estudiante' => now(),
                'estado_validacion' => 'completado',
                'estado_logico' => true,
            ]);
        }

        return redirect()->route('proyectos.gestion')
            ->with('success', 'Proyecto actualizado con éxito.');
    }

    public function toggleStatus($id)
    {
        $this->gestion->alternarEstado((int) $id);
        return redirect()->route('proyectos.gestion')
            ->with('success', 'Estado del proyecto actualizado.');
    }

    public function approve($id)
    {
        try {
            $this->gestion->aprobar((int) $id);
            return redirect()->route('proyectos.gestion')
                ->with('success', 'Proyecto aprobado con éxito.');
        } catch (\Throwable $e) {
            return redirect()->route('proyectos.gestion')
                ->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|min:10',
        ], [
            'motivo.required' => 'Debe indicar el motivo de rechazo.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        try {
            $this->gestion->rechazar((int) $id, $request->input('motivo'));
            return redirect()->route('proyectos.gestion')
                ->with('success', 'Proyecto rechazado.');
        } catch (\Throwable $e) {
            return redirect()->route('proyectos.gestion')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->gestion->eliminar((int) $id);
        return redirect()->route('proyectos.gestion')
            ->with('success', 'Proyecto eliminado correctamente.');
    }

    public function registrarDesdeGrupo(Request $request, $grpCodigo)
    {
        $user = auth()->user();
        $proyecto = $this->gestion->registrarProyectoDesdeGrupo((int) $grpCodigo, $user);

        if (!$proyecto) {
            return redirect()->route('proyectos.gestion')
                ->with('error', 'No se pudo registrar el proyecto desde el grupo.');
        }

        return redirect()->route('proyectos.gestion.edit', $proyecto->id)
            ->with('success', 'Proyecto registrado desde el grupo. Complete los datos.');
    }

    // ─── Involucrados AJAX ───────────────────────────────────────────

    public function buscarInvolucrados(Request $request, $id)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }
        return response()->json($this->gestion->buscarInvolucrados($q)->values());
    }

    public function buscarPersonaPorCedula(Request $request)
    {
        $cedula = $request->get('cedula', '');
        if (strlen($cedula) < 3) {
            return response()->json(null);
        }
        return response()->json($this->gestion->buscarPersonaPorCedula($cedula));
    }

    public function buscarRoles(Request $request, $id)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 1) {
            return response()->json([]);
        }
        return response()->json($this->gestion->buscarRoles($q)->values());
    }

    public function agregarInvolucrado(Request $request, $id)
    {
        $request->validate([
            'involucrado_id' => 'required|integer',
            'roles' => 'nullable|array',
            'roles.*' => 'integer',
        ]);

        $this->gestion->agregarInvolucradoAProyecto(
            (int) $id,
            (int) $request->input('involucrado_id'),
            $request->input('roles', [])
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado agregado al proyecto.');
    }

    public function crearInvolucrado(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|min:2|max:255',
            'apellido' => 'required|min:2|max:255',
            'cedula' => 'required|min:5|max:20',
            'roles' => 'nullable|array',
            'roles.*' => 'integer',
        ]);

        $involucrado = $this->gestion->crearInvolucrado(
            $request->input('nombre'),
            $request->input('apellido'),
            $request->input('cedula')
        );

        $this->gestion->agregarInvolucradoAProyecto(
            (int) $id,
            $involucrado->id,
            $request->input('roles', [])
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $involucrado->id]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado creado y agregado al proyecto.');
    }

    public function quitarInvolucrado(Request $request, $id, $invId)
    {
        $this->gestion->quitarInvolucradoDeProyecto((int) $id, (int) $invId);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Involucrado eliminado del proyecto.');
    }

    public function agregarRolInvolucrado(Request $request, $id, $invId)
    {
        $request->validate([
            'rol_id' => 'required|integer',
        ]);

        $pivotId = $this->gestion->agregarInvolucradoAProyecto(
            (int) $id,
            (int) $invId,
            [(int) $request->input('rol_id')]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'pivot_id' => $pivotId]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Rol asignado al involucrado.');
    }

    public function quitarRolInvolucrado(Request $request, $id, $pivotId, $rolId)
    {
        $this->gestion->quitarRolDeInvolucrado((int) $pivotId, (int) $rolId);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('proyectos.gestion.edit', $id)
            ->with('success', 'Rol eliminado del involucrado.');
    }

    public function crearRol(Request $request)
    {
        $request->validate([
            'nombre' => 'required|min:2|max:255',
        ]);

        $rol = $this->gestion->crearRol($request->input('nombre'));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $rol->id, 'nombre' => $rol->nombre]);
        }
        return back()->with('success', 'Rol creado correctamente.');
    }

    // ─── Reporte Excel (Depósito de Proyectos) ──────────────────────────────

    /**
     * Genera y descarga el reporte Excel del Depósito de Proyectos.
     * Accesible únicamente para administrador y coordinador.
     */
    public function exportarExcel(Request $request)
    {
        $filtros = [
            'estado'    => $request->get('estado', ''),
            'comunidad' => $request->get('comunidad', ''),
        ];

        $datos   = $this->reporteDeposito->construirFilasReporte($filtros);
        $filas   = $datos['filas'];
        $maxInt  = $datos['maxIntegrantes'];
        $lapso   = $datos['lapsoMasActual']  ?? '';
        $pnf     = $datos['pnfPredominante'] ?? '';

        $writer  = new SpreadsheetMlWriter();
        $writer->setTitle('Deposito de Proyectos');

        // ── Calcular número total de columnas ──────────────────────────────
        // Fijas: N°, Sede, PNF, Trayecto, Sección, Lapso, Título, Comunidad, Equipo
        //       + (Nombre + Cédula) * maxInt  +  Tutor + Estado + Cant.Beneficiados
        $colsFijas       = 9;
        $colsIntegrantes = $maxInt * 2;
        $colsFinales     = 3;
        $totalCols       = $colsFijas + $colsIntegrantes + $colsFinales;

        // ── Fila de título ─────────────────────────────────────────────────
        $writer->addMergedTitleRow(
            'UPTP JUAN DE JESÚS MONTILLA – DEPÓSITO DE PROYECTOS',
            $totalCols,
            '#1F3864'
        );

        // ── Anchos de columna (puntos) ─────────────────────────────────────
        $widths = [
            25,   // N°
            90,   // Sede
            60,   // PNF
            60,   // Trayecto
            60,   // Sección
            80,   // Lapso
            200,  // Título
            100,  // Comunidad
            100,  // Equipo
        ];
        // Nombre + Cédula por integrante
        for ($i = 0; $i < $maxInt; $i++) {
            $widths[] = 130; // Nombre
            $widths[] = 70;  // Cédula
        }
        $widths[] = 130; // Tutor Académico
        $widths[] = 70;  // Estado
        $widths[] = 80;  // Cant. Beneficiados

        // ── Encabezados ───────────────────────────────────────────────────
        $headers = [
            'N°', 'Sede', 'PNF', 'Trayecto', 'Sección',
            'Lapso Académico', 'Título del Proyecto', 'Comunidad', 'Nombre del Equipo',
        ];
        for ($i = 1; $i <= $maxInt; $i++) {
            $headers[] = "Integrante {$i} – Nombre Completo";
            $headers[] = "Integrante {$i} – Cédula";
        }
        $headers[] = 'Tutor Académico';
        $headers[] = 'Estado de Validación';
        $headers[] = 'Cantidad de Beneficiados';

        $writer->addRow($headers, isHeader: true, height: 35, widths: $widths);

        // ── Filas de datos ────────────────────────────────────────────────
        foreach ($filas as $fila) {
            $celdas = [
                $fila['numero'],
                $fila['sede'],
                $fila['pnf'],
                $fila['trayecto'],
                $fila['seccion'],
                $fila['lapso'],
                $fila['titulo'],
                $fila['comunidad'],
                $fila['equipo'],
            ];

            // Integrantes (dinámico, rellena con vacíos hasta maxInt)
            $integrantes = $fila['integrantes'];
            for ($i = 0; $i < $maxInt; $i++) {
                $celdas[] = $integrantes[$i]['nombre'] ?? '';
                $celdas[] = $integrantes[$i]['cedula'] ?? '';
            }

            $celdas[] = $fila['tutor_academico'];
            $celdas[] = $fila['estado_validacion'];
            $celdas[] = $fila['cant_beneficiados']; // siempre vacío

            $writer->addRow($celdas, wrap: true);
        }

        // ── Generar nombre de archivo ──────────────────────────────────────
        // Formato: "DEPOSITO PROYECTOS INFORMATICA 2024 II.xls"
        // Si no hay PNF/lapso disponible, usa fecha como fallback.
        $partesPnf   = $pnf   !== '' ? ' ' . strtoupper($pnf)   : '';
        $partesLapso = $lapso !== '' ? ' ' . strtoupper($lapso) : (' ' . now()->format('Y'));
        $filename = 'DEPOSITO PROYECTOS' . $partesPnf . $partesLapso . '.xls';

        return $writer->download($filename);
    }

    public function solvencia($id)
    {
        try {
            $datos = $this->gestion->datosSolvencia((int) $id);
            $now = now();
            $folio = 'SOL-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT) . '-' . $now->format('Y');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.solvencia', [
                'folio' => $folio,
                'estudiante_nombre' => $datos['estudiante_nombre'],
                'estudiante_cedula' => $datos['estudiante_cedula'],
                'titulo_proyecto' => $datos['titulo_proyecto'],
                'pnf' => $datos['pnf'],
                'trayecto' => $datos['trayecto'],
                'seccion' => $datos['seccion'],
                'lapso' => $datos['lapso'],
                'dia' => $now->day,
                'mes' => ucfirst($now->translatedFormat('F')),
                'anio' => $now->year,
            ]);

            return $pdf->download("solvencia_{$folio}.pdf");
        } catch (\RuntimeException $e) {
            return redirect()->route('proyectos.gestion')
                ->with('error', $e->getMessage());
        }
    }

    protected function buildEstadoFromDatos(array $datos): array
    {
        return [
            'equipo_seccion_clave' => $datos['equipo_seccion_clave'] ?? '',
            'filterLapsoEquipo' => $datos['filterLapsoEquipo'] ?? '',
            'filterProgramaEquipo' => $datos['filterProgramaEquipo'] ?? '',
            'filterSeccionEquipo' => $datos['filterSeccionEquipo'] ?? '',
            'programa_id' => $datos['programa_id_derived'] ?? null,
            'trayecto' => $datos['trayecto_derived'] ?? '',
            'trayecto_codigo' => $datos['trayecto_derived_codigo'] ?? '',
            'titulo' => $datos['titulo'] ?? '',
            'resumen' => $datos['resumen'] ?? '',
            'linea_investigacion_id' => $datos['linea_investigacion_id'] ?? '',
            'metodologia_id' => $datos['metodologia_id'] ?? '',
            'tipo_publicacion_id' => $datos['tipo_publicacion_id'] ?? '',
            'tipo_investigacion_id' => $datos['tipo_investigacion_id'] ?? '',
            'objetivo_investigacion_id' => $datos['objetivo_investigacion_id'] ?? '',
            'comunidad_id' => $datos['comunidad_id'] ?? '',
        ];
    }

    protected function cargarMiembrosGrupo(string $clave, array &$miembros): void
    {
        try {
            $integrantes = $this->grupos->integrantes($clave);
            $miembros = $integrantes->map(fn($m) => [
                'cedula' => $m->cedula,
                'nombre' => $m->nombre,
                'apellido' => $m->apellido ?? '',
                'rol_id' => $m->rol_id ?? 0,
            ])->toArray();
        } catch (\Throwable) {
            $miembros = [];
        }
    }
}
