<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intercambios', function (Blueprint $table) {
            $table->id();

            // El usuario que propone el intercambio
            $table->foreignUuid('emisor_id')->constrained('users')->onDelete('cascade');

            // El usuario destinatario (NULL = oferta pública, cualquiera puede aceptar)
            $table->foreignUuid('receptor_id')->nullable()->constrained('users')->onDelete('cascade');

            // Lo que el emisor ofrece
            $table->foreignId('objeto_ofrecido_id')->nullable()->constrained('objetos')->onDelete('cascade');
            $table->integer('monedas_ofrecidas')->default(0);

            // Lo que el emisor pide a cambio
            $table->foreignId('objeto_solicitado_id')->nullable()->constrained('objetos')->onDelete('cascade');
            $table->integer('monedas_solicitadas')->default(0);

            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado', 'cancelado'])->default('pendiente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intercambios');
    }
};
