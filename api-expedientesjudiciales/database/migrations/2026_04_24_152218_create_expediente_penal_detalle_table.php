<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expediente_penal_detalle', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')
                ->unique()
                ->constrained('expedientes')
                ->cascadeOnDelete();

            $table->foreignId('distrito_judicial_id')
                ->nullable()
                ->constrained('distritos_judiciales')
                ->nullOnDelete();

            $table->foreignId('dependencia_id')
                ->nullable()
                ->constrained('dependencias')
                ->nullOnDelete();

            $table->foreignId('materia_id')
                ->nullable()
                ->constrained('materias')
                ->nullOnDelete();

            $table->foreignId('etapa_id')
                ->nullable()
                ->constrained('etapas')
                ->nullOnDelete();

            $table->foreignId('especialidad_id')
                ->nullable()
                ->constrained('especialidades')
                ->nullOnDelete();

            $table->string('delito', 250)->nullable();
            $table->string('fiscalia', 250)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index('expediente_id');
            $table->index('distrito_judicial_id');
            $table->index('dependencia_id');
            $table->index('materia_id');
            $table->index('etapa_id');
            $table->index('especialidad_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_penal_detalle');
    }
};