<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();

            $table->string('username', 50)->unique();
            $table->string('password', 255);

            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->string('nombre_completo', 310);

            $table->string('dni', 8)->unique();
            $table->string('correo_institucional', 150)->nullable()->unique();
            $table->string('telefono', 20)->nullable();

            $table->foreignId('especialidad_id')
                ->nullable()
                ->constrained('especialidades')
                ->nullOnDelete();

            $table->foreignId('perfil_id')
                ->constrained('perfiles')
                ->restrictOnDelete();

            $table->string('estado_usuario', 20)->default('ACTIVO');

            $table->date('fecha_inicio_asignacion')->nullable();
            $table->date('fecha_termino_asignacion')->nullable();

            $table->timestamp('ultimo_acceso_at')->nullable();
            $table->integer('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('perfil_id');
            $table->index('especialidad_id');
            $table->index('estado_usuario');
        });

        DB::statement("
            ALTER TABLE usuarios
            ADD CONSTRAINT chk_usuarios_estado
            CHECK (estado_usuario IN ('ACTIVO', 'INACTIVO', 'BLOQUEADO', 'CESADO'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};