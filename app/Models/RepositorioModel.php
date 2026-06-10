<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelos almacenados en la BD MySQL repositorio (proyectos, comunidades, catálogos locales).
 */
abstract class RepositorioModel extends Model
{
    use Concerns\MapsLegacyColumns;

    public function getConnectionName(): ?string
    {
        return (string) config('dual_database.repositorio_connection', 'mysql');
    }
}
