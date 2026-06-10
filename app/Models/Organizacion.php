<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizacion extends RepositorioModel
{
    protected $table = 'organizacion';

    protected $schemaKey = 'organizacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'rif',
        'correo',
        'direccion',
        'dep_codigo',
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'org_dep_codigo', 'dep_codigo');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(OrgContacto::class, 'org_codigo', 'org_codigo');
    }
}
