<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')
                ->constrained('expedientes')
                ->cascadeOnDelete();

            $table->foreignId('tipo_alerta_id')
                ->constrained('tipos_alerta')
                ->restrictOnDelete();

            $table->timestamp('fecha_alerta');

            $table->text('mensaje');

            $table->foreignId('usuario_destino_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->boolean('leido')->default(false);

            $table->string('estado', 20)->default('ACTIVA');

            $table->timestamps();

            $table->index('expediente_id');
            $table->index('tipo_alerta_id');
            $table->index('usuario_destino_id');
            $table->index('fecha_alerta');
            $table->index('leido');
            $table->index('estado');
        });

        DB::statement("
            ALTER TABLE alertas
            ADD CONSTRAINT chk_alertas_estado
            CHECK (estado IN ('ACTIVA', 'ATENDIDA', 'ANULADA'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};