<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('perfiles')->insert([
            [
                'nombre' => 'Administrador Procuraduria',
                'descripcion' => 'Acceso total al sistema',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Gestor Procuraduria',
                'descripcion' => 'Registro y seguimiento de expedientes',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Consulta Procuraduria',
                'descripcion' => 'Acceso de solo lectura',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}