<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest; // Importamos a tu vigilante
use Illuminate\Support\Facades\Hash; // Herramienta para encriptar contraseñas
use App\Http\Requests\LoginUserRequest; // Importamos el nuevo vigilante
use Illuminate\Support\Facades\Auth; // Herramienta de Laravel para comprobar contraseñas

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

    // Función para iniciar sesión (Login)
    public function verify(LoginUserRequest $request)
    {
        // 1. Comprobamos si el email y la contraseña coinciden en la base de datos
        // Auth::attempt coge la contraseña escrita, la encripta por dentro y la compara con la guardada.
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si falla, devolvemos un error 401 (No Autorizado)
            return response()->json([
                'error' => true,
                'message' => 'Credenciales incorrectas.',
                'data' => [],
                'code' => 401 
            ], 401);
        }

        // 2. Si el código llega aquí, el usuario y contraseña son correctos. 
        // Buscamos a ese usuario en la base de datos para extraer sus datos:
        $user = User::where('email', $request->email)->first();

        // 3. Le fabricamos su Token VIP de Sanctum para que pueda jugar
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Devolvemos tu mensaje personalizado y el Token
        return response()->json([
            'error' => false,
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'usuario' => $user,
                'token' => $token // Entregamos la llave
            ],
            'code' => 200 // 200 significa "Todo OK"
        ], 200);
    }
}