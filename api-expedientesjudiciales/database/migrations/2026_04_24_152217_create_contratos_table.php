<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();

            $table->string('numero_contrato', 100)->unique();
            $table->text('objeto_contrato')->nullable();
            $table->date('fecha_contrato')->nullable();

            $table->string('entidad_contratante', 250)->nullable();
            $table->string('contratista', 250)->nullable();

            $table->decimal('monto_contrato', 14, 2)->nullable()->default(0);

            $table->text('observaciones')->nullable();
            $table->string('documento_contrato_url', 500)->nullable();

            $table->timestamps();

            $table->index('numero_contrato');
            $table->index('contratista');
            $table->index('fecha_contrato');
        });

        DB::statement("
            ALTER TABLE contratos
            ADD CONSTRAINT chk_contratos_monto_no_negativo
            CHECK (monto_contrato IS NULL OR monto_contrato >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};