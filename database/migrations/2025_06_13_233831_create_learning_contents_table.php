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
        Schema::create('learning_contents', function (Blueprint $table) {
            $table->id();
            $table->text('url')->nullable();
            // Cambiado a 8, 4 aquí también
            $table->unsignedBigInteger('size_bytes')->nullable(); 
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('type_content_id');
            $table->unsignedBigInteger('chapter_id')->unique(); // Este sí lleva unique (1 a 1)
            $table->unsignedBigInteger('format_id');
            $table->timestamps();
            $table->foreign('type_content_id')->references('id')->on('type_learning_contents')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('restrict');
            $table->foreign('format_id')->references('id')->on('formats')->onDelete('restrict');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_contents');
    }
};
