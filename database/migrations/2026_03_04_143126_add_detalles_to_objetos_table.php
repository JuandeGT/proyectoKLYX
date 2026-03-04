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
        // Usamos Schema::table en lugar de Schema::create porque la tabla ya existe
        Schema::table('objetos', function (Blueprint $table) {
            // Añadimos 'tipo' (por defecto será cuchillo para los que ya existan)
            $table->string('tipo')->default('cuchillo')->after('nombre');
            
            // Añadimos peso y longitud. Le ponemos ->nullable() para que las pegatinas 
            // puedan tener este campo vacío sin que la base de datos dé error.
            $table->decimal('peso', 5, 2)->nullable()->after('tipo'); // Ej: 0.45 (kg)
            $table->decimal('longitud', 5, 2)->nullable()->after('peso'); // Ej: 21.50 (cm)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // El método down es el "botón de pánico". Si nos arrepentimos, borra estas 3 columnas.
        Schema::table('objetos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'peso', 'longitud']);
        });
    }
};