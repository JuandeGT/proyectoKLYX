<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Caja>
 */
class CajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Caja 1', 'Caja 2', 'Caja 3', 'Caja 4', 'Caja Premium']),
            
            'precio' => $this->faker->numberBetween(300, 5000),
            
            'imagen' => null,
        ];
    }
}