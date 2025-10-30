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
        Schema::create('test_view_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_view_question_id'); 
            $table->unsignedBigInteger('answer_id');
            $table->integer('order'); // El orden para esta pregunta (1, 2, 3...)
            $table->timestamps();

            $table->foreign('test_view_question_id')->references('id')->on('test_view_questions')->onDelete('cascade');
            $table->foreign('answer_id')->references('id')->on('answers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_view_answers');
    }
};
