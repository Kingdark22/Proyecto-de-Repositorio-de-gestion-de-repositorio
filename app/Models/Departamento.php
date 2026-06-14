<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Departamento extends RepositorioModel
{
    protected $table = 'departamento';

    protected $schemaKey = 'departamento';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'cargo',
        'uex_codigo',
    ];

    public function usuarioExterno(): BelongsTo
    {
        return $this->belongsTo(UsuarioExterno::class, 'dep_uex_codigo', 'uex_codigo');
    }
}
