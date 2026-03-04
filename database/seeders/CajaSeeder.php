<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Caja;
use App\Models\Objeto; // IMPORTANTE: Importamos el modelo Objeto también

class CajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Creamos las 15 cajas y las guardamos en una variable
        $cajas = Caja::factory(15)->create();

        // 2. Cogemos los 50 objetos que el ObjetoSeeder acaba de crear justo antes
        $todosLosObjetos = Objeto::all();

        // 3. Recorremos las 15 cajas una a una
        foreach ($cajas as $caja) {
            
            // Elegimos 5 objetos al azar de los 50 que hay
            $premiosParaEstaCaja = $todosLosObjetos->random(5);

            // Los metemos en la caja
            foreach ($premiosParaEstaCaja as $premio) {
                $caja->objetos()->attach($premio->id, [
                    'probabilidad' => rand(1, 25) // Le damos una probabilidad inventada
                ]);
            }
        }
    }
}