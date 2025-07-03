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
        Schema::create('type_learning_contents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('max_size')->nullable();
            $table->string('min_duration_seconds')->nullable();
            $table->string('max_duration_seconds')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_learning_contents');
    }
};
