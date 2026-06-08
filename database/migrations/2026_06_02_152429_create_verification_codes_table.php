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
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            
            // Relación con el usuario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // El código en sí (ej. "482910")
            // Recomendación: Guarda el código encriptado (Hash) por seguridad
            $table->string('code')->nullable(); 
            //Campo para identificar al usuario en el chat bot de whatsapp y telegram para eviar el spam.
            $table->string('code_verify')->nullable()->unique();
            
            // Para qué sirve este código
            $table->enum('type', [
                'email_verification', 
                'phone_verification', 
                'password_reset'
            ]);
            
            // Por dónde se envió
            $table->enum('channel', [
                'email', 
                'whatsapp',
                'telegram'
            ])->default('email');
            
            // Control de tiempo y uso
            $table->timestamp('expires_at')->nullable(); // Cuándo caduca (ej. 15 minutos)
            $table->timestamp('code_verify_expires_at')->nullable();
            $table->timestamp('used_at')->nullable(); // Para saber si ya se usó y no dejarlo usar dos veces
            
            $table->timestamps();

            // Índices para búsquedas más rápidas
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
