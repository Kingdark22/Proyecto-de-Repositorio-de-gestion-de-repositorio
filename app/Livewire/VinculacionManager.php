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



    // Título seleccionado
    public string $tituloSeleccionado = '';
    public string $nuevoTitulo = '';
    public array $titulosDisponibles = [];

    // Comunidad seleccionada
    public string $comunidadId = '';
    public ?Comunidad $comunidadSeleccionada = null;

    // Proyectos seleccionados
    public array $selectedProjects = [];
    public bool $selectAll = false;

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
            if ($this->tituloSeleccionado === '') {
                $this->safeDispatch('error', 'Debe seleccionar o crear un título de vinculación.');
                return;
            }
            if ($this->tituloSeleccionado === 'nuevo' && trim($this->nuevoTitulo) === '') {
                $this->safeDispatch('error', 'Escriba un nombre para el nuevo título.');
                return;
            }
        }
        if ($this->pasoActual === 2) {
            if ($this->comunidadId === '') {
                $this->safeDispatch('error', 'Debe seleccionar o crear una comunidad.');
                return;
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

    // ─── Título ───────────────────────────────────────────────

    public function updatedTituloSeleccionado(): void
    {
        if ($this->tituloSeleccionado !== 'nuevo') {
            $this->nuevoTitulo = '';
        }
    }

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
        $rules = [
            'selectedProjects' => 'required|array|min:1',
            'tituloSeleccionado' => 'required|string',
        ];
        $messages = [
            'selectedProjects.required' => 'Seleccione al menos un proyecto.',
            'selectedProjects.min' => 'Seleccione al menos un proyecto.',
            'tituloSeleccionado.required' => 'Seleccione o cree un título de vinculación.',
        ];
        $attributes = [
            'selectedProjects' => 'proyectos seleccionados',
            'tituloSeleccionado' => 'título de vinculación',
        ];

        if ($this->tituloSeleccionado === 'nuevo') {
            $rules['nuevoTitulo'] = 'required|string|min:3|max:255';
            $messages['nuevoTitulo.required'] = 'Escriba el nombre del nuevo título.';
            $messages['nuevoTitulo.min'] = 'El título debe tener al menos 3 caracteres.';
            $messages['nuevoTitulo.max'] = 'El título no puede exceder 255 caracteres.';
            $attributes['nuevoTitulo'] = 'nuevo título';
        }

        $this->validate($rules, $messages, $attributes);

        $tituloId = null;
        $tituloTexto = '';

        if ($this->tituloSeleccionado === 'nuevo') {
            $tituloTexto = trim($this->nuevoTitulo);
            $existing = TituloVinculacion::where('tiv_titulo', $tituloTexto)->first();
            if ($existing) {
                $tituloId = $existing->id;
            } else {
                $tv = TituloVinculacion::create(['tiv_titulo' => $tituloTexto]);
                $tituloId = $tv->id;
            }
        } elseif ($this->tituloSeleccionado !== '') {
            $tituloId = (int) $this->tituloSeleccionado;
            $tv = TituloVinculacion::find($tituloId);
            $tituloTexto = $tv?->titulo ?? '';
        }

        if (!$tituloId) {
            $this->safeDispatch('error', 'Seleccione o cree un título de vinculación.');
            return;
        }

        $comCodigo = $this->comunidadId !== '' ? (int) $this->comunidadId : null;

        $creadas = 0;
        $actualizadas = 0;
        foreach ($this->selectedProjects as $pid) {
            $existe = Vinculacion::where('proyecto_id', $pid)->first();
            if ($existe) {
                $existe->update([
                    'titulo_vinculacion_id' => $tituloId,
                    'com_codigo' => $comCodigo,
                ]);
                $actualizadas++;
                continue;
            }
            Vinculacion::create([
                'proyecto_id' => $pid,
                'titulo_vinculacion_id' => $tituloId,
                'com_codigo' => $comCodigo,
                'tipo' => 'Vinculación',
            ]);
            $creadas++;
        }

        $msg = "{$creadas} vinculación(es) creada(s)";
        if ($actualizadas > 0) {
            $msg .= " y {$actualizadas} actualizada(s)";
        }
        $msg .= " para «{$tituloTexto}».";
        $this->safeDispatch('success', $msg);
        $this->cerrarWizard();
        $this->cargarTitulos();
    }

    protected function limpiar(): void
    {
        $this->selectedProjects = [];
        $this->selectAll = false;
        $this->comunidadId = '';
        $this->comunidadSeleccionada = null;
        $this->tituloSeleccionado = '';
        $this->nuevoTitulo = '';
    }

    // ─── Quitar vinculación ───────────────────────────────────

    public function quitarVinculacion(int $proyectoId): void
    {
        Vinculacion::where('proyecto_id', $proyectoId)->delete();
        $this->safeDispatch('success', 'Vinculación eliminada.');
    }

    // ─── Detalle de proyecto ──────────────────────────────────

    public function verDetalle(int $proyectoId): void
    {
        $this->proyectoDetalle = Proyecto::with([
            'comunidad.direccion.municipio.estado',
            'linea_investigacion',
            'metodologia',
            'tipo_publicacion',
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
        $this->tipoReporte = 'titulo';
        $this->reporteTituloId = '';
        $this->reporteLapsoId = '';
        $this->titulosReporte = TituloVinculacion::where('tiv_estado_logico', true)
            ->orderBy('tiv_titulo')
            ->pluck('tiv_titulo', 'tiv_codigo')
            ->toArray();

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
        if ($this->tipoReporte === 'titulo') {
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
                          ->orWhereHas('tipo_publicacion', fn($cq) => $cq->whereRaw('tpu_nombre ILIKE ?', [$term]))
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
                $v->sede = '';
                $v->lapso_nombre = '';
                if ($p && $p->equipo_ref) {
                    $partes = $equipoSvc->parsearClave($p->equipo_ref);
                    if ($partes) {
                        $ctx = $equipoSvc->etiquetasContexto($partes['lap_codigo'], $partes['sec_codigo']);
                        $v->sede = $ctx['sed_siglas'] ?? '';
                        if (!empty($ctx['lap_nombre'])) {
                            $v->lapso_nombre = $ctx['lap_nombre'];
                        }
                    }
                }
            }

            $vinculacionesAgrupadas = $todasVinculaciones
                ->groupBy(fn($v) => $v->titulo . '|' . ($v->comunidad?->nombre ?? 'sin-comunidad'));

            $comunidades = Comunidad::orderBy('com_nombre')->get();

            if ($this->comunidadId !== '' && !$this->comunidadSeleccionada) {
                $this->comunidadSeleccionada = Comunidad::find((int) $this->comunidadId);
            }

            return view('livewire.vinculacion-manager', [
                'proyectos' => $proyectosPaginados,
                'vinculacionesAgrupadas' => $vinculacionesAgrupadas,
                'comunidades' => $comunidades,
            ]);
        } catch (\Throwable $e) {
            return view('livewire.vinculacion-manager', [
                'proyectos' => null,
                'vinculacionesAgrupadas' => collect(),
                'comunidades' => collect(),
            ]);
        }
    }
}
