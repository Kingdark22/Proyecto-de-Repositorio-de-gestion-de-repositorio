<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class MetodologiaInvestigacion extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'metodologia_investigacions';

    protected $primaryKey = 'mei_codigo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean'
    ];
}
