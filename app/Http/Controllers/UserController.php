<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest; // Importamos a tu vigilante
use Illuminate\Support\Facades\Hash; // Herramienta para encriptar contraseñas

class UserController extends Controller
{
    // Función para registrar un nuevo usuario (Registro)
    public function store(StoreUserRequest $request)
    {
        // 1. Si el código llega aquí, el StoreUserRequest ya ha comprobado que los datos son perfectos.
        // Creamos al usuario en la base de datos:
        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encriptamos la contraseña siempre
            // Nota: 'monedas' y 'suscripcion' se ponen solos a 0 y false por defecto gracias a la base de datos
        ]);

        // 2. Le asignamos automáticamente el rol de usuario normal
        $user->assignRole('Usuario');

        // 3. Devolvemos la respuesta imitando la estructura de tu profesor
        return response()->json([
            'error' => false,
            'message' => 'Cuenta creada correctamente.',
            'data' => $user,
            'code' => 201 // 201 significa "Creado con éxito" en internet
        ], 201);
    }
}