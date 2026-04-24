<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expediente_masc_detalle', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')
                ->unique()
                ->constrained('expedientes')
                ->cascadeOnDelete();

            $table->string('subtipo_masc', 20);

            $table->foreignId('solicitante_id_parte')
                ->nullable()
                ->constrained('expediente_partes')
                ->nullOnDelete();

            $table->foreignId('contratista_id_parte')
                ->nullable()
                ->constrained('expediente_partes')
                ->nullOnDelete();

            $table->foreignId('contrato_id')
                ->nullable()
                ->constrained('contratos')
                ->nullOnDelete();

            $table->string('centro_masc', 250)->nullable();
            $table->string('estado_especial', 100)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index('expediente_id');
            $table->index('subtipo_masc');
            $table->index('solicitante_id_parte');
            $table->index('contratista_id_parte');
            $table->index('contrato_id');
        });

        DB::statement("
            ALTER TABLE expediente_masc_detalle
            ADD CONSTRAINT chk_expediente_masc_subtipo
            CHECK (subtipo_masc IN ('ARBITRAJE', 'CONCILIACION'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_masc_detalle');
    }
};