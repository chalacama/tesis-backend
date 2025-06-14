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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('order')->default(1);
            $table->boolean('random_questions')->default(false);
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('type_form_id');
            $table->timestamps();

            $table->foreign('type_form_id')->references('id')->on('type_forms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
