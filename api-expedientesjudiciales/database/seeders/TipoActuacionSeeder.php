<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoActuacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_actuacion')->insert([
            ['nombre' => 'Registro inicial', 'descripcion' => 'Registro inicial del expediente', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Audiencia', 'descripcion' => 'Audiencia judicial o administrativa', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Presentación de escrito', 'descripcion' => 'Presentación de escrito', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Notificación', 'descripcion' => 'Recepción o emisión de notificación', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Informe', 'descripcion' => 'Informe interno', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}