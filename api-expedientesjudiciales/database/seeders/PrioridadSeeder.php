<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioridadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('prioridades')->insert([
            ['nombre' => 'Baja', 'nivel' => 1, 'color_ui' => 'gray', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Media', 'nivel' => 2, 'color_ui' => 'blue', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Alta', 'nivel' => 3, 'color_ui' => 'orange', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Crítica', 'nivel' => 4, 'color_ui' => 'red', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}