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
            $table->decimal('max_size_mb', 6, 2)->nullable();
            $table->integer('min_duration_seconds')->nullable();
            $table->integer('max_duration_seconds')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
