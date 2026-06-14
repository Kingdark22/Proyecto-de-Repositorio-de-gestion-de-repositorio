<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoDocumento extends RepositorioModel
{
    protected $table = 'proyecto_documentos';
    protected $primaryKey = 'pd_codigo';

    protected $fillable = [
        'pry_codigo',
        'comp_codigo',
        'pd_archivo_path',
        'pd_orden',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'pry_codigo', 'pry_codigo');
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class, 'comp_codigo', 'comp_codigo');
    }
}
