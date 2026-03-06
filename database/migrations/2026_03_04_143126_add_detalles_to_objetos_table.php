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
        Schema::table('objetos', function (Blueprint $table) {
            $table->string('tipo')->default('cuchillo')->after('nombre');
            
            $table->decimal('peso', 5, 2)->nullable()->after('tipo');
            $table->decimal('longitud', 5, 2)->nullable()->after('peso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('objetos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'peso', 'longitud']);
        });
    }
};