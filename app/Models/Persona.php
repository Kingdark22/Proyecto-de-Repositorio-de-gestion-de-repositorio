<?php

namespace App\Models;

class Persona extends IntranetModel
{
    protected $table = 'persona';
    protected $primaryKey = 'per_cedula';
    public $incrementing = false; // Primary key is not auto-incrementing
    protected $keyType = 'string'; // Primary key is a string (cedula)
}
