<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            ['codigo' => 'dashboard.ver', 'nombre' => 'Ver dashboard', 'modulo' => 'Dashboard'],

            ['codigo' => 'catalogos.ver', 'nombre' => 'Ver catálogos', 'modulo' => 'Catálogos'],

            ['codigo' => 'usuarios.ver', 'nombre' => 'Ver usuarios', 'modulo' => 'Usuarios'],
            ['codigo' => 'usuarios.crear', 'nombre' => 'Crear usuarios', 'modulo' => 'Usuarios'],
            ['codigo' => 'usuarios.editar', 'nombre' => 'Editar usuarios', 'modulo' => 'Usuarios'],
            ['codigo' => 'usuarios.eliminar', 'nombre' => 'Eliminar usuarios', 'modulo' => 'Usuarios'],

            ['codigo' => 'expedientes.ver', 'nombre' => 'Ver expedientes', 'modulo' => 'Expedientes'],
            ['codigo' => 'expedientes.crear', 'nombre' => 'Crear expedientes', 'modulo' => 'Expedientes'],
            ['codigo' => 'expedientes.editar', 'nombre' => 'Editar expedientes', 'modulo' => 'Expedientes'],
            ['codigo' => 'expedientes.eliminar', 'nombre' => 'Eliminar expedientes', 'modulo' => 'Expedientes'],
            ['codigo' => 'expedientes.cerrar', 'nombre' => 'Cerrar expedientes', 'modulo' => 'Expedientes'],
            ['codigo' => 'expedientes.reabrir', 'nombre' => 'Reabrir expedientes', 'modulo' => 'Expedientes'],

            ['codigo' => 'partes.ver', 'nombre' => 'Ver partes', 'modulo' => 'Partes'],
            ['codigo' => 'partes.crear', 'nombre' => 'Crear partes', 'modulo' => 'Partes'],
            ['codigo' => 'partes.editar', 'nombre' => 'Editar partes', 'modulo' => 'Partes'],
            ['codigo' => 'partes.eliminar', 'nombre' => 'Eliminar partes', 'modulo' => 'Partes'],

            ['codigo' => 'detalles.ver', 'nombre' => 'Ver detalles', 'modulo' => 'Detalles'],
            ['codigo' => 'detalles.editar', 'nombre' => 'Editar detalles', 'modulo' => 'Detalles'],

            ['codigo' => 'actuaciones.ver', 'nombre' => 'Ver actuaciones', 'modulo' => 'Actuaciones'],
            ['codigo' => 'actuaciones.crear', 'nombre' => 'Crear actuaciones', 'modulo' => 'Actuaciones'],
            ['codigo' => 'actuaciones.editar', 'nombre' => 'Editar actuaciones', 'modulo' => 'Actuaciones'],
            ['codigo' => 'actuaciones.eliminar', 'nombre' => 'Eliminar actuaciones', 'modulo' => 'Actuaciones'],

            ['codigo' => 'documentos.ver', 'nombre' => 'Ver documentos', 'modulo' => 'Documentos'],
            ['codigo' => 'documentos.subir', 'nombre' => 'Subir documentos', 'modulo' => 'Documentos'],
            ['codigo' => 'documentos.descargar', 'nombre' => 'Descargar documentos', 'modulo' => 'Documentos'],
            ['codigo' => 'documentos.anular', 'nombre' => 'Anular documentos', 'modulo' => 'Documentos'],
            ['codigo' => 'documentos.eliminar', 'nombre' => 'Eliminar documentos', 'modulo' => 'Documentos'],

            ['codigo' => 'alertas.ver', 'nombre' => 'Ver alertas', 'modulo' => 'Alertas'],
            ['codigo' => 'alertas.crear', 'nombre' => 'Crear alertas', 'modulo' => 'Alertas'],
            ['codigo' => 'alertas.editar', 'nombre' => 'Editar alertas', 'modulo' => 'Alertas'],
            ['codigo' => 'alertas.eliminar', 'nombre' => 'Eliminar alertas', 'modulo' => 'Alertas'],

            ['codigo' => 'auditoria.ver', 'nombre' => 'Ver auditoría', 'modulo' => 'Auditoría'],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permisos')->updateOrInsert(
                ['codigo' => $permiso['codigo']],
                [
                    'nombre' => $permiso['nombre'],
                    'descripcion' => $permiso['nombre'],
                    'modulo' => $permiso['modulo'],
                    'estado' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}