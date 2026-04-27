<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PerfilSeeder::class,
            PermisoSeeder::class,
            PerfilPermisoSeeder::class,

            EspecialidadSeeder::class,
            TipoExpedienteSeeder::class,
            EstadoExpedienteSeeder::class,
            DistritoJudicialSeeder::class,
            InstanciaSeeder::class,
            MateriaSeeder::class,
            EtapaSeeder::class,
            TipoActuacionSeeder::class,
            TipoDocumentoSeeder::class,
            PrioridadSeeder::class,
            MotivoCierreSeeder::class,
            TipoAlertaSeeder::class,
            UsuarioAdminSeeder::class,
        ]);
    }
}
