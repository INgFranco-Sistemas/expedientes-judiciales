<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoExpedienteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados_expediente')->insert([
            ['nombre' => 'Registrado', 'descripcion' => 'Expediente registrado', 'color_ui' => 'blue', 'orden_visual' => 1, 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En trámite', 'descripcion' => 'Expediente en trámite', 'color_ui' => 'orange', 'orden_visual' => 2, 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En seguimiento', 'descripcion' => 'Expediente en seguimiento', 'color_ui' => 'yellow', 'orden_visual' => 3, 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cerrado', 'descripcion' => 'Expediente cerrado', 'color_ui' => 'green', 'orden_visual' => 4, 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Archivado', 'descripcion' => 'Expediente archivado', 'color_ui' => 'gray', 'orden_visual' => 5, 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}