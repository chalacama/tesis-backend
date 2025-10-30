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
        Schema::create('test_view_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_view_id');
            $table->unsignedBigInteger('question_id');
            $table->integer('order'); // El orden para este test_view (1, 2, 3...)
            $table->timestamps();

            $table->foreign('test_view_id')->references('id')->on('test_views')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_view_questions');
    }
};
