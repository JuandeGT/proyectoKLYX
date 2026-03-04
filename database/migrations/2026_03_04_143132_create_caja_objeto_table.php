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
        Schema::create('caja_objeto', function (Blueprint $table) {
            $table->id();
            
            // Las dos claves foráneas que unen la caja y el objeto
            $table->foreignId('caja_id')->constrained()->onDelete('cascade');
            $table->foreignId('objeto_id')->constrained()->onDelete('cascade');
            
            // El porcentaje de que toque este objeto en la caja
            $table->decimal('probabilidad', 5, 2)->default(10.00); // Ej: 10.00%
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_objeto');
    }
};