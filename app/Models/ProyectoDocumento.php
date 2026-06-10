<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProyectoDocumento extends RepositorioModel
{
    use HasFactory;

    protected $table = 'proyecto_documentos';

    protected $fillable = [
        'proyecto_id',
        'componente_id',
        'archivo_path'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function componente()
    {
        return $this->belongsTo(Componente::class);
    }
}
