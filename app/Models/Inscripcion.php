<?php

namespace App\Models;

use App\Helpers\DbHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de solo lectura para inscripciones desde intranet/simulación.
 * Las consultas académicas (equipos del estudiante, programas, etc.)
 * se realizan via IntranetEquipoSeccionService, no desde este modelo.
 */
class Inscripcion extends Model
{
    protected $table = 'inscripcion';
    protected $connection = 'intranet';

    public $timestamps = false;

    protected $fillable = [
        'ins_codigo',
        'ins_cedula',
        'ins_cod_seccion_unidad_docente',
        'ins_estatus',
    ];

    public function getConnectionName()
    {
        return $this->connection ?: DbHelper::connection();
    }
}
