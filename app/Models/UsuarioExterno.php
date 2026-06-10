<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioExterno extends RepositorioModel
{
    protected $table = 'usuarios_externos';

    protected $schemaKey = 'usuarios_externos';

    protected $primaryKey = 'uex_codigo';

    public $timestamps = false;

    protected $fillable = [
        'uex_nombre',
        'uex_contrasena',
        'uex_rex_codigo',
        'uex_estado',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolExterno::class, 'uex_rex_codigo', 'rex_codigo');
    }
}
