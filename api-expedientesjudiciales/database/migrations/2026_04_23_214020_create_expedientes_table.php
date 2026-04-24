<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tipo_expediente_id')
                ->constrained('tipos_expediente')
                ->restrictOnDelete();

            $table->string('numero_expediente', 100);
            $table->integer('anio_expediente')->nullable();
            $table->string('codigo_unico_interno', 50)->unique();

            $table->foreignId('distrito_judicial_id')
                ->nullable()
                ->constrained('distritos_judiciales')
                ->nullOnDelete();

            $table->foreignId('dependencia_id')
                ->nullable()
                ->constrained('dependencias')
                ->nullOnDelete();

            $table->foreignId('especialidad_id')
                ->nullable()
                ->constrained('especialidades')
                ->nullOnDelete();

            $table->foreignId('instancia_id')
                ->nullable()
                ->constrained('instancias')
                ->nullOnDelete();

            $table->foreignId('materia_id')
                ->nullable()
                ->constrained('materias')
                ->nullOnDelete();

            $table->foreignId('etapa_id')
                ->nullable()
                ->constrained('etapas')
                ->nullOnDelete();

            $table->foreignId('estado_expediente_id')
                ->constrained('estados_expediente')
                ->restrictOnDelete();

            $table->foreignId('encargado_actual_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('prioridad_id')
                ->nullable()
                ->constrained('prioridades')
                ->nullOnDelete();

            $table->decimal('monto', 14, 2)->nullable()->default(0);
            $table->text('pretensiones')->nullable();
            $table->text('observaciones_generales')->nullable();

            $table->date('fecha_registro');
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_ultima_actuacion')->nullable();
            $table->date('fecha_proximo_vencimiento')->nullable();
            $table->date('fecha_cierre')->nullable();

            $table->foreignId('motivo_cierre_id')
                ->nullable()
                ->constrained('motivos_cierre')
                ->nullOnDelete();

            $table->boolean('importante')->default(false);
            $table->string('estado_registro', 20)->default('ACTIVO');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tipo_expediente_id', 'numero_expediente', 'anio_expediente'], 'uq_expediente_tipo_numero_anio');

            $table->index('tipo_expediente_id');
            $table->index('estado_expediente_id');
            $table->index('encargado_actual_id');
            $table->index('fecha_registro');
            $table->index('fecha_proximo_vencimiento');
            $table->index('importante');
            $table->index('numero_expediente');
        });

        DB::statement("
            ALTER TABLE expedientes
            ADD CONSTRAINT chk_expedientes_estado_registro
            CHECK (estado_registro IN ('ACTIVO', 'CERRADO', 'ARCHIVADO'))
        ");

        DB::statement("
            ALTER TABLE expedientes
            ADD CONSTRAINT chk_expedientes_monto_no_negativo
            CHECK (monto IS NULL OR monto >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};