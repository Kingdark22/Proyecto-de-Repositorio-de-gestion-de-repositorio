<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Proyecto extends RepositorioModel
{
    protected $table = 'proyectos';

    protected $fillable = [
        // titulo es accessor - no se persiste en BD
        'resumen',
        'linea_investigacion_id',
        'metodologia_id',
        'tipo_publicacion_id',
        'tipo_investigacion_id',
        'objetivo_investigacion_id',
        'estado_logico',
        'estado_validacion',
        'motivo_rechazo',
        'actualizado_por_estudiante',
        'fecha_actualizacion_estudiante',
        'creador_cedula',
        'comunidad_id',
        'equipo_ref',
    ];

    protected static array $resumenEquipoCache = [];

    public function getTituloAttribute(): string
    {
        if (!$this->equipo_ref) {
            return '(sin título)';
        }
        // Try as identificador first
        $grupo = \App\Models\GrupoProyectoModulo::where('grp_identificador', $this->equipo_ref)->first();
        if ($grupo) {
            return $grupo->grp_nombre;
        }
        // Fallback: parse as EQGRP:{id}
        $service = app(\App\Services\GrupoProyectoService::class);
        $partes = $service->parsearClave($this->equipo_ref);
        if ($partes && ($partes['tipo'] ?? '') === \App\Services\GrupoProyectoService::PREFIJO && !empty($partes['grp_codigo'])) {
            $codigo = $partes['grp_codigo'];
            if ($this->relationLoaded('grupoProyecto') && $this->grupoProyecto) {
                return $this->grupoProyecto->grp_nombre;
            }
            $g = \App\Models\GrupoProyectoModulo::find($codigo);
            return $g ? $g->grp_nombre : $this->equipo_ref;
        }
        return $this->equipo_ref;
    }

    public function getEquipoResumenAttribute(): string
    {
        $key = $this->equipo_ref ?? '__null__';
        if (isset(self::$resumenEquipoCache[$key])) {
            return self::$resumenEquipoCache[$key];
        }
        $resumen = app(\App\Services\IntranetEquipoSeccionService::class)
            ->resumenEquipo($this->equipo_ref);
        self::$resumenEquipoCache[$key] = $resumen;
        return $resumen;
    }

    protected $casts = [
        'estado_logico' => 'boolean',
        'actualizado_por_estudiante' => 'boolean',
        'fecha_actualizacion_estudiante' => 'datetime',
    ];
    

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_logico', true);
    }

    public function scopeAprobados(Builder $query): Builder
    {
        return $query->where('estado_validacion', 'aprobado');
    }

    public function scopeVisiblesPublico(Builder $query): Builder
    {
        return $query->activos()->aprobados();
    }

    public function scopeBusquedaPublica(Builder $query, ?string $search = null, ?int $programaId = null, ?string $lapso = null): Builder
    {
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('equipo_ref', 'ILIKE', "%{$search}%");
                try {
                    $q->orWhereRaw('to_tsvector(\'spanish\', coalesce(pry_resumen, \'\')) @@ plainto_tsquery(\'spanish\', ?)', [$search]);
                } catch (\Throwable) {
                    $q->orWhereRaw('pry_resumen ILIKE ?', ["%{$search}%"]);
                }
            });
        }

        if ($programaId) {
            $query->whereHas('linea_investigacion', function ($q) use ($programaId) {
                $q->where('programa_id', $programaId);
            });
        }

        // El filtro de lapso depende de cómo se guarde en equipo_ref o similar
        // Por ahora lo dejamos así si no hay una columna directa de lapso en proyectos
        
        return $query;
    }

    public function linea_investigacion()
    {
        return $this->belongsTo(LineaInvestigacion::class, 'lin_codigo', 'lin_codigo');
    }

    public function metodologia()
    {
        return $this->belongsTo(MetodologiaInvestigacion::class, 'mei_codigo', 'mei_codigo');
    }

    public function tipo_publicacion()
    {
        return $this->belongsTo(TipoPublicacion::class, 'tpu_codigo', 'tpu_codigo');
    }

    public function tipo_investigacion()
    {
        return $this->belongsTo(TipoInvestigacion::class, 'tin_codigo', 'tin_codigo');
    }

    public function objetivo_investigacion()
    {
        return $this->belongsTo(ObjetivoInvestigacion::class, 'objetivo_investigacion_id', 'obi_codigo');
    }

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'com_codigo', 'com_codigo');
    }

    public function documentos()
    {
        return $this->hasMany(ProyectoDocumento::class, 'pry_codigo', 'pry_codigo')->orderBy('pd_orden');
    }

    public function vinculaciones()
    {
        return $this->hasMany(Vinculacion::class, 'proyecto_id', 'pry_codigo');
    }

    public static function precargarTitulos($proyectos): void
    {
        $identificadores = [];
        $codigos = [];
        $service = app(\App\Services\GrupoProyectoService::class);

        foreach ($proyectos as $p) {
            $ref = $p->equipo_ref;
            if (!$ref) continue;

            // Try as identificador
            if (!str_starts_with($ref, 'EQGRP:') && !str_starts_with($ref, 'EQSEC:')) {
                $identificadores[] = $ref;
            } else {
                $partes = $service->parsearClave($ref);
                if ($partes && ($partes['tipo'] ?? '') === \App\Services\GrupoProyectoService::PREFIJO && !empty($partes['grp_codigo'])) {
                    $codigos[$partes['grp_codigo']] = true;
                }
            }
        }

        $gruposPorId = [];
        if ($codigos) {
            $gruposPorId = \App\Models\GrupoProyectoModulo::whereIn('grp_codigo', array_keys($codigos))->get()->keyBy('grp_codigo');
        }

        $gruposPorIdent = [];
        if ($identificadores) {
            $gruposPorIdent = \App\Models\GrupoProyectoModulo::whereIn('grp_identificador', $identificadores)->get()->keyBy('grp_identificador');
        }

        foreach ($proyectos as $p) {
            $ref = $p->equipo_ref;
            if (!$ref) continue;

            if (!str_starts_with($ref, 'EQGRP:') && !str_starts_with($ref, 'EQSEC:')) {
                if (isset($gruposPorIdent[$ref])) {
                    $p->setRelation('grupoProyecto', $gruposPorIdent[$ref]);
                }
            } else {
                $partes = $service->parsearClave($ref);
                if ($partes && !empty($partes['grp_codigo'])) {
                    $codigo = $partes['grp_codigo'];
                    if (isset($gruposPorId[$codigo])) {
                        $p->setRelation('grupoProyecto', $gruposPorId[$codigo]);
                    }
                }
            }
        }
    }

    // ─── Scopes y métodos para ValidacionesManager ────────────────

    /**
     * Proyectos pendientes o completados (listos para revisión).
     */
    public function scopePendientes(Builder $query, ?string $search = null): Builder
    {
        $query->whereIn('estado_validacion', ['pendiente', 'completado']);

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pry_direccion_logica', 'ILIKE', "%{$search}%");
                try {
                    $q->orWhereRaw('pry_resumen ILIKE ?', ["%{$search}%"]);
                } catch (\Throwable) {
                    // fallback
                }
            });
        }

        return $query;
    }

    /**
     * Aprueba el proyecto directamente.
     */
    public function aprobar(): void
    {
        $this->update([
            'estado_validacion' => 'aprobado',
            'estado_logico' => true,
        ]);
    }

    /**
     * Rechaza el proyecto con un motivo.
     */
    public function rechazar(string $motivo): void
    {
        $this->update([
            'estado_validacion' => 'rechazado',
            'motivo_rechazo' => $motivo,
            'estado_logico' => false,
        ]);
    }

    /**
     * Proyectos rechazados (para notificaciones).
     */
    public function scopeRechazados(Builder $query): Builder
    {
        return $query->where('estado_validacion', 'rechazado')
            ->where('actualizado_por_estudiante', false);
    }

    /**
     * Proyectos pendientes de documentos por parte del estudiante.
     */
    public function scopePendientesEstudiante(Builder $query): Builder
    {
        return $query->where('actualizado_por_estudiante', false)
            ->whereIn('estado_validacion', ['pendiente', 'aprobado']);
    }
}
