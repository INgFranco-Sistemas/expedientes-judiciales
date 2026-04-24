<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoExpedienteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_expediente')->insert([
            ['codigo' => 'JUDICIAL', 'nombre' => 'Judicial', 'descripcion' => 'Proceso judicial', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'PENAL', 'nombre' => 'Penal', 'descripcion' => 'Proceso penal', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'ARBITRAJE', 'nombre' => 'Arbitraje', 'descripcion' => 'Proceso arbitral', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'CONCILIACION', 'nombre' => 'Conciliación', 'descripcion' => 'Proceso de conciliación', 'estado' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}