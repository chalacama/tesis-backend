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
    Schema::create('user_information', function (Blueprint $table) {
        $table->id();
        $table->date('birthdate')->nullable();
        // 👇 Ahora guardamos solo IDs numéricos
        $table->unsignedInteger('province_id')->nullable();
        $table->unsignedInteger('canton_id')->nullable();
        $table->unsignedInteger('parish_id')->nullable();

        $table->enum('sexo', ['MUJER', 'HOMBRE'])->nullable();

        $table->enum('estado_civil', [
            'casado/a',
            'unido/a',
            'separado/a',
            'divorciado/a',
            'viudo/a',
            'soltero/a',
        ])->nullable();

        $table->enum('discapacidad', ['si', 'no'])->nullable();

        $table->enum('discapacidad_permanente', [
            'intelectual (retraso mental)',
            'físico-motora (parálisis y amputaciones)',
            'visual (ceguera)',
            'auditiva (sordera)',
            'mental (enfermedades psiquiátricas)',
            'otro tipo',
        ])->nullable();

        $table->enum('asistencia_establecimiento_discapacidad', ['si', 'no'])
            ->nullable();

        $table->unsignedBigInteger('user_id')->unique();

        $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

        // 👇 Opcional, pero ayuda a buscar rápido por ubicación
        $table->index(['province_id', 'canton_id', 'parish_id']);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_information');
    }
};
