<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; 
use Illuminate\Support\Facades\Hash; 

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos tu cuenta personal de superusuario
        $juande = User::create([
            'nombre' => 'Juande',
            'email' => 'juandegomez2005@gmail.com',
            'password' => Hash::make('juande1234'), 
            'saldo' => 99999, 
            'suscripcion' => true,
        ]);

        // 2. Te asignamos el rol de Administrador (que ya estará creado por el RoleSeeder)
        $juande->assignRole('Admin');
    }
}