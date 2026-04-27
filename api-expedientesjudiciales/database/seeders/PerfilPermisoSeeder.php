<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = DB::table('perfiles')->where('nombre', 'Administrador Procuraduria')->first();
        $gestor = DB::table('perfiles')->where('nombre', 'Gestor Procuraduria')->first();
        $consulta = DB::table('perfiles')->where('nombre', 'Consulta Procuraduria')->first();

        if (!$admin || !$gestor || !$consulta) {
            return;
        }

        $todosLosPermisos = DB::table('permisos')->pluck('id')->toArray();

        foreach ($todosLosPermisos as $permisoId) {
            DB::table('perfil_permiso')->updateOrInsert([
                'perfil_id' => $admin->id,
                'permiso_id' => $permisoId,
            ], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permisosGestor = [
            'dashboard.ver',
            'catalogos.ver',

            'expedientes.ver',
            'expedientes.crear',
            'expedientes.editar',
            'expedientes.cerrar',

            'partes.ver',
            'partes.crear',
            'partes.editar',
            'partes.eliminar',

            'detalles.ver',
            'detalles.editar',

            'actuaciones.ver',
            'actuaciones.crear',
            'actuaciones.editar',
            'actuaciones.eliminar',

            'documentos.ver',
            'documentos.subir',
            'documentos.descargar',
            'documentos.anular',

            'alertas.ver',
            'alertas.crear',
            'alertas.editar',
        ];

        $this->asignarPermisos($gestor->id, $permisosGestor);

        $permisosConsulta = [
            'dashboard.ver',
            'catalogos.ver',

            'expedientes.ver',
            'partes.ver',
            'detalles.ver',
            'actuaciones.ver',
            'documentos.ver',
            'documentos.descargar',
            'alertas.ver',
        ];

        $this->asignarPermisos($consulta->id, $permisosConsulta);
    }

    private function asignarPermisos(int $perfilId, array $codigos): void
    {
        $permisos = DB::table('permisos')
            ->whereIn('codigo', $codigos)
            ->get();

        foreach ($permisos as $permiso) {
            DB::table('perfil_permiso')->updateOrInsert([
                'perfil_id' => $perfilId,
                'permiso_id' => $permiso->id,
            ], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}