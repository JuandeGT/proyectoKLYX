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
        Schema::create('historial_aperturas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones: Quién, en qué caja, y qué le tocó
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('caja_id')->constrained()->onDelete('cascade');
            $table->foreignId('objeto_id')->constrained()->onDelete('cascade');

            // Esto es magia de Laravel: creará automáticamente 'created_at' 
            // que nos servirá como la fecha y hora exacta de la apertura.
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_aperturas');
    }
};