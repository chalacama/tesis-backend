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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            
            $table->string('title');
            $table->text('description');
            $table->boolean('private')->default(false);
            $table->string('code')->nullable()->unique();
            $table->boolean('enabled')->default(false);
            $table->foreignId('difficulty_id')->constrained()->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
