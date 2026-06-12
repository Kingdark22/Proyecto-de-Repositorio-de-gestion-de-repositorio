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
        $availableRoles = array_keys($user->availableRoles());
        $isAdmin = in_array('administrador', $availableRoles, true);
        $isCoordinator = in_array('coordinador', $availableRoles, true);
        $isTeacher = in_array('profesor proyecto', $availableRoles, true);
        $isStudent = in_array('estudiante', $availableRoles, true);

        $notificaciones = [];

        if ($isAdmin || $isCoordinator || $isTeacher) {
            $proyectos = Proyecto::where('actualizado_por_estudiante', true)->get();
            foreach ($proyectos as $p) {
                $notificaciones[] = [
                    'mensaje' => 'Proyecto actualizado por el líder: ' . $p->titulo,
                    'url' => route('proyectos.gestion'),
                    'proyecto_id' => $p->id,
                ];
            }
        } elseif ($isStudent) {
            $cedula = trim($user->usu_cedula);
            $proyectos = Proyecto::where('actualizado_por_estudiante', false)
                ->whereNotNull('pry_direccion_logica')
                ->get();

            $gruposSvc = app(GrupoProyectoService::class);

            foreach ($proyectos as $p) {
                $clave = $p->equipo_ref;
                if ($clave === '') {
                    continue;
                }

                $partes = $gruposSvc->parsearClave($clave);
                if (!$partes || ($partes['tipo'] ?? '') !== GrupoProyectoService::PREFIJO) {
                    continue;
                }

                $grupo = GrupoProyectoModulo::find($partes['grp_codigo'] ?? 0);
                if (!$grupo) {
                    continue;
                }

                $miembros = $grupo->grp_miembros ?? [];
                foreach ($miembros as $m) {
                    if (trim($m['cedula'] ?? '') === $cedula
                        && (int) ($m['rol_id'] ?? 0) === IntranetEquipoSeccionService::ROL_LIDER
                    ) {
                        $notificaciones[] = [
                            'mensaje' => 'Debe subir los documentos del proyecto: ' . $p->titulo,
                            'url' => route('proyectos.gestion'),
                            'proyecto_id' => $p->id,
                        ];
                        break;
                    }
                }
            }
        }

        return $notificaciones;
    }

    public function contarPendientes(?User $user): int
    {
        return count($this->listar($user));
    }
}
