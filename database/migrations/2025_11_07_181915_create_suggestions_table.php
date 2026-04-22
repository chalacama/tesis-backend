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
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('texto'); // El término que el usuario escribió o el nombre del recurso
            $table->enum('search_type', ['title', 'career', 'category', 'difficulty', 'tutor'])
                  ->default('title'); // Define en qué "cubeta" cae la búsqueda
            
            $table->unsignedBigInteger('entity_id')->nullable(); // ID del curso, categoría o carrera (opcional)
            $table->integer('searched')->default(1); 
            $table->unsignedBigInteger('user_id');
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Índice para búsquedas rápidas por tipo y texto
            $table->index(['search_type', 'texto']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
