<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('especialidades')->insert([
            ['nombre' => 'Civil', 'descripcion' => 'Especialidad civil', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Penal', 'descripcion' => 'Especialidad penal', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Laboral', 'descripcion' => 'Especialidad laboral', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Contencioso Administrativo', 'descripcion' => 'Especialidad contencioso administrativo', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Constitucional', 'descripcion' => 'Especialidad constitucional', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}