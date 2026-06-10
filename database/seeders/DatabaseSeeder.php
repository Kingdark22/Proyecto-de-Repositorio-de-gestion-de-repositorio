<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(EstadoMunicipioSeeder::class);
        $this->call(SimulacionAcademicaSeeder::class);
        
        // Agregar roles a la tabla 'roles'
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            ['id' => 1, 'tipo_de_rol' => 'lider', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'tipo_de_rol' => 'autor', 'created_at' => now(), 'updated_at' => now()],
        ]);


        $admin = \App\Models\User::factory()->create([
            'nombre' => 'Administrador',
            'apellido' => 'Sistema',
            'sexo' => 'M',
            'fecha_nacimiento' => '1990-01-01',
            'email' => 'admin@upt.edu.ve',
            'password' => bcrypt('admin123'),
        ]);
        $admin->roles()->attach(1); // administrador

        $coord = \App\Models\User::factory()->create([
            'nombre' => 'Coordinador',
            'apellido' => 'Academico',
            'sexo' => 'F',
            'fecha_nacimiento' => '1985-05-15',
            'email' => 'coordinador@upt.edu.ve',
            'password' => bcrypt('coord123'),
        ]);
        $coord->roles()->attach(2); // coordinador coordinacion

        $prof = \App\Models\User::factory()->create([
            'nombre' => 'Profesor',
            'apellido' => 'Juan',
            'sexo' => 'M',
            'fecha_nacimiento' => '1980-10-20',
            'email' => 'profesor@upt.edu.ve',
            'password' => bcrypt('prof123'),
        ]);
        $prof->roles()->attach(4); // profesor

        $estu = \App\Models\User::factory()->create([
            'nombre' => 'Estudiante',
            'apellido' => 'Maria',
            'sexo' => 'F',
            'fecha_nacimiento' => '2000-03-10',
            'email' => 'estudiante@upt.edu.ve',
            'password' => bcrypt('estu123'),
        ]);
        $estu->roles()->attach(5); // estudiante

        \App\Models\Comunidad::create(['nombre' => 'Comunidad Las Brisas']);
        \App\Models\Comunidad::create(['nombre' => 'Comunidad El Centro']);
    }
}
