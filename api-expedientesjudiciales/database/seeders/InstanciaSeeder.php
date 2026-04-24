<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstanciaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('instancias')->insert([
            ['nombre' => 'Primera Instancia', 'descripcion' => 'Primera instancia', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Segunda Instancia', 'descripcion' => 'Segunda instancia', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Casación', 'descripcion' => 'Casación', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ejecución', 'descripcion' => 'Ejecución', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}