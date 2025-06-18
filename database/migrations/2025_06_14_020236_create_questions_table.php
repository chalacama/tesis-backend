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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('statement');
            $table->decimal('spot', 2, 1)->default(1);
            $table->integer('order')->default(1);
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('type_questions_id');
            $table->unsignedBigInteger('form_id');
            $table->timestamps();

            $table->foreign('type_questions_id')->references('id')->on('type_questions')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
