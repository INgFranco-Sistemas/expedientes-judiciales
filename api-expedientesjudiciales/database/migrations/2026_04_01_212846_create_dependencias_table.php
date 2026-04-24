<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distrito_judicial_id')
                ->nullable()
                ->constrained('distritos_judiciales')
                ->nullOnDelete();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->unique(['distrito_judicial_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependencias');
    }
};