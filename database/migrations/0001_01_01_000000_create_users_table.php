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
    
        
        Schema::create('users', function (Blueprint $table) {
            $table->id();            
            $table->string('google_id')->nullable()->unique(); // Para el ID de Google
            $table->string('name');
            $table->string('lastname');
            $table->string('username')->unique();
            $table->string('cedula', 10)->unique()->nullable();
            $table->timestamp('username_at')->nullable()->default(null);
            $table->string('email')->unique()->nullable();
            $table->string('phone_number', 13)->unique()->nullable();
            $table->string('password'); // Contraseña opcional
            $table->string('profile_picture_url')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('cedula_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
