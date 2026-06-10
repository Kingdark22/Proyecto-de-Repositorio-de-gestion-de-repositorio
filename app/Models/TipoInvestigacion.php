<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class TipoInvestigacion extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'tipo_investigacions';

    protected $primaryKey = 'tin_codigo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean'
    ];
}
