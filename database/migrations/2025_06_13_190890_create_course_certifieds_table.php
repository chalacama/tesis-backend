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
        Schema::create('course_certifieds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->unique(); // <-- Clave única para relación 1:1
            $table->boolean('is_certified')->default(false);
            $table->boolean('is_unlimited')->default(true);
            $table->integer('max_attempts')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_certifieds');
    }
};
