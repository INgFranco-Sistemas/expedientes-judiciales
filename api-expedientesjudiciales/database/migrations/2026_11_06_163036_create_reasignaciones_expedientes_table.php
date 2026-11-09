<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reasignaciones_expedientes', function (Blueprint $table) {
            $table->id();

            // Identifica todos los movimientos de una misma operación
            $table->uuid('lote_uuid');

            // Expediente que cambia de responsable
            $table->foreignId('expediente_id')
                ->constrained('expedientes')
                ->restrictOnDelete();

            // Responsable anterior
            $table->foreignId('usuario_origen_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            // Nuevo responsable
            $table->foreignId('usuario_destino_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            // Administrador que realiza la transferencia
            $table->foreignId('realizado_por_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            // Justificación de la reasignación
            $table->text('motivo');

            $table->timestamps();

            // Índices para consultas de historial
            $table->index('lote_uuid');
            $table->index('expediente_id');
            $table->index('usuario_origen_id');
            $table->index('usuario_destino_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reasignaciones_expedientes');
    }
};
