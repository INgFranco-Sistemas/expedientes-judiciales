<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistritoJudicialSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('distritos_judiciales')->insert([
            ['nombre' => 'Huánuco', 'codigo' => 'HUANUCO', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lima', 'codigo' => 'LIMA', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pasco', 'codigo' => 'PASCO', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}