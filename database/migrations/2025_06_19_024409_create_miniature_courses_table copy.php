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
        Schema::create('miniature_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->unique(); // Unique to enforce one-to-one
            $table->text('url')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->unsignedBigInteger('type_thumbnail_id'); // FK a type_thumbnails
            $table->timestamps();
            
            $table->foreign('type_thumbnail_id')->references('id')->on('type_thumbnails')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miniature_courses');
    }
};
