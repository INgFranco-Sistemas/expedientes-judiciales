<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EtapaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('etapas')->insert([
            ['nombre' => 'Investigación preparatoria', 'descripcion' => 'Etapa penal', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Intermedia', 'descripcion' => 'Etapa intermedia', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Juzgamiento', 'descripcion' => 'Etapa de juzgamiento', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ejecución', 'descripcion' => 'Etapa de ejecución', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}