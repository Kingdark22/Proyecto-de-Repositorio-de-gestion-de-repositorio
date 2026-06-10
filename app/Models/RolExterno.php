<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class RolExterno extends RepositorioModel
{
    protected $table = 'rol_externo';

    protected $schemaKey = 'rol_externo';

    protected $primaryKey = 'rex_codigo';

    public $timestamps = false;

    protected $fillable = [
        'rex_nombre',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(UsuarioExterno::class, 'uex_rex_codigo', 'rex_codigo');
    }
}
