<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'administrador',
            'coordinador coordinacion',
            'profesor proyecto',
            'profesor',
            'estudiante',
        ];

        foreach ($roles as $role) {
            \App\Models\Rol::create(['nombre' => $role]);
        }
    }
}
