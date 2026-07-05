<?php

namespace App\Models;

use App\Helpers\DbHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelos cuya fuente principal es la BD intranet (con fallback simulación vía DbHelper).
 */
abstract class IntranetModel extends Model
{
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
        if ($this->connection) {
            return $this->connection;
        }
        return DbHelper::connection();
    }
}
