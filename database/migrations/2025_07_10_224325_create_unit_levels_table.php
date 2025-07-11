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
        Schema::create('unit_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('educational_unit_id');
            $table->unsignedBigInteger('educational_level_id');
            $table->timestamps();

            $table->foreign('educational_unit_id')->references('id')->on('educational_units')->onDelete('cascade');
            $table->foreign('educational_level_id')->references('id')->on('educational_levels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_levels');
    }
};
