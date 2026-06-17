<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class ObjetivoInvestigacion extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'objetivo_investigacions';

    protected $primaryKey = 'obi_codigo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean'
    ];
}
