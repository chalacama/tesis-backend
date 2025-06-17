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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('modulo_id');
            $table->unsignedBigInteger('learning_content_id')->nullable()->unique(); // uno a uno
            $table->unsignedBigInteger('form_id')->nullable(); // varios capítulos pueden compartir el mismo formulario
            $table->integer('order')->default(1);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('modulo_id')->references('id')->on('modules')->onDelete('cascade');
            $table->foreign('learning_content_id')->references('id')->on('learning_contents')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('forms')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
