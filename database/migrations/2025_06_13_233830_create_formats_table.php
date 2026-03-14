<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('formats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        
            // RECOMENDADO: Guardar siempre en Bytes como entero sin signo.
            // Ej: 800 MB = 838860800 bytes | 0.0366 MB = 38377 bytes
            $table->unsignedBigInteger('max_size_bytes')->nullable();
        
            // RECOMENDADO: Enteros sin signo (unsigned) para evitar duraciones negativas
            $table->unsignedInteger('min_duration_seconds')->nullable();
            $table->unsignedInteger('max_duration_seconds')->nullable();
        
            $table->boolean('enabled')->default(false);
        
            // FORMA MODERNA: Sintaxis más limpia de Laravel para llaves foráneas
            $table->foreignId('type_learning_content_id')
              ->constrained('type_learning_contents')
              ->cascadeOnDelete();
        
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formats');
    }
};
