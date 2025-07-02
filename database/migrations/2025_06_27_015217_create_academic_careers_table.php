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
        Schema::create('academic_careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('career_id')->constrained()->onDelete('cascade');
            $table->unique(['academic_program_id', 'career_id']); // Restricción de unicidad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_careers');
    }
};
