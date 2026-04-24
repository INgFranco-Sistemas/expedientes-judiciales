<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();

            $table->string('tabla_afectada', 100);
            $table->unsignedBigInteger('registro_id');

            $table->string('accion', 50);

            $table->jsonb('valor_anterior_json')->nullable();
            $table->jsonb('valor_nuevo_json')->nullable();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('fecha_evento')->useCurrent();

            $table->timestamps();

            $table->index(['tabla_afectada', 'registro_id']);
            $table->index('accion');
            $table->index('usuario_id');
            $table->index('fecha_evento');
        });

        DB::statement("
            ALTER TABLE auditoria
            ADD CONSTRAINT chk_auditoria_accion
            CHECK (accion IN (
                'CREATE',
                'UPDATE',
                'DELETE_LOGICO',
                'LOGIN',
                'LOGOUT',
                'CAMBIO_ESTADO',
                'REASIGNACION',
                'SUBIDA_DOCUMENTO',
                'DESCARGA_DOCUMENTO',
                'CIERRE_EXPEDIENTE',
                'REAPERTURA_EXPEDIENTE'
            ))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};