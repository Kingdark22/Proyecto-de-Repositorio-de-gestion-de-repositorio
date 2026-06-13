<?php

namespace App\Services;

use App\Models\GrupoProyectoModulo;
use App\Models\Proyecto;
use App\Models\User;

class NotificacionService
{
    public function listar(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $userRoleService = app(UserRoleService::class);
        $activeRole = $userRoleService->getActiveRole($user);
        $isAdmin = $activeRole === 'administrador';
        $isCoordinator = $activeRole === 'coordinador';
        $isTeacher = $activeRole === 'profesor proyecto';
        $isStudent = $activeRole === 'estudiante';

        $notificaciones = [];

        if ($isAdmin || $isCoordinator || $isTeacher) {
            $query = Proyecto::where('estado_validacion', 'pendiente');

            if ($isTeacher) {
                $clavesDocente = app(ProyectoGestionService::class)->clavesEquipoFiltroValidacion($user);
                if ($clavesDocente !== null) {
                    $query->whereIn('pry_direccion_logica', $clavesDocente);
                }
            }

            $proyectos = $query->get();

            foreach ($proyectos as $p) {
                if ($p->actualizado_por_estudiante) {
                    $notificaciones[] = [
                        'type' => 'info',
                        'title' => 'Proyecto actualizado',
                        'mensaje' => 'Proyecto actualizado por el líder: ' . $p->titulo,
                        'url' => route('proyectos.gestion', ['tab' => 'validar', 'details' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                } else {
                    $notificaciones[] = [
                        'type' => 'info',
                        'title' => 'Proyecto registrado',
                        'mensaje' => 'Nuevo proyecto registrado: ' . $p->titulo,
                        'url' => route('proyectos.gestion', ['tab' => 'validar', 'details' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }
        } elseif ($isStudent) {
            $cedula = trim($user->usu_cedula);
            $gruposSvc = app(GrupoProyectoService::class);

            // 1. Proyectos nuevos que necesitan subir documentos
            $proyectosNuevos = Proyecto::where('actualizado_por_estudiante', false)
                ->where('estado_validacion', '!=', 'aprobado')
                ->where('estado_validacion', '!=', 'rechazado')
                ->whereNotNull('pry_direccion_logica')
                ->get();

            // 2. Proyectos rechazados que necesitan correcciones
            $proyectosRechazados = Proyecto::where('estado_validacion', 'rechazado')
                ->whereNotNull('pry_direccion_logica')
                ->get();

            foreach ($proyectosNuevos as $p) {
                if ($this->esLiderDelProyecto($p, $cedula, $gruposSvc)) {
                    $notificaciones[] = [
                        'type' => 'warning',
                        'title' => 'Subir documentos',
                        'mensaje' => 'Debe subir los documentos del proyecto: ' . $p->titulo,
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }

            foreach ($proyectosRechazados as $p) {
                if ($this->esLiderDelProyecto($p, $cedula, $gruposSvc)) {
                    $notificaciones[] = [
                        'type' => 'warning',
                        'title' => 'Proyecto rechazado',
                        'mensaje' => 'Revisión requerida para "' . $p->titulo . '". Motivo: ' . ($p->motivo_rechazo ?: 'Revisar detalles.'),
                        'url' => route('proyectos.gestion', ['edit' => $p->id]),
                        'proyecto_id' => $p->id,
                    ];
                }
            }
        }

        return $notificaciones;
    }

    public function contarPendientes(?User $user): int
    {
        return count($this->listar($user));
    }

    protected function esLiderDelProyecto(Proyecto $p, string $cedula, GrupoProyectoService $gruposSvc): bool
    {
        $clave = $p->equipo_ref;
        if ($clave === '') {
            return false;
        }

        $partes = $gruposSvc->parsearClave($clave);
        if (!$partes || ($partes['tipo'] ?? '') !== GrupoProyectoService::PREFIJO) {
            return false;
        }

        $grupo = GrupoProyectoModulo::find($partes['grp_codigo'] ?? 0);
        if (!$grupo) {
            return false;
        }

        $miembros = $grupo->grp_miembros ?? [];
        foreach ($miembros as $m) {
            if (trim($m['cedula'] ?? '') === $cedula
                && (int) ($m['rol_id'] ?? 0) === IntranetEquipoSeccionService::ROL_LIDER
            ) {
                return true;
            }
        }

        return false;
    }
}
