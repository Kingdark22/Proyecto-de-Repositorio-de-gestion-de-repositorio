<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Estado;
use App\Models\Municipio;
use App\Repositories\ComunidadRepository;
use App\Services\ComunidadGestionService;
use App\Services\UnicidadNombreService;
use App\Services\ValidacionCorreoService;
use App\Services\ValidacionRifService;
use Illuminate\Http\Request;

class ComunidadController extends Controller
{
    public function __construct(
        protected ComunidadGestionService $gestion,
        protected ComunidadRepository $comunidadRepo,
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $comunidades = $this->comunidadRepo->paginate(
            ['search' => trim($search)],
            $request->get('page', 1)
        );

        $user = auth()->user();
        $puedeGestionar = $user && (
            $user->hasRole('administrador', 'coordinador', 'gestionador') ||
            ($user->hasRole('profesor proyecto') && app(\App\Services\IntranetProfessorService::class)
                ->esProfesorProyectoVigente(trim((string) $user->usu_cedula)))
        );

        return view('comunidades.index', compact('comunidades', 'search', 'puedeGestionar'));
    }

    public function create()
    {
        $estados = Estado::orderBy('est_nombre')->get();
        $municipios = collect();

        return view('comunidades.create', compact('estados', 'municipios'));
    }

    public function store(Request $request, UnicidadNombreService $unicidadService, ValidacionRifService $rifService, ValidacionCorreoService $correoService)
    {
        $validated = $request->validate($this->gestion->reglasValidacion());

        $nombre = trim($validated['nombre']);

        $disponible = $unicidadService->check(
            Comunidad::class,
            'nombre',
            $nombre,
            null,
        );

        if (!$disponible) {
            return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
        }

        $rifNumero = preg_replace('/\D/', '', $request->input('rif_numero', ''));
        $rifLetra = $request->input('rif_letra', 'J');
        $rifCompleto = null;

        if ($rifNumero !== '') {
            if (strlen($rifNumero) < 9) {
                return back()->withErrors(['rif_numero' => 'El RIF debe tener exactamente 9 dígitos.'])->withInput();
            }
            $digito = $rifService->calcularDigito($rifLetra, $rifNumero);
            if ($digito === null) {
                return back()->withErrors(['rif_numero' => 'El RIF ingresado no es válido.'])->withInput();
            }
            $rifCompleto = "{$rifLetra}-{$rifNumero}-{$digito}";
        }

        $correo = trim($validated['correo'] ?? '');
        if ($correo !== '') {
            $resultado = $correoService->validarCompleto($correo);
            if (!$resultado['valido']) {
                return back()->withErrors(['correo' => $resultado['error'] ?? 'Correo inválido.'])->withInput();
            }
            $resultado2 = $correoService->validarCompleto($correo, true);
            if (!$resultado2['valido']) {
                return back()->withErrors(['correo' => $resultado2['error'] ?? 'El dominio del correo no existe.'])->withInput();
            }
        }

        $payload = [
            'nombre' => $nombre,
            'correo' => $correo,
            'prefijo_telefono' => $request->input('prefijo_telefono', '0424'),
            'numero_telefono' => $request->input('numero_telefono', ''),
            'estado_id' => $validated['estado_id'],
            'municipio_id' => $validated['municipio_id'],
            'dir_nombre' => $validated['dir_nombre'],
        ];

        if ($rifCompleto) {
            $payload['rif'] = $rifCompleto;
        }

        $this->gestion->guardar(null, $payload);

        return redirect()->route('comunidades.index')
            ->with('success', 'Comunidad registrada con éxito.');
    }

    public function edit($id)
    {
        $datos = $this->gestion->cargarParaEdicion($id);
        $estados = Estado::orderBy('est_nombre')->get();
        $municipios = $datos['estado_id'] !== ''
            ? Municipio::where('est_codigo', $datos['estado_id'])->orderBy('mun_nombre')->get()
            : collect();

        $rifService = app(ValidacionRifService::class);
        $parsed = $rifService->parsear($datos['rif'] ?? '');
        $telefonoCompleto = $datos['numero_telefono'] ?? '';
        $prefijo = '0424';
        $numeroTel = $telefonoCompleto;
        $prefijos = ['0424', '0414', '0412', '0422', '0416', '0426'];
        foreach ($prefijos as $p) {
            if (str_starts_with($telefonoCompleto, $p)) {
                $prefijo = $p;
                $numeroTel = substr($telefonoCompleto, strlen($p));
                break;
            }
        }

        return view('comunidades.edit', compact(
            'datos', 'estados', 'municipios', 'parsed', 'prefijo', 'numeroTel', 'id'
        ));
    }

    public function update(Request $request, $id, UnicidadNombreService $unicidadService, ValidacionRifService $rifService, ValidacionCorreoService $correoService)
    {
        $validated = $request->validate($this->gestion->reglasValidacion());

        $nombre = trim($validated['nombre']);
        $comunidad = Comunidad::findOrFail($id);

        if ($comunidad->nombre !== $nombre) {
            $disponible = $unicidadService->check(
                Comunidad::class,
                'nombre',
                $nombre,
                $id,
            );
            if (!$disponible) {
                return back()->withErrors(['nombre' => 'Este nombre ya está en uso.'])->withInput();
            }
        }

        $rifNumero = preg_replace('/\D/', '', $request->input('rif_numero', ''));
        $rifLetra = $request->input('rif_letra', 'J');
        $rifCompleto = null;

        if ($rifNumero !== '') {
            if (strlen($rifNumero) < 9) {
                return back()->withErrors(['rif_numero' => 'El RIF debe tener exactamente 9 dígitos.'])->withInput();
            }
            $digito = $rifService->calcularDigito($rifLetra, $rifNumero);
            if ($digito === null) {
                return back()->withErrors(['rif_numero' => 'El RIF ingresado no es válido.'])->withInput();
            }
            $rifCompleto = "{$rifLetra}-{$rifNumero}-{$digito}";
        }

        $correo = trim($validated['correo'] ?? '');
        if ($correo !== '') {
            $resultado = $correoService->validarCompleto($correo);
            if (!$resultado['valido']) {
                return back()->withErrors(['correo' => $resultado['error'] ?? 'Correo inválido.'])->withInput();
            }
            $resultado2 = $correoService->validarCompleto($correo, true);
            if (!$resultado2['valido']) {
                return back()->withErrors(['correo' => $resultado2['error'] ?? 'El dominio del correo no existe.'])->withInput();
            }
        }

        $payload = [
            'nombre' => $nombre,
            'correo' => $correo,
            'prefijo_telefono' => $request->input('prefijo_telefono', '0424'),
            'numero_telefono' => $request->input('numero_telefono', ''),
            'estado_id' => $validated['estado_id'],
            'municipio_id' => $validated['municipio_id'],
            'dir_nombre' => $validated['dir_nombre'],
        ];

        if ($rifCompleto) {
            $payload['rif'] = $rifCompleto;
        }

        $this->gestion->guardar($id, $payload);

        return redirect()->route('comunidades.index')
            ->with('success', 'Comunidad actualizada con éxito.');
    }

    public function destroy($id)
    {
        $this->gestion->eliminar($id);
        return redirect()->route('comunidades.index')
            ->with('success', 'Comunidad eliminada correctamente.');
    }

    public function municipios($estadoId)
    {
        $municipios = Municipio::where('est_codigo', $estadoId)
            ->orderBy('mun_nombre')
            ->get(['mun_codigo', 'mun_nombre']);

        return response()->json($municipios);
    }
}
