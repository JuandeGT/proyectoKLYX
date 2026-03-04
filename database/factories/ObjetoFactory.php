<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ObjetoFactory extends Factory
{
    public function definition(): array
    {
        // 1. Decidimos aleatoriamente si el objeto será un cuchillo o una pegatina
        $tipo = $this->faker->randomElement(['cuchillo', 'pegatina']);

        // 2. Lógica pro: Si es cuchillo, inventamos números. Si es pegatina, lo dejamos en null.
        $peso = ($tipo === 'cuchillo') ? $this->faker->randomFloat(2, 0.10, 2.50) : null; // De 100g a 2.5kg
        $longitud = ($tipo === 'cuchillo') ? $this->faker->randomFloat(2, 15.00, 45.00) : null; // De 15cm a 45cm

        // 3. Devolvemos el array mezclando lo que ya tenías con lo nuevo
        return [
            'nombre' => $this->faker->word() . ' ' . $this->faker->word(), 
            'descripcion' => $this->faker->sentence(), 
            
            // CAMBIO AQUÍ: Usamos numberBetween para generar KlyxCoins enteras.
            // Si antes era entre 5€ y 500€, ahora es entre 500 y 50000 monedas.
            'precio' => $this->faker->numberBetween(500, 50000), 
            
            'imagen' => $this->faker->imageUrl(),
            'tipo' => $tipo,
            'peso' => $peso,
            'longitud' => $longitud,
        ];
    }
}