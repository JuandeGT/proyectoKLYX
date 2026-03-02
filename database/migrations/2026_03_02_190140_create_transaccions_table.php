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
        Schema::create('transaccions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Usamos UUID también para los tickets
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete(); // Si borramos al usuario, se borra su historial
            $table->string('tipo'); // Ej: 'recarga', 'compra_vip', 'compra_caja'
            $table->integer('cantidad'); // Monedas (positivo si gana, negativo si gasta)
            $table->string('descripcion'); // Ej: 'Recarga de 500 Klyx Coins mediante tarjeta'
            $table->timestamps(); // Guarda la fecha y hora exacta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaccions');
    }
};
