<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; 
use Illuminate\Support\Facades\Hash; 

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Creamos al superusuario
        $juande = User::create([
            'nombre' => 'Juande',
            'email' => 'juandegomez2005@gmail.com',
            'password' => Hash::make('juande1234'), 
            'saldo' => 99999, 
            'suscripcion' => true,
        ]);

        // Le asignamos el rol de admin
        $juande->assignRole('Admin');
    }
}