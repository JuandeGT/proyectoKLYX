<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Objeto; // IMPORTANTE: Le decimos dónde está nuestro modelo


class ObjetoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        // Le decimos al modelo Objeto que use Factory para crear 50 registros en la base de datos.
        Objeto::factory(50)->create();
    }
}