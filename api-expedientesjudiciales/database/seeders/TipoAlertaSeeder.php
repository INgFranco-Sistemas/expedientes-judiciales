<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoAlertaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_alerta')->insert([
            ['nombre' => 'Vencimiento próximo', 'descripcion' => 'Alerta de vencimiento próximo', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Expediente sin movimiento', 'descripcion' => 'Sin movimiento en plazo definido', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Audiencia programada', 'descripcion' => 'Audiencia registrada', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Documento pendiente', 'descripcion' => 'Falta documento relevante', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}