<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogLogic;

class TipoPublicacion extends RepositorioModel
{
    use HasCatalogLogic;

    protected $table = 'tipo_publicacions';

    protected $primaryKey = 'tpu_codigo';

    protected $fillable = [
        'nombre',
        'mencion_honorifica',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean',
        'mencion_honorifica' => 'boolean'
    ];
}
