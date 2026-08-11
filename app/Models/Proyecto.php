<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Proyecto extends RepositorioModel
{
    protected $table = 'proyectos';

    protected $fillable = [
        'resumen',
        'linea_investigacion_id',
        'metodologia_id',
        'tipo_investigacion_id',
        'objetivo_investigacion_id',
        'pry_estado',
        'actualizado_por_estudiante',
        'fecha_actualizacion_estudiante',
        'creador_cedula',
        'comunidad_id',
        'pry_cantidad_beneficiados',
        'equipo_ref',
    ];

    protected $uppercaseExceptions = ['pry_estado', 'correo', 'email', 'password', 'contrasena'];

    protected static array $resumenEquipoCache = [];

    public function getTituloAttribute(): string
    {
        if (!$this->equipo_ref) {
            return '(sin título)';
        }
        $grupo = \App\Models\GrupoProyectoModulo::porIdentificador($this->equipo_ref ?? '');
        if ($grupo) {
            return $grupo->grp_nombre;
        }
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

    public function getEstadoValidacionAttribute(): string
    {
        return strtolower((string) $this->pry_estado);
    }

    public function getMotivoRechazoAttribute(): string
    {
        return '';
    }

    protected $casts = [
        'actualizado_por_estudiante' => 'boolean',
        'fecha_actualizacion_estudiante' => 'datetime',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('pry_estado', 'Aprobado');
    }

    public function scopeAprobados(Builder $query): Builder
    {
        return $query->where('pry_estado', 'Aprobado');
    }

    public function scopeVisiblesPublico(Builder $query): Builder
    {
        return $query->where('pry_estado', 'Aprobado');
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
                $q->where('coord_codigo', $programaId);
            });
        }

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

    public function scopePendientes(Builder $query, ?string $search = null): Builder
    {
        $query->where('pry_estado', 'Pendiente');

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pry_direccion_logica', 'ILIKE', "%{$search}%");
                try {
                    $q->orWhereRaw('pry_resumen ILIKE ?', ["%{$search}%"]);
                } catch (\Throwable) {
                }
            });
        }

        return $query;
    }

    public function aprobar(): void
    {
        $this->update([
            'pry_estado' => 'Aprobado',
        ]);
    }

    public function rechazar(string $motivo): void
    {
        $this->update([
            'pry_estado' => 'Rechazado',
        ]);
    }

    public function scopeRechazados(Builder $query): Builder
    {
        return $query->where('pry_estado', 'Rechazado')
            ->where('actualizado_por_estudiante', false);
    }

    public function scopePendientesEstudiante(Builder $query): Builder
    {
        return $query->where('actualizado_por_estudiante', false)
            ->where('pry_estado', 'Pendiente');
    }
}
