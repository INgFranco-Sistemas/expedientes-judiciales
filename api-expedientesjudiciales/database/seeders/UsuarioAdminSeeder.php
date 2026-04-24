<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $perfilAdmin = DB::table('perfiles')
            ->where('nombre', 'Administrador Procuraduria')
            ->first();

        if (!$perfilAdmin) {
            return;
        }

        DB::table('usuarios')->insert([
            'username' => 'admin.procuraduria',
            'password' => Hash::make('Admin123*'),
            'nombres' => 'Administrador',
            'apellidos' => 'General',
            'nombre_completo' => 'Administrador General',
            'dni' => '00000001',
            'correo_institucional' => 'admin@procuraduria.gob.pe',
            'telefono' => null,
            'especialidad_id' => null,
            'perfil_id' => $perfilAdmin->id,
            'estado_usuario' => 'ACTIVO',
            'fecha_inicio_asignacion' => now()->toDateString(),
            'fecha_termino_asignacion' => null,
            'ultimo_acceso_at' => null,
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}