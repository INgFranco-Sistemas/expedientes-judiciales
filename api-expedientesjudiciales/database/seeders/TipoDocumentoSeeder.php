<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_documento')->insert([
            ['nombre' => 'Demanda', 'descripcion' => 'Documento de demanda', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Contestación', 'descripcion' => 'Contestación de demanda', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Resolución', 'descripcion' => 'Resolución', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Informe', 'descripcion' => 'Informe interno', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Anexo', 'descripcion' => 'Documento anexo', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}