<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Importamos tu modelo de Usuario
use Spatie\Permission\Models\Role; // Importamos la herramienta de Roles
use Illuminate\Support\Facades\Hash; // Herramienta para encriptar tu contraseña

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos los dos roles en la base de datos
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleUsuario = Role::create(['name' => 'Usuario']);

        // 2. Creamos tu cuenta personal de superusuario
        $juande = User::create([
            'nombre' => 'Juande',
            'email' => 'juandegomez2005@gmail.com',
            'password' => Hash::make('juande1234'), // Encriptamos 'juande1234' por seguridad
            'monedas' => 9999.99, // Dinero infinito para probar las cajas luego
            'suscripcion' => true,
        ]);

        // 3. Te asignamos el rol de Administrador
        $juande->assignRole($roleAdmin);
    }
}
