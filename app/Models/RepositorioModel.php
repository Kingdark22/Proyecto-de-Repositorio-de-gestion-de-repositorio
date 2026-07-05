<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelos almacenados en la BD repositorio (proyectos, comunidades, catálogos locales).
 */
abstract class RepositorioModel extends Model
{
    use Concerns\MapsLegacyColumns;

    protected $uppercaseExceptions = ['correo', 'email', 'password', 'contrasena'];

    public function setAttribute($key, $value)
    {
        if (is_string($value) && !in_array($key, $this->uppercaseExceptions)) {
            $value = strtoupper($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getConnectionName(): ?string
    {
        return (string) config('dual_database.repositorio_connection', 'pgsql');
    }
}
