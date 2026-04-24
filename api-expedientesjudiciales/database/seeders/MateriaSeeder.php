<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('materias')->insert([
            ['nombre' => 'Indemnización', 'descripcion' => 'Materia indemnización', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Nulidad de acto administrativo', 'descripcion' => 'Materia administrativa', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Obligación de dar suma de dinero', 'descripcion' => 'Materia económica', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Responsabilidad penal', 'descripcion' => 'Materia penal', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Conciliación extrajudicial', 'descripcion' => 'Materia conciliación', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}