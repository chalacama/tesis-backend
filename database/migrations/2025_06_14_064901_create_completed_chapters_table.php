<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // un capitulo puede ser completado varias veces
    public function up(): void
    {
        Schema::create('completed_chapters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('chapter_id');
            $table->decimal('content_progress', 5, 2)->default(0);
            $table->unsignedBigInteger('question_id')->nullable()->default(null);
            $table->timestamp('content_at')->nullable();
            $table->timestamp('test_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('set null');
            $table->unique(['user_id', 'chapter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('completed_chapters');
    }
};
