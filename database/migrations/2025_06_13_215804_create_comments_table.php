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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            
            // foreignId es una forma más moderna y corta de definir la llave foránea.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->text('texto');
            
            // Columna para las respuestas. Es nullable porque los comentarios principales no tienen padre.
            $table->unsignedBigInteger('parent_id')->nullable();
            
            // Columnas polimórficas. Creará commentable_id y commentable_type.
            $table->morphs('commentable');
            

            $table->timestamps();
            $table->softDeletes();
            // Definimos la llave foránea para las respuestas (apunta a la misma tabla).
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
        });

        /* Schema::create('comments', function (Blueprint $table) {
            $table->id();
        $table->text('texto');
        $table->boolean('enabled')->default(true);
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('curso_id');
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('curso_id')->references('id')->on('courses')->onDelete('cascade');
        }); */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
