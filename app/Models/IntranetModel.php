<?php

namespace App\Models;

use App\Helpers\DbHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelos cuya fuente principal es la BD intranet (con fallback simulación vía DbHelper).
 */
abstract class IntranetModel extends Model
{
    public function getConnectionName(): ?string
    {
        if ($this->connection) {
            return $this->connection;
        }
        return DbHelper::connection();
    }
}
