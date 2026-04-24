<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')
                ->constrained('expedientes')
                ->cascadeOnDelete();

            $table->foreignId('actuacion_id')
                ->nullable()
                ->constrained('actuaciones')
                ->nullOnDelete();

            $table->foreignId('tipo_documento_id')
                ->constrained('tipos_documento')
                ->restrictOnDelete();

            $table->string('nombre_original', 255);
            $table->string('nombre_guardado', 255);
            $table->string('ruta_archivo', 500);

            $table->string('extension', 20);
            $table->string('mime_type', 100)->nullable();

            $table->bigInteger('peso_bytes');
            $table->string('hash_archivo', 128)->nullable();

            $table->integer('version')->default(1);

            $table->text('observacion')->nullable();

            $table->foreignId('subido_por')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->timestamp('fecha_subida')->useCurrent();

            $table->string('estado', 20)->default('ACTIVO');

            $table->timestamps();
            $table->softDeletes();

            $table->index('expediente_id');
            $table->index('actuacion_id');
            $table->index('tipo_documento_id');
            $table->index('subido_por');
            $table->index('estado');
            $table->index('fecha_subida');
        });

        DB::statement("
            ALTER TABLE documentos
            ADD CONSTRAINT chk_documentos_estado
            CHECK (estado IN ('ACTIVO', 'ANULADO'))
        ");

        DB::statement("
            ALTER TABLE documentos
            ADD CONSTRAINT chk_documentos_version
            CHECK (version >= 1)
        ");

        DB::statement("
            ALTER TABLE documentos
            ADD CONSTRAINT chk_documentos_peso
            CHECK (peso_bytes >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};