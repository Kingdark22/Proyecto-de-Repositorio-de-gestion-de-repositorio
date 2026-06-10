<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas', 
            'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro', 'Distrito Capital', 
            'Falcón', 'Guárico', 'Lara', 'Mérida', 'Miranda', 
            'Monagas', 'Nueva Esparta', 'Portuguesa', 'Sucre', 'Táchira', 
            'Trujillo', 'La Guaira', 'Yaracuy', 'Zulia'
        ];

        foreach ($estados as $estado) {
            \App\Models\Estado::create(['est_nombre' => $estado]);
        }
    }
}
