<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgContacto extends Model
{
    protected $primaryKey = 'oco_codigo';

    protected $fillable = ['org_codigo', 'dep_codigo', 'oco_nombre', 'oco_apellido', 'oco_correo', 'oco_telefono', 'oco_cargo'];

    protected $table = 'org_contactos';

    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class, 'org_codigo', 'org_codigo');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'dep_codigo', 'dep_codigo');
    }
}
