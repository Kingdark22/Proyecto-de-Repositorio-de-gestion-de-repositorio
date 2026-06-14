<?php

namespace App\Models;

class ComponentePrograma extends RepositorioModel
{
    protected $table = 'componente_programa';
    protected $primaryKey = 'cpp_codigo';

    protected $fillable = [
        'comp_codigo',
        'pro_codigo',
    ];

    public function componente()
    {
        return $this->belongsTo(Componente::class, 'comp_codigo', 'comp_codigo');
    }
}
