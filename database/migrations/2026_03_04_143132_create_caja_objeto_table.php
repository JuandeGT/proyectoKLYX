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
            
            $table->foreignId('caja_id')->constrained()->onDelete('cascade');
            $table->foreignId('objeto_id')->constrained()->onDelete('cascade');
            
            $table->integer('probabilidad')->default(20);
            
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