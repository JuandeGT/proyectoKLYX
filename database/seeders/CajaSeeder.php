<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Caja;
use App\Models\Objeto;

class CajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cajas = Caja::factory(15)->create();

        $todosLosObjetos = Objeto::all();

        foreach ($cajas as $caja) {
            
            $premiosParaEstaCaja = $todosLosObjetos->random(5);

            foreach ($premiosParaEstaCaja as $premio) {
                $caja->objetos()->attach($premio->id, [
                    'probabilidad' => rand(1, 25)
                ]);
            }
        }
    }
}