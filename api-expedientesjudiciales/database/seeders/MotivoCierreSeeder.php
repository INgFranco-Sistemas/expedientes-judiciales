<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotivoCierreSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('motivos_cierre')->insert([
            ['nombre' => 'Sentencia firme', 'descripcion' => 'Cierre por sentencia firme', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Archivamiento definitivo', 'descripcion' => 'Cierre por archivamiento', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Desistimiento', 'descripcion' => 'Cierre por desistimiento', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Conciliación concluida', 'descripcion' => 'Cierre por conciliación', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}