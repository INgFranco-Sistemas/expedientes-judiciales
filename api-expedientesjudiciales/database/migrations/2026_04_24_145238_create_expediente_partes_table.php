<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expediente_partes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')
                ->constrained('expedientes')
                ->cascadeOnDelete();

            $table->string('tipo_parte', 30);
            $table->string('tipo_persona', 20);

            $table->string('nombres_razon_social', 250);
            $table->string('documento_identidad', 20)->nullable();

            $table->string('correo', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 250)->nullable();

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index('expediente_id');
            $table->index('tipo_parte');
            $table->index('tipo_persona');
            $table->index('nombres_razon_social');
            $table->index('documento_identidad');
        });

        DB::statement("
            ALTER TABLE expediente_partes
            ADD CONSTRAINT chk_expediente_partes_tipo_parte
            CHECK (tipo_parte IN (
                'DEMANDANTE',
                'DEMANDADO',
                'DENUNCIANTE',
                'DENUNCIADO',
                'SOLICITANTE',
                'CONTRATISTA',
                'ENTIDAD',
                'TERCERO'
            ))
        ");

        DB::statement("
            ALTER TABLE expediente_partes
            ADD CONSTRAINT chk_expediente_partes_tipo_persona
            CHECK (tipo_persona IN ('NATURAL', 'JURIDICA'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_partes');
    }
};