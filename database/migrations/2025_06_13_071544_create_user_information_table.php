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
        Schema::create('user_information', function (Blueprint $table) {
            $table->id();
            $table->date('birthdate')->nullable();
        $table->string('phone_number')->nullable();
        $table->string('province')->nullable();
        $table->string('canton')->nullable();
        $table->string('parish')->nullable();

        $table->unsignedBigInteger('academic_program')->nullable();
        $table->unsignedBigInteger('career_id')->nullable();
        $table->unsignedBigInteger('semester_id')->nullable();
        $table->unsignedBigInteger('user_id')->unique();

        $table->foreign('academic_program')->references('id')->on('academic_programs')->onDelete('set null');
        $table->foreign('career_id')->references('id')->on('careers')->onDelete('set null');
        $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_information');
    }
};
