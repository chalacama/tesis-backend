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
            // Cambiado a 8, 4 para soportar 800 y 0.0366
            $table->decimal('max_size_mb', 8, 4)->nullable();
            $table->integer('min_duration_seconds')->nullable();
            $table->integer('max_duration_seconds')->nullable();
            $table->boolean('enabled')->default(false);
            
            // ¡ELIMINADO el ->unique() de aquí abajo!
            $table->unsignedBigInteger('type_learning_content_id'); 
            $table->foreign('type_learning_content_id')->references('id')->on('type_learning_contents')->onDelete('cascade');
            
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
