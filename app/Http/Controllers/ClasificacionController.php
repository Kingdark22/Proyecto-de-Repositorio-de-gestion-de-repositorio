<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Componente;
use App\Models\ComponentePrograma;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\ObjetivoInvestigacion;
use App\Models\TipoInvestigacion;
use App\Repositories\ComunidadRepository;
use App\Repositories\CatalogoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClasificacionController extends Controller
{
    public function __construct(
        protected ComunidadRepository $comunidadRepo,
        protected CatalogoRepository $catalogoRepo,
    ) {}

    public function index()
    {
        $estados = Estado::orderBy('est_nombre')->get();
        $programas = \App\Models\Programa::orderBy('pro_siglas')->get();

        return view('clasificacion.index', compact('estados', 'programas'));
    }

    public function listar(Request $request, string $tipo)
    {
        $search = trim($request->get('q', $request->get('search', '')));
        $page = (int) $request->get('page', 1);

        return match ($tipo) {
            'comunidades' => $this->listarComunidades($search, $page),
            'lineas' => $this->listarLineas($search, $page),
            'tipos' => $this->listarTipos($search, $page),
            'metodologias' => $this->listarMetodologias($search, $page),
            'objetivos' => $this->listarObjetivos($search, $page),
            'componentes' => $this->listarComponentes($search, $page),
            default => response()->json(['error' => 'Tipo no válido'], 404),
        };
    }

    public function listados()
    {
        $comunidades = Comunidad::with('direccion.municipio.estado')
            ->orderBy('com_nombre')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'detalle' => trim(implode(' · ', array_filter([
                    $c->rif,
                    $c->correo,
                    optional(optional($c->direccion)->municipio)->mun_nombre,
                    optional(optional(optional($c->direccion)->municipio)->estado)->est_nombre,
                ]))),
            ]);

        $lineas = LineaInvestigacion::orderBy('nombre_investigacion')
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'nombre' => $l->nombre_investigacion,
                'detalle' => trim(($l->area_de_investigacion ?? '') . ($l->activo ? '' : ' · INACTIVA')),
            ]);

        $simple = fn ($model, $extra) => $model::orderBy('nombre')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'nombre' => $m->nombre,
                'detalle' => $extra($m) ?? '',
            ]);

        $tipos = $simple(TipoInvestigacion::class, fn ($m) => $m->descripcion);
        $metodologias = $simple(MetodologiaInvestigacion::class, fn ($m) => $m->descripcion);
        $objetivos = $simple(ObjetivoInvestigacion::class, fn ($m) => $m->descripcion);

        $componentes = Componente::where('estado_logico', true)
            ->orderBy('comp_nombre')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'detalle' => trim(implode(' · ', array_filter([
                    strtoupper((string) $c->tipo_archivo),
                    $c->tamano_maximo_mb . ' MB',
                    $c->es_obligatorio ? 'Obligatorio' : null,
                ]))),
            ]);

        return response()->json(compact('comunidades', 'lineas', 'tipos', 'metodologias', 'objetivos', 'componentes'));
    }

    public function guardar(Request $request, string $tipo)
    {
        $validator = Validator::make(
            $request->all(),
            $this->reglas($tipo),
            $this->mensajes($tipo),
            $this->atributos($tipo)
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise los campos marcados.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        match ($tipo) {
            'comunidades' => $this->guardarComunidad($validated),
            'lineas' => $this->guardarLinea($validated),
            'tipos' => TipoInvestigacion::create($validated),
            'metodologias' => MetodologiaInvestigacion::create($validated),
            'objetivos' => ObjetivoInvestigacion::create($validated),
            'componentes' => $this->guardarComponente($validated),
            default => response()->json(['error' => 'Tipo no válido'], 404),
        };

        return response()->json(['success' => true, 'message' => 'Registro creado correctamente.']);
    }

    public function eliminar(string $tipo, int $id)
    {
        $model = match ($tipo) {
            'comunidades' => Comunidad::findOrFail($id),
            'lineas' => LineaInvestigacion::findOrFail($id),
            'tipos' => TipoInvestigacion::findOrFail($id),
            'metodologias' => MetodologiaInvestigacion::findOrFail($id),
            'objetivos' => ObjetivoInvestigacion::findOrFail($id),
            'componentes' => Componente::findOrFail($id),
            default => null,
        };

        if (! $model) {
            return response()->json(['success' => false, 'message' => 'Tipo no válido.']);
        }

        $model->delete();

        return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
    }

    public function editar(string $tipo, int $id)
    {
        $model = match ($tipo) {
            'comunidades' => Comunidad::with('direccion.municipio.estado')->findOrFail($id),
            'lineas' => LineaInvestigacion::findOrFail($id),
            'tipos' => TipoInvestigacion::findOrFail($id),
            'metodologias' => MetodologiaInvestigacion::findOrFail($id),
            'objetivos' => ObjetivoInvestigacion::findOrFail($id),
            'componentes' => Componente::findOrFail($id),
            default => null,
        };

        if (!$model) {
            return response()->json(['success' => false, 'message' => 'Tipo no válido.'], 404);
        }

        $data = match ($tipo) {
            'comunidades' => [
                'nombre' => $model->nombre,
                'rif_letra' => '',
                'rif_numero' => '',
                'correo' => $model->correo ?? '',
                'numero_telefono' => '',
                'prefijo_telefono' => '',
                'estado_id' => optional(optional($model->direccion)->municipio)->est_codigo ?? '',
                'municipio_id' => optional($model->direccion)->mun_codigo ?? '',
                'dir_nombre' => optional($model->direccion)->dir_calle ?? '',
            ],
            'lineas' => [
                'nombre_investigacion' => $model->nombre_investigacion,
                'area_de_investigacion' => $model->area_de_investigacion,
                'programa_id' => $model->programa_id,
                'descripcion' => $model->descripcion ?? '',
            ],
            'tipos', 'metodologias', 'objetivos' => [
                'nombre' => $model->nombre,
                'descripcion' => $model->descripcion ?? '',
            ],
            'componentes' => [
                'nombre' => $model->nombre,
                'tipo_archivo' => $model->tipo_archivo,
                'tamano_maximo_mb' => $model->tamano_maximo_mb,
                'es_obligatorio' => $model->es_obligatorio ? 1 : 0,
            ],
            default => [],
        };

        if ($tipo === 'comunidades') {
            if (!empty($model->rif)) {
                $parts = explode('-', $model->rif, 2);
                $data['rif_letra'] = $parts[0] ?? '';
                $data['rif_numero'] = $parts[1] ?? '';
            }
            if (!empty($model->numero_telefono)) {
                $data['prefijo_telefono'] = substr($model->numero_telefono, 0, 4);
                $data['numero_telefono'] = substr($model->numero_telefono, 4);
            }
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function actualizar(Request $request, string $tipo, int $id)
    {
        $validator = Validator::make(
            $request->all(),
            $this->reglas($tipo),
            $this->mensajes($tipo),
            $this->atributos($tipo)
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise los campos marcados.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        match ($tipo) {
            'comunidades' => $this->actualizarComunidad($validated, $id),
            'lineas' => LineaInvestigacion::findOrFail($id)->update($validated),
            'tipos' => TipoInvestigacion::findOrFail($id)->update($validated),
            'metodologias' => MetodologiaInvestigacion::findOrFail($id)->update($validated),
            'objetivos' => ObjetivoInvestigacion::findOrFail($id)->update($validated),
            'componentes' => $this->actualizarComponente($validated, $id),
            default => null,
        };

        return response()->json(['success' => true, 'message' => 'Registro actualizado correctamente.']);
    }

    protected function actualizarComunidad(array $data, int $id): void
    {
        $comunidad = Comunidad::findOrFail($id);

        $rif = trim(($data['rif_letra'] ?? '') . '-' . ($data['rif_numero'] ?? ''));
        $rif = $rif === '-' ? null : $rif;

        $telefono = trim(($data['prefijo_telefono'] ?? '') . ($data['numero_telefono'] ?? ''));
        $telefono = $telefono ? $telefono : null;

        if ($comunidad->com_dir_codigo) {
            \App\Models\Direccion::where('dir_codigo', $comunidad->com_dir_codigo)->update([
                'mun_codigo' => $data['municipio_id'],
                'dir_calle' => $data['dir_nombre'],
            ]);
        } else {
            $dir = \App\Models\Direccion::create([
                'mun_codigo' => $data['municipio_id'],
                'dir_calle' => $data['dir_nombre'],
                'dir_parroquia' => 'N/A',
                'dir_sector' => 'N/A',
            ]);
            $comunidad->update(['direccion_id' => $dir->dir_codigo]);
        }

        $comunidad->update([
            'nombre' => $data['nombre'],
            'rif' => $rif,
            'correo' => $data['correo'] ?? null,
            'numero_telefono' => $telefono,
        ]);
    }

    protected function actualizarComponente(array $data, int $id): void
    {
        Componente::findOrFail($id)->update([
            'nombre' => $data['nombre'],
            'tipo_archivo' => $data['tipo_archivo'],
            'tamano_maximo_mb' => $data['tamano_maximo_mb'],
            'es_obligatorio' => ($data['es_obligatorio'] ?? 0) == 1,
        ]);
    }

    public function verificar(Request $request, string $tipo)
    {
        $nombre = trim($request->get('nombre', ''));
        if ($nombre === '') {
            return response()->json(['existe' => false]);
        }

        $existe = match ($tipo) {
            'comunidades' => Comunidad::where('nombre', 'ilike', $nombre)->exists(),
            'lineas' => LineaInvestigacion::where('nombre_investigacion', 'ilike', $nombre)->exists(),
            'tipos' => TipoInvestigacion::where('nombre', 'ilike', $nombre)->exists(),
            'metodologias' => MetodologiaInvestigacion::where('nombre', 'ilike', $nombre)->exists(),
            'objetivos' => ObjetivoInvestigacion::where('nombre', 'ilike', $nombre)->exists(),
            'componentes' => Componente::where('comp_nombre', 'ilike', $nombre)->exists(),
            default => false,
        };

        return response()->json(['existe' => $existe]);
    }

    protected function mensajes(string $tipo): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre no debe exceder los :max caracteres.',
            'nombre_investigacion.required' => 'El nombre de la línea es obligatorio.',
            'nombre_investigacion.min' => 'El nombre debe tener al menos :min caracteres.',
            'nombre_investigacion.max' => 'El nombre no debe exceder los :max caracteres.',
            'area_de_investigacion.required' => 'El área académica es obligatoria.',
            'area_de_investigacion.min' => 'El área debe tener al menos :min caracteres.',
            'area_de_investigacion.max' => 'El área no debe exceder los :max caracteres.',
            'programa_id.required' => 'Debe seleccionar un programa.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos :min caracteres.',
            'descripcion.max' => 'La descripción no debe exceder los :max caracteres.',
            'estado_id.required' => 'Debe seleccionar un estado.',
            'municipio_id.required' => 'Debe seleccionar un municipio.',
            'dir_nombre.required' => 'La dirección es obligatoria.',
            'dir_nombre.min' => 'La dirección debe tener al menos :min caracteres.',
            'dir_nombre.max' => 'La dirección no debe exceder los :max caracteres.',
            'tipo_archivo.required' => 'Debe seleccionar un tipo de archivo.',
            'tamano_maximo_mb.required' => 'El tamaño máximo es obligatorio.',
            'tamano_maximo_mb.integer' => 'El tamaño debe ser un número entero.',
            'tamano_maximo_mb.min' => 'El tamaño mínimo es :min MB.',
            'tamano_maximo_mb.max' => 'El tamaño máximo es :max MB.',
        ];
    }

    protected function atributos(string $tipo): array
    {
        return [
            'nombre' => 'nombre',
            'nombre_investigacion' => 'nombre de la línea',
            'area_de_investigacion' => 'área académica',
            'programa_id' => 'programa',
            'descripcion' => 'descripción',
            'estado_id' => 'estado',
            'municipio_id' => 'municipio',
            'dir_nombre' => 'dirección',
            'tipo_archivo' => 'tipo de archivo',
            'tamano_maximo_mb' => 'tamaño máximo',
            'rif_letra' => 'letra del RIF',
            'rif_numero' => 'número del RIF',
            'correo' => 'correo',
            'prefijo_telefono' => 'prefijo telefónico',
            'numero_telefono' => 'número de teléfono',
        ];
    }

    protected function reglas(string $tipo): array
    {
        return match ($tipo) {
            'comunidades' => [
                'nombre' => 'required|string|min:3|max:100',
                'estado_id' => 'required|string',
                'municipio_id' => 'required|string',
                'dir_nombre' => 'required|string|min:3|max:200',
                'rif_letra' => 'nullable|string|size:1',
                'rif_numero' => 'nullable|string|max:9',
                'correo' => 'nullable|email|max:40',
                'prefijo_telefono' => 'nullable|string|max:4',
                'numero_telefono' => 'nullable|string|max:7',
            ],
            'lineas' => [
                'nombre_investigacion' => 'required|string|min:3|max:100',
                'area_de_investigacion' => 'required|string|min:3|max:100',
                'programa_id' => 'required|string',
                'descripcion' => 'required|string|min:10|max:500',
            ],
            'tipos' => [
                'nombre' => 'required|string|min:3|max:200',
                'descripcion' => 'nullable|string',
            ],
            'metodologias' => [
                'nombre' => 'required|string|min:3|max:200',
                'descripcion' => 'nullable|string',
            ],
            'objetivos' => [
                'nombre' => 'required|string|min:3|max:200',
                'descripcion' => 'nullable|string',
            ],
            'componentes' => [
                'nombre' => 'required|string|min:3|max:200',
                'tipo_archivo' => 'required|string',
                'tamano_maximo_mb' => 'required|integer|min:1|max:200',
                'es_obligatorio' => 'nullable|boolean',
            ],
            default => [],
        };
    }

    protected function guardarComunidad(array $data): void
    {
        $rif = trim(($data['rif_letra'] ?? '') . '-' . ($data['rif_numero'] ?? ''));
        $rif = $rif === '-' ? null : $rif;

        $telefono = trim(($data['prefijo_telefono'] ?? '') . ($data['numero_telefono'] ?? ''));
        $telefono = $telefono ? $telefono : null;

        $dir = \App\Models\Direccion::create([
            'mun_codigo' => $data['municipio_id'],
            'dir_calle' => $data['dir_nombre'],
            'dir_parroquia' => 'N/A',
            'dir_sector' => 'N/A',
        ]);

        Comunidad::create([
            'nombre' => $data['nombre'],
            'rif' => $rif,
            'correo' => $data['correo'] ?? null,
            'numero_telefono' => $telefono,
            'direccion_id' => $dir->dir_codigo,
            'estado_logico' => true,
        ]);
    }

    protected function guardarLinea(array $data): void
    {
        LineaInvestigacion::create([
            'nombre_investigacion' => $data['nombre_investigacion'],
            'area_de_investigacion' => $data['area_de_investigacion'],
            'programa_id' => $data['programa_id'],
            'descripcion' => $data['descripcion'],
            'activo' => true,
        ]);
    }

    protected function guardarComponente(array $data): void
    {
        Componente::create([
            'nombre' => $data['nombre'],
            'tipo_archivo' => $data['tipo_archivo'],
            'tamano_maximo_mb' => $data['tamano_maximo_mb'],
            'es_obligatorio' => ($data['es_obligatorio'] ?? 0) == 1,
            'estado_logico' => true,
        ]);
    }

    protected function listarComunidades(string $search, int $page)
    {
        $items = $this->comunidadRepo->paginate(['search' => $search], $page);
        $esAdminCoord = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador');

        return view('clasificacion.partials._comunidades', compact('items', 'esAdminCoord'))->render();
    }

    protected function listarLineas(string $search, int $page)
    {
        $query = LineaInvestigacion::query();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_investigacion', 'ilike', "%{$search}%")
                  ->orWhere('area_de_investigacion', 'ilike', "%{$search}%");
            });
        }
        $items = $query->orderBy('nombre_investigacion')->paginate(10, ['*'], 'page', $page);
        $esAdminCoord = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador');

        return view('clasificacion.partials._lineas', compact('items', 'esAdminCoord'))->render();
    }

    protected function listarTipos(string $search, int $page)
    {
        $query = TipoInvestigacion::query();
        if ($search !== '') {
            $query->where('nombre', 'ilike', "%{$search}%");
        }
        $items = $query->orderBy('nombre')->paginate(10, ['*'], 'page', $page);
        $esAdminCoord = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador');

        return view('clasificacion.partials._tipos', compact('items', 'esAdminCoord'))->render();
    }

    protected function listarMetodologias(string $search, int $page)
    {
        $query = MetodologiaInvestigacion::query();
        if ($search !== '') {
            $query->where('nombre', 'ilike', "%{$search}%");
        }
        $items = $query->orderBy('nombre')->paginate(10, ['*'], 'page', $page);
        $esAdminCoord = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador');

        return view('clasificacion.partials._metodologias', compact('items', 'esAdminCoord'))->render();
    }

    protected function listarObjetivos(string $search, int $page)
    {
        $query = ObjetivoInvestigacion::query();
        if ($search !== '') {
            $query->where('nombre', 'ilike', "%{$search}%");
        }
        $items = $query->orderBy('nombre')->paginate(10, ['*'], 'page', $page);
        $esAdminCoord = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador');

        return view('clasificacion.partials._objetivos', compact('items', 'esAdminCoord'))->render();
    }

    protected function listarComponentes(string $search, int $page)
    {
        $query = Componente::with('programas')
            ->where('estado_logico', true);
        if ($search !== '') {
            $query->where('comp_nombre', 'ilike', "%{$search}%");
        }
        $items = $query->orderBy('comp_nombre')->paginate(10, ['*'], 'page', $page);
        $esAdminCoord = auth()->user() && auth()->user()->hasRole('administrador', 'coordinador');

        return view('clasificacion.partials._componentes', compact('items', 'esAdminCoord'))->render();
    }

    public function vinculacionData()
    {
        $componentes = Componente::query()->where('estado_logico', true)->orderBy('nombre')->get();
        $programas = $this->catalogoRepo->programasDisponibles();
        $compIds = $componentes->pluck('id')->toArray();
        $asignaciones = ComponentePrograma::whereIn('comp_codigo', $compIds)->get();

        $pnfRows = [];
        foreach ($programas as $prog) {
            $proCodigo = (int) $prog->pro_codigo;
            $pnfTrayectos = $this->catalogoRepo->trayectosPorPrograma($proCodigo);
            $asigsEstePnf = $asignaciones->where('pro_codigo', $proCodigo);

            $trayectosData = [];
            foreach ($pnfTrayectos as $tra) {
                $traCodigo = (string) $tra->tra_codigo;
                $asig = $asigsEstePnf->firstWhere('tra_codigo', $traCodigo);
                $trayectosData[$traCodigo] = [
                    'nombre' => $tra->tra_nombre ?? $traCodigo,
                    'selected' => $asig !== null,
                ];
            }

            $pnfRows[$proCodigo] = [
                'pro_codigo' => $proCodigo,
                'pro_siglas' => $prog->pro_siglas ?? $prog->pro_nombre,
                'activo' => $asigsEstePnf->isNotEmpty(),
                'trayectos' => $trayectosData,
            ];
        }

        return response()->json([
            'componentes' => $componentes->map(fn($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
            ])->toArray(),
            'pnfRows' => array_values($pnfRows),
        ]);
    }

    public function vinculacionGuardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'componente_ids' => 'required|array|min:1',
            'componente_ids.*' => 'required|integer|min:1|exists:componentes,comp_codigo',
        ], [
            'componente_ids.required' => 'Seleccione al menos un componente.',
            'componente_ids.min' => 'Seleccione al menos un componente.',
            'componente_ids.*.exists' => 'Uno de los componentes seleccionados no existe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise los campos marcados.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $componenteIds = $request->input('componente_ids', []);
        $pnfsActivos = $request->input('pnf_activo', []);
        $trayectosSelected = $request->input('tra_selected', []);
        $totalVinculaciones = 0;

        foreach ($componenteIds as $compCodigo) {
            $compCodigo = (int) $compCodigo;
            foreach ($pnfsActivos as $proCodigo => $activo) {
                if ((int) $activo !== 1) continue;
                $proCodigo = (int) $proCodigo;
                $trayectos = $trayectosSelected[$proCodigo] ?? [];
                foreach ($trayectos as $traCodigo => $selected) {
                    if ((int) $selected !== 1) continue;
                    $existe = ComponentePrograma::where('comp_codigo', $compCodigo)
                        ->where('pro_codigo', $proCodigo)
                        ->where('tra_codigo', (string) $traCodigo)
                        ->exists();
                    if (!$existe) {
                        ComponentePrograma::create([
                            'comp_codigo' => $compCodigo,
                            'pro_codigo' => $proCodigo,
                            'tra_codigo' => (string) $traCodigo,
                        ]);
                        $totalVinculaciones++;
                    }
                }
            }
        }

        $numComponentes = count($componenteIds);
        return response()->json([
            'success' => true,
            'message' => "Vinculación guardada: {$totalVinculaciones} registro(s) para {$numComponentes} componente(s).",
        ]);
    }
}
