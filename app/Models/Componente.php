<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class Componente extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'componentes';

    protected $fillable = [
        'nombre',
        'es_obligatorio',
        'estado_logico',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'estado_logico' => 'boolean',
    ];
}
