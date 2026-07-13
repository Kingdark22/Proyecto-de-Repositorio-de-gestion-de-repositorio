<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSafeNotify;
use App\Models\Comunidad;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\LapsoAcademico;
use App\Models\Municipio;
use App\Models\Proyecto;
use App\Models\TituloVinculacion;
use App\Models\Vinculacion;
use App\Services\IntranetEquipoSeccionService;
use App\Services\UnicidadNombreService;
use App\Services\ValidacionRifService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class VinculacionManager extends Component
{
    use WithPagination;
    use WithSafeNotify;

    public string $search = '';

    // Búsqueda del listado
    public string $busquedaListado = '';

    // Wizard
    public bool $mostrarWizard = false;
    public int $pasoActual = 1;

    // Reporte modal
    public bool $mostrarModalReporte = false;
    public string $tipoReporte = 'titulo';
    public string $reporteTituloId = '';
    public string $reporteLapsoId = '';
    public array $lapsosReporte = [];
    public array $titulosReporte = [];

    // Paso 1: Proyectos seleccionados
    public array $selectedProjects = [];
    public bool $selectAll = false;

    // Paso 2: Título seleccionado (un solo título para todos los proyectos seleccionados)
    public string $tituloSeleccionado = '';
    public string $nuevoTitulo = '';

    // Paso 3: Comunidad (opcional)
    public string $comunidadId = '';
    public ?Comunidad $comunidadSeleccionada = null;

    // Títulos disponibles
    public array $titulosDisponibles = [];

    // Modal comunidad
    public bool $mostrarModalComunidad = false;
    public string $buscarComunidad = '';
    public Collection $comunidadesEncontradas;
    public string $modalComunidadNombre = '';
    public ?string $modalComunidadNombreStatus = null;
    public string $modalComunidadRifLetra = 'J';
    public string $modalComunidadRifNumero = '';
    public ?string $modalComunidadRifDigito = null;
    public ?string $modalComunidadRifStatus = null;
    public ?string $modalComunidadRifError = null;
    public string $modalComunidadCorreo = '';
    public string $modalComunidadTelefono = '';
    public string $modalComunidadPrefijo = '0424';
    public string $modalComunidadEstadoId = '';
    public string $modalComunidadMunicipioId = '';
    public string $modalComunidadDirNombre = '';
    public Collection $municipiosFiltrados;
    public Collection $estados;

    // Modal detalle proyecto
    public bool $mostrarModalDetalle = false;
    public ?Proyecto $proyectoDetalle = null;

    public function mount(): void
    {
        $this->comunidadesEncontradas = collect();
        $this->municipiosFiltrados = collect();
        $this->estados = Estado::orderBy('est_nombre')->get();
        $this->cargarTitulos();
    }

    protected function cargarTitulos(): void
    {
        try {
            $this->titulosDisponibles = TituloVinculacion::where('tiv_estado_logico', true)
                ->orderBy('tiv_titulo')
                ->pluck('tiv_titulo', 'tiv_codigo')
                ->toArray();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error cargando títulos: ' . $e->getMessage());
            $this->titulosDisponibles = [];
        }
    }

    // ─── Wizard ────────────────────────────────────────────────

    public function abrirWizard(): void
    {
        $this->cargarTitulos();
        $this->limpiar();
        $this->pasoActual = 1;
        $this->mostrarWizard = true;
    }

    public function cerrarWizard(): void
    {
        $this->mostrarWizard = false;
        $this->limpiar();
    }

    public function siguientePaso(): void
    {
        if ($this->pasoActual === 1) {
            if (empty($this->selectedProjects)) {
                $this->safeDispatch('error', 'Seleccione al menos un proyecto.');
                return;
            }
        }
        if ($this->pasoActual === 2) {
            $tituloId = $this->tituloSeleccionado;
            if ($tituloId === '') {
                $this->safeDispatch('error', 'Seleccione o cree un título de vinculación.');
                return;
            }
            if ($tituloId === 'nuevo') {
                $nombre = strtoupper(trim($this->nuevoTitulo));
                if (mb_strlen($nombre) < 3) {
                    $this->safeDispatch('error', 'El título nuevo debe tener al menos 3 caracteres.');
                    return;
                }
            }
        }
        if ($this->pasoActual < 4) {
            $this->pasoActual++;
        }
    }

    public function pasoAnterior(): void
    {
        if ($this->pasoActual > 1) {
            $this->pasoActual--;
        }
    }

    public function pasoEspecifico(int $paso): void
    {
        if ($paso >= 1 && $paso <= 4) {
            $this->pasoActual = $paso;
        }
    }

    // ─── Modal Comunidad ──────────────────────────────────────

    public function updatedBuscarComunidad(): void
    {
        $q = trim($this->buscarComunidad);
        if ($q === '') {
            $this->comunidadesEncontradas = collect();
            return;
        }
        $this->comunidadesEncontradas = Comunidad::whereRaw('com_nombre ILIKE ?', ["%{$q}%"])
            ->orWhereRaw('com_rif ILIKE ?', ["%{$q}%"])
            ->orderByRaw('com_nombre')
            ->get();
    }

    public function abrirModalComunidad(): void
    {
        $this->mostrarModalComunidad = true;
        $this->modalComunidadNombre = '';
        $this->modalComunidadNombreStatus = null;
        $this->modalComunidadRifLetra = 'J';
        $this->modalComunidadRifNumero = '';
        $this->modalComunidadRifDigito = null;
        $this->modalComunidadRifStatus = null;
        $this->modalComunidadRifError = null;
        $this->modalComunidadCorreo = '';
        $this->modalComunidadTelefono = '';
        $this->modalComunidadPrefijo = '0424';
        $this->modalComunidadEstadoId = '';
        $this->modalComunidadMunicipioId = '';
        $this->modalComunidadDirNombre = '';
        $this->municipiosFiltrados = collect();
        $this->buscarComunidad = '';
        $this->comunidadesEncontradas = collect();
    }

    public function cerrarModalComunidad(): void
    {
        $this->mostrarModalComunidad = false;
    }

    public function seleccionarComunidadModal(string $id): void
    {
        $this->comunidadId = $id;
        $this->comunidadSeleccionada = Comunidad::find((int) $id);
        $this->cerrarModalComunidad();
    }

    public function guardarComunidadModal(): void
    {
        $this->validate([
            'modalComunidadNombre' => 'required|string|max:255',
            'modalComunidadEstadoId' => 'required|integer|exists:estados,est_codigo',
            'modalComunidadMunicipioId' => 'required|integer|exists:municipios,mun_codigo',
            'modalComunidadDirNombre' => 'required|string|max:500',
        ], [
            'modalComunidadNombre.required' => 'El nombre de la comunidad es obligatorio.',
            'modalComunidadEstadoId.required' => 'Seleccione un estado.',
            'modalComunidadMunicipioId.required' => 'Seleccione un municipio.',
            'modalComunidadDirNombre.required' => 'La dirección exacta es obligatoria.',
        ], [
            'modalComunidadNombre' => 'nombre de la comunidad',
            'modalComunidadEstadoId' => 'estado',
            'modalComunidadMunicipioId' => 'municipio',
            'modalComunidadDirNombre' => 'dirección',
        ]);

        if ($this->modalComunidadNombreStatus === 'no_disponible') {
            $this->addError('modalComunidadNombre', 'Este nombre ya está en uso.');
            return;
        }

        if ($this->modalComunidadRifNumero !== '' && strlen($this->modalComunidadRifNumero) < 9) {
            $this->addError('modalComunidadRifNumero', 'El RIF debe tener exactamente 9 dígitos.');
            return;
        }

        if ($this->modalComunidadRifNumero !== '' && $this->modalComunidadRifStatus !== 'valido') {
            $this->addError('modalComunidadRifNumero', 'El RIF ingresado no es válido.');
            return;
        }

        $rif = null;
        if ($this->modalComunidadRifNumero !== '') {
            $rif = "{$this->modalComunidadRifLetra}-{$this->modalComunidadRifNumero}-{$this->modalComunidadRifDigito}";
        }

        $telefono = null;
        if ($this->modalComunidadTelefono !== '') {
            $telefono = $this->modalComunidadPrefijo . '-' . $this->modalComunidadTelefono;
        }

        $direccion = Direccion::create([
            'dir_calle' => $this->modalComunidadDirNombre,
            'mun_codigo' => (int) $this->modalComunidadMunicipioId,
            'dir_parroquia' => '',
            'dir_sector' => '',
        ]);

        $comunidad = Comunidad::create([
            'nombre' => $this->modalComunidadNombre,
            'rif' => $rif,
            'correo' => $this->modalComunidadCorreo !== '' ? $this->modalComunidadCorreo : null,
            'numero_telefono' => $telefono,
            'direccion_id' => $direccion->dir_codigo,
        ]);

        $this->comunidadId = (string) $comunidad->id;
        $this->comunidadSeleccionada = $comunidad;
        $this->cerrarModalComunidad();
    }

    public function quitarComunidad(): void
    {
        $this->comunidadId = '';
        $this->comunidadSeleccionada = null;
    }

    public function updatedModalComunidadNombre(): void
    {
        // Filtrar caracteres no permitidos (solo letras, números y espacios)
        $this->modalComunidadNombre = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ0-9\s]/', '', $this->modalComunidadNombre);

        if (strlen(trim($this->modalComunidadNombre)) < 3) {
            $this->modalComunidadNombreStatus = null;
            $this->resetValidation('modalComunidadNombre');
            return;
        }
        $this->modalComunidadNombreStatus = app(UnicidadNombreService::class)->check(
            Comunidad::class,
            'nombre',
            $this->modalComunidadNombre,
        ) ? 'disponible' : 'no_disponible';
        if ($this->modalComunidadNombreStatus === 'disponible') {
            $this->resetValidation('modalComunidadNombre');
        }
    }

    public function updatedModalComunidadRifNumero(ValidacionRifService $rifService): void
    {
        $num = preg_replace('/\D/', '', $this->modalComunidadRifNumero);
        $this->modalComunidadRifNumero = $num;
        if ($num === '') {
            $this->modalComunidadRifDigito = null;
            $this->modalComunidadRifStatus = null;
            $this->modalComunidadRifError = null;
            $this->resetValidation('modalComunidadRifNumero');
            return;
        }
        if (strlen($num) < 9) {
            $this->modalComunidadRifDigito = null;
            $this->modalComunidadRifStatus = 'invalido';
            $this->modalComunidadRifError = 'Debe tener 9 dígitos';
            $this->resetValidation('modalComunidadRifNumero');
            return;
        }
        $this->modalComunidadRifDigito = $rifService->calcularDigito($this->modalComunidadRifLetra, $num);
        $this->modalComunidadRifStatus = $this->modalComunidadRifDigito !== null ? 'valido' : 'invalido';
        $this->modalComunidadRifError = $this->modalComunidadRifStatus === 'valido' ? null : 'RIF inválido';
        if ($this->modalComunidadRifStatus === 'valido') {
            $this->resetValidation('modalComunidadRifNumero');
        }
    }

    public function updatedModalComunidadRifLetra(ValidacionRifService $rifService): void
    {
        if (strlen($this->modalComunidadRifNumero) >= 9) {
            $this->updatedModalComunidadRifNumero($rifService);
        }
    }

    public function updatedModalComunidadEstadoId(): void
    {
        $this->modalComunidadMunicipioId = '';
        if ($this->modalComunidadEstadoId === '') {
            $this->municipiosFiltrados = collect();
            return;
        }
        $this->municipiosFiltrados = Municipio::where('est_codigo', (int) $this->modalComunidadEstadoId)
            ->orderBy('mun_nombre')
            ->get();
    }

    // ─── Comunidad ──────────────────────────────────────────────

    public function updatedComunidadId(): void
    {
        if ($this->comunidadId !== '') {
            $this->comunidadSeleccionada = Comunidad::find((int) $this->comunidadId);
        } else {
            $this->comunidadSeleccionada = null;
        }
    }

    // ─── Selección proyectos ──────────────────────────────────

    public function updatedSelectAll(): void
    {
        if (!$this->selectAll) {
            $this->selectedProjects = [];
            return;
        }
        $query = Proyecto::where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);
        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('pry_resumen ILIKE ?', [$term])
                  ->orWhereRaw('pry_direccion_logica ILIKE ?', [$term])
                  ->orWhereRaw('pry_creador_cedula ILIKE ?', [$term])
                  ->orWhereHas('comunidad', fn($cq) => $cq->whereRaw('com_nombre ILIKE ?', [$term]));
            });
        }
        $this->selectedProjects = $query->pluck('id')->toArray();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleProject(int $projectId): void
    {
        if (in_array($projectId, $this->selectedProjects)) {
            $this->selectedProjects = array_values(array_diff($this->selectedProjects, [$projectId]));
        } else {
            $this->selectedProjects[] = $projectId;
        }
    }

    public function toggleSelectAll(): void
    {
        $total = $this->selectedProjectsCount();
        if ($total > 0 && count($this->selectedProjects) === $total) {
            $this->selectedProjects = [];
            return;
        }
        $this->selectAll = true;
        $this->updatedSelectAll();
    }

    protected function selectedProjectsCount(): int
    {
        $query = Proyecto::where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);
        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('pry_resumen ILIKE ?', [$term])
                  ->orWhereRaw('pry_direccion_logica ILIKE ?', [$term])
                  ->orWhereRaw('pry_creador_cedula ILIKE ?', [$term])
                  ->orWhereHas('comunidad', fn($cq) => $cq->whereRaw('com_nombre ILIKE ?', [$term]));
            });
        }
        return $query->count();
    }

    // ─── Guardar vinculación ──────────────────────────────────

    public function guardarVinculacion(): void
    {
        if (empty($this->selectedProjects)) {
            $this->safeDispatch('error', 'Seleccione al menos un proyecto.');
            return;
        }

        $tituloId = null;
        if ($this->tituloSeleccionado === 'nuevo') {
            $nombre = strtoupper(trim($this->nuevoTitulo));
            $existing = TituloVinculacion::whereRaw('UPPER(tiv_titulo) = ?', [$nombre])
                ->where('tiv_estado_logico', true)
                ->first();
            if ($existing) {
                $tituloId = $existing->id;
            } else {
                $tv = TituloVinculacion::create(['tiv_titulo' => $nombre]);
                $tituloId = $tv->id;
                $this->cargarTitulos();
            }
        } else {
            $tituloId = (int) $this->tituloSeleccionado;
        }

        if (!$tituloId) {
            $this->safeDispatch('error', 'Error con el título seleccionado.');
            return;
        }

        $comCodigo = $this->comunidadId !== '' ? (int) $this->comunidadId : null;
        $tv = TituloVinculacion::find($tituloId);

        $creadas = 0;
        foreach ($this->selectedProjects as $pid) {
            Vinculacion::create([
                'proyecto_id' => $pid,
                'titulo_vinculacion_id' => $tituloId,
                'com_codigo' => $comCodigo,
                'tipo' => 'Vinculación',
            ]);
            $creadas++;
        }

        $this->safeDispatch('success', "{$creadas} vinculación(es) creada(s) — Título: " . ($tv?->titulo ?? ''));
        $this->resetParaSiguienteVinculacion();
    }

    public function resetParaSiguienteVinculacion(): void
    {
        $this->selectedProjects = [];
        $this->selectAll = false;
        $this->tituloSeleccionado = '';
        $this->nuevoTitulo = '';
        $this->comunidadId = '';
        $this->comunidadSeleccionada = null;
        $this->search = '';
        $this->pasoActual = 1;
    }

    protected function limpiar(): void
    {
        $this->selectedProjects = [];
        $this->selectAll = false;
        $this->tituloSeleccionado = '';
        $this->nuevoTitulo = '';
        $this->comunidadId = '';
        $this->comunidadSeleccionada = null;
        $this->search = '';
    }

    // ─── Quitar vinculación ───────────────────────────────────

    public function quitarVinculacion(int $vinCodigo): void
    {
        Vinculacion::where('vin_codigo', $vinCodigo)->delete();
        $this->safeDispatch('success', 'Vinculación eliminada.');
    }

    // ─── Detalle de proyecto ──────────────────────────────────

    public function verDetalle(int $proyectoId): void
    {
        $this->proyectoDetalle = Proyecto::with([
            'comunidad.direccion.municipio.estado',
            'linea_investigacion',
            'metodologia',
            'tipo_investigacion',
            'objetivo_investigacion',
            'documentos.componente',
            'vinculaciones.tituloVinculacion',
            'vinculaciones.comunidad',
        ])->find($proyectoId);

        if ($this->proyectoDetalle) {
            Proyecto::precargarTitulos(collect([$this->proyectoDetalle]));
            $this->mostrarModalDetalle = true;
        }
    }

    public function cerrarDetalle(): void
    {
        $this->mostrarModalDetalle = false;
        $this->proyectoDetalle = null;
    }

    // ─── Reporte PDF (modal) ───────────────────────────────────

    public function abrirModalReporte(): void
    {
        $this->mostrarModalReporte = true;
        $this->tipoReporte = 'todos';
        $this->reporteTituloId = '';
        $this->reporteLapsoId = '';

        // Mostrar solo títulos que tienen al menos una vinculación vinculada
        $tivConVinculaciones = Vinculacion::whereNotNull('titulo_vinculacion_id')
            ->pluck('titulo_vinculacion_id')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        if (!empty($tivConVinculaciones)) {
            $this->titulosReporte = TituloVinculacion::where('tiv_estado_logico', true)
                ->whereIn('tiv_codigo', $tivConVinculaciones)
                ->orderBy('tiv_titulo')
                ->pluck('tiv_titulo', 'tiv_codigo')
                ->toArray();
        } else {
            // Fallback: todos los títulos activos si no hay vinculaciones
            $this->titulosReporte = TituloVinculacion::where('tiv_estado_logico', true)
                ->orderBy('tiv_titulo')
                ->pluck('tiv_titulo', 'tiv_codigo')
                ->toArray();
        }

        $this->cargarLapsosReporte();
    }

    protected function cargarLapsosReporte(): void
    {
        $vinculaciones = Vinculacion::with('proyecto')->get();
        $equipoSeccion = app(IntranetEquipoSeccionService::class);
        $lapsos = [];
        foreach ($vinculaciones as $v) {
            if (!$v->proyecto || !$v->proyecto->equipo_ref) continue;
            $partes = $equipoSeccion->parsearClave($v->proyecto->equipo_ref);
            if (!$partes || empty($partes['lap_codigo'])) continue;
            $lapsos[$partes['lap_codigo']] = null;
        }
        if (!empty($lapsos)) {
            $models = LapsoAcademico::whereIn('lap_codigo', array_keys($lapsos))
                ->orderByDesc('lap_codigo')
                ->pluck('lap_nombre', 'lap_codigo');
            $this->lapsosReporte = $models->toArray();
        } else {
            $this->lapsosReporte = [];
        }
    }

    public function cerrarModalReporte(): void
    {
        $this->mostrarModalReporte = false;
    }

    public function generarReporte(): void
    {
        if ($this->tipoReporte === 'todos') {
            $params = [];  // sin filtros → todos los vinculados
        } elseif ($this->tipoReporte === 'titulo') {
            if (empty($this->reporteTituloId)) {
                $this->safeDispatch('error', 'Seleccione un título.');
                return;
            }
            $titulo = TituloVinculacion::find((int) $this->reporteTituloId);
            $params = ['filtro_titulo' => $titulo?->titulo ?? ''];
        } elseif ($this->tipoReporte === 'wizard') {
            if (empty($this->selectedProjects)) {
                $this->safeDispatch('error', 'No hay proyectos seleccionados en el wizard.');
                return;
            }
            $params = ['proyectos' => $this->selectedProjects];
        } else {
            if (empty($this->reporteLapsoId)) {
                $this->safeDispatch('error', 'Seleccione un lapso académico.');
                return;
            }
            $params = ['filtro_lapso' => $this->reporteLapsoId];
        }

        $url = route('vinculacion.reporte-pdf', $params);
        $this->dispatch('descargar-pdf', url: $url);
        $this->cerrarModalReporte();
    }

    // ─── Render ───────────────────────────────────────────────

    public function render()
    {
        try {
            $proyectosPaginados = null;

            if ($this->mostrarWizard) {
                $query = Proyecto::with('comunidad', 'vinculaciones.tituloVinculacion', 'vinculaciones.comunidad')
                    ->where('estado_validacion', 'aprobado')
                    ->where('estado_logico', true);

                if ($this->search !== '') {
                    $search = trim($this->search);
                    $term = '%' . $search . '%';
                    $query->where(function ($q) use ($search, $term) {
                        $q->whereRaw('pry_resumen ILIKE ?', [$term])
                          ->orWhereRaw('pry_direccion_logica ILIKE ?', [$term])
                          ->orWhereRaw('pry_motivo_rechazo ILIKE ?', [$term])
                          ->orWhereRaw('pry_creador_cedula ILIKE ?', [$term])
                          ->orWhereHas('comunidad', fn($cq) => $cq->whereRaw('com_nombre ILIKE ?', [$term]))
                          ->orWhereHas('linea_investigacion', fn($cq) => $cq->whereRaw('lin_nombre_investigacion ILIKE ?', [$term]))
                          ->orWhereHas('metodologia', fn($cq) => $cq->whereRaw('mei_nombre ILIKE ?', [$term]))
                          ->orWhereHas('tipo_investigacion', fn($cq) => $cq->whereRaw('tin_nombre ILIKE ?', [$term]))
                          ->orWhereHas('objetivo_investigacion', fn($cq) => $cq->whereRaw('obi_nombre::text ILIKE ?', [$term]));
                    });
                }

                $proyectosPaginados = $query->orderBy('id', 'desc')->paginate(8);
                Proyecto::precargarTitulos(collect($proyectosPaginados->items()));
            }

            $equipoSvc = app(IntranetEquipoSeccionService::class);
            $todasVinculaciones = Vinculacion::with('proyecto', 'comunidad', 'tituloVinculacion')->get();
            $proyectosVinculados = $todasVinculaciones->pluck('proyecto')->filter();
            Proyecto::precargarTitulos($proyectosVinculados);

            foreach ($todasVinculaciones as $v) {
                $p = $v->proyecto;
                $v->lapso_nombre = '';
                if ($p && $p->equipo_ref) {
                    $partes = $equipoSvc->parsearClave($p->equipo_ref);
                    if ($partes) {
                        $ctx = $equipoSvc->etiquetasContexto($partes['lap_codigo'], $partes['sec_codigo']);
                        if (!empty($ctx['lap_nombre'])) {
                            $v->lapso_nombre = $ctx['lap_nombre'];
                        }
                    }
                }
            }

            $vinculacionesPorProyecto = $todasVinculaciones
                ->groupBy(fn($v) => $v->proyecto_id);

            if (trim($this->busquedaListado) !== '') {
                $term = strtolower(trim($this->busquedaListado));
                $vinculacionesPorProyecto = $vinculacionesPorProyecto->filter(function ($grupo) use ($term) {
                    $proyecto = $grupo->first()->proyecto;
                    $tituloProyecto = strtolower($proyecto->titulo ?? '');
                    $equipoRef = strtolower($proyecto->equipo_ref ?? '');
                    $titulos = $grupo->pluck('titulo')->map(fn($t) => strtolower($t))->toArray();
                    $comunidades = $grupo->pluck('comunidad.nombre')->filter()->map(fn($c) => strtolower($c))->toArray();
                    $lapso = strtolower($grupo->first()->lapso_nombre ?? '');

                    return str_contains($tituloProyecto, $term)
                        || str_contains($equipoRef, $term)
                        || $lapso !== '' && str_contains($lapso, $term)
                        || collect($titulos)->contains(fn($t) => str_contains($t, $term))
                        || collect($comunidades)->contains(fn($c) => str_contains($c, $term));
                });
            }

            $comunidades = Comunidad::orderBy('com_nombre')->get();

            if ($this->comunidadId !== '' && !$this->comunidadSeleccionada) {
                $this->comunidadSeleccionada = Comunidad::find((int) $this->comunidadId);
            }

            return view('livewire.vinculacion-manager', [
                'proyectos' => $proyectosPaginados,
                'vinculacionesPorProyecto' => $vinculacionesPorProyecto,
                'comunidades' => $comunidades,
            ]);
        } catch (\Throwable $e) {
            return view('livewire.vinculacion-manager', [
                'proyectos' => null,
                'vinculacionesPorProyecto' => collect(),
                'comunidades' => collect(),
            ]);
        }
    }
}
