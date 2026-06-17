<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class Componente extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'componentes';

    protected $fillable = [
        'nombre',
        'programa_id',
        'es_obligatorio',
        'estado_logico',
        'tipo_archivo',
        'tamano_maximo_mb',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'estado_logico' => 'boolean',
        'tamano_maximo_mb' => 'integer',
    ];

    /**
     * Tipos de archivo disponibles para los componentes.
     */
    public static function tiposArchivo(): array
    {
        return [
            'pdf' => 'PDF (.pdf)',
            'zip' => 'ZIP (.zip)',
            'rar' => 'RAR (.rar)',
            'pdf,zip' => 'PDF o ZIP',
            'pdf,rar' => 'PDF o RAR',
            'pdf,zip,rar' => 'PDF, ZIP o RAR',
            'doc,docx' => 'Word (.doc, .docx)',
            'xls,xlsx' => 'Excel (.xls, .xlsx)',
            'img' => 'Imagen (.jpg, .png, .gif)',
        ];
    }

    /**
     * Obtiene el accept HTML para el input file.
     */
    public function getAcceptAttribute(): string
    {
        $map = [
            'pdf' => '.pdf',
            'zip' => '.zip',
            'rar' => '.rar',
            'doc' => '.doc,.docx',
            'docx' => '.doc,.docx',
            'xls' => '.xls,.xlsx',
            'xlsx' => '.xls,.xlsx',
            'img' => '.jpg,.jpeg,.png,.gif',
        ];

        $tipos = explode(',', $this->tipo_archivo ?? 'pdf');
        $exts = [];
        foreach ($tipos as $t) {
            $t = trim($t);
            if (isset($map[$t])) {
                $exts[] = $map[$t];
            }
        }

        return implode(',', array_unique($exts));
    }

    /**
     * Obtiene el tamano maximo en KB para validacion.
     */
    public function getMaxSizeKbAttribute(): int
    {
        $mb = $this->tamano_maximo_mb ?? 10;
        return $mb * 1024;
    }

    /**
     * Obtiene el mimetype para el accept del input.
     */
    public function getMimeTypesAttribute(): string
    {
        $map = [
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'img' => 'image/jpeg,image/png,image/gif',
        ];

        $tipos = explode(',', $this->tipo_archivo ?? 'pdf');
        $mimes = [];
        foreach ($tipos as $t) {
            $t = trim($t);
            if (isset($map[$t])) {
                $mimes[] = $map[$t];
            }
        }

        return implode(',', array_unique($mimes));
    }

    /**
     * Guarda múltiples componentes.
     */
    public static function guardarMuchos(array $rows, string $programa_id): void
    {
        foreach ($rows as $row) {
            $comp = self::create([
                'nombre' => $row['nombre'],
                'programa_id' => $programa_id,
                'es_obligatorio' => $row['es_obligatorio'],
                'estado_logico' => true,
            ]);
            // Sync pivot for new components
            $comp->programas()->create(['pro_codigo' => $programa_id]);
        }
    }

    public function getNombreProgramaAttribute(): string
    {
        if (isset($this->attributes['nombre_programa_cache'])) {
            return $this->attributes['nombre_programa_cache'];
        }

        $id = $this->programa_id;
        if (! $id) {
            return 'N/A';
        }

        return once(function () use ($id) {
            $conn = \App\Helpers\DualDatabase::academicConnection();
            $prog = \Illuminate\Support\Facades\DB::connection($conn)
                ->table('programa')
                ->where('pro_codigo', $id)
                ->first(['pro_nombre', 'pro_siglas']);

            return $prog ? ($prog->pro_siglas ?? $prog->pro_nombre) : "Programa #{$id}";
        });
    }

    public function programas()
    {
        return $this->hasMany(ComponentePrograma::class, 'comp_codigo', 'comp_codigo');
    }
}
