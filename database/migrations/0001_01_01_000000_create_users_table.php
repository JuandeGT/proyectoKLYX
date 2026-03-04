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
            $table->uuid('id')->primary(); // Cambiamos el número autoincremental por un UUID
            
            $table->string('nombre');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            
            // integer numeros enteros limpios Ej: 999999.99
            $table->integer('saldo')->default(0); 
            
            // boolean: Solo guarda true (1) o false (0)
            $table->boolean('suscripcion')->default(false); 
            
            // timestamp: Guarda fechas y horas
            $table->timestamp('fecha_fin_suscripcion')->nullable(); 

            $table->rememberToken();
            $table->timestamps(); // Crea las columnas 'created_at' y 'updated_at'
        });

        // Creado por laravel
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
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
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
