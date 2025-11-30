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

            // Siempre Ecuador
            $table->string('contry')->default('ECUADOR');

            // Ahora guardamos SOLO IDs, opcionalmente null
            $table->unsignedInteger('province_id')->nullable();
            $table->unsignedInteger('canton_id')->nullable();

            $table->unsignedBigInteger('educational_unit_id');
            $table->timestamps();

            $table->foreign('educational_unit_id')
                ->references('id')
                ->on('educational_units')
                ->onDelete('cascade');

            // Opcional: índices para filtros
            $table->index(['province_id', 'canton_id']);
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
