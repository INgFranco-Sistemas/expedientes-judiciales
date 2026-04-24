<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actuaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')
                ->constrained('expedientes')
                ->cascadeOnDelete();

            $table->foreignId('tipo_actuacion_id')
                ->constrained('tipos_actuacion')
                ->restrictOnDelete();

            $table->timestamp('fecha_actuacion');

            $table->text('descripcion');

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->timestamp('fecha_proxima_accion')->nullable();

            $table->text('resultado')->nullable();
            $table->text('observaciones')->nullable();

            $table->foreignId('estado_resultante_id')
                ->nullable()
                ->constrained('estados_expediente')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('expediente_id');
            $table->index('tipo_actuacion_id');
            $table->index('usuario_id');
            $table->index('fecha_actuacion');
            $table->index('fecha_proxima_accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actuaciones');
    }
};