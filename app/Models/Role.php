<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'tipo_de_rol',
    ];

    public function comunidades()
    {
        return $this->belongsToMany(Comunidad::class, 'comunidad_estudiante', 'role_id', 'comunidad_id')
                    ->withPivot('persona_id')
                    ->withTimestamps();
    }
}
