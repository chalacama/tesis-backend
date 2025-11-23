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
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('contry')->default('Ecuador');
            $table->string('province')->nullable();
            $table->string('canton')->nullable();
            $table->unsignedBigInteger('educational_unit_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('educational_unit_id')->references('id')
            ->on('educational_units')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};
