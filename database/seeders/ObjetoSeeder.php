<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Objeto;


class ObjetoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        
        Objeto::factory(50)->create();
    }
}