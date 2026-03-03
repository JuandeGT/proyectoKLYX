<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest; // Importamos a tu vigilante
use Illuminate\Support\Facades\Hash; // Herramienta para encriptar contraseñas
use App\Http\Requests\LoginUserRequest; // Importamos el nuevo vigilante
use Illuminate\Support\Facades\Auth; // Herramienta de Laravel para comprobar contraseñas
use App\Http\Requests\RecargarMonedasRequest;
use Illuminate\Http\Request;
use App\Models\Transaccion;
use Illuminate\Support\Facades\DB;

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
        // 1. Buscamos al usuario en la base de datos por su email
        $user = User::where('email', $request->email)->first();

        // 2. Comprobamos si existe y si la contraseña encriptada coincide (Hash::check)
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Si falla, devolvemos un error 401 (No Autorizado)
            return response()->json([
                'error' => true,
                'message' => 'Credenciales incorrectas.',
                'data' => [],
                'code' => 401 
            ], 401);
        }

        // Comprobación de caducidad VIP al hacer Login
        if ($user->suscripcion && $user->fecha_fin_suscripcion && $user->fecha_fin_suscripcion < now()) {
            $user->suscripcion = false;
            $user->fecha_fin_suscripcion = null;
            $user->save();
        }

        // Le fabricamos su Token VIP de Sanctum para que pueda jugar
        $token = $user->createToken('auth_token')->plainTextToken;

        // Sacamos el array limpio (ej: ["Usuario"])
        $nombresRoles = $user->getRoleNames(); 

        // Le decimos a Laravel: "Oculta la relación bruta de la base de datos"
        $user->makeHidden('roles');

        // Devolvemos tu mensaje personalizado y el Token
        return response()->json([
            'error' => false,
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'usuario' => $user,
                'rol' => $nombresRoles,
                'token' => $token // Entregamos la llave
            ],
            'code' => 200 // 200 significa "Todo OK"
        ], 200);
    }

    // Función para cerrar sesión (Logout)
    public function logout(Request $request)
    {
        // Identificamos el token que está usando el usuario en este momento y lo destruimos
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'error' => false,
            'message' => 'Sesión cerrada correctamente. ¡Vuelve pronto!',
            'data' => [],
            'code' => 200
        ], 200);
    }

    // Función para recargar Klyx Coins (Igual para todos los usuarios)
    public function recargar(RecargarMonedasRequest $request)
    {
        // 1. Identificamos al usuario gracias a su Token VIP de Sanctum
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            // 2. Le sumamos las monedas a su cartera
            $user->monedas += $request->cantidad;
            $user->save(); // Guardamos en la base de datos

            // Registramos la transacción
            Transaccion::create([
                'user_id' => $user->id,
                'tipo' => 'recarga',
                'cantidad' => $request->cantidad,
                'descripcion' => 'Recarga de ' . $request->cantidad . ' Klyx Coins.'
            ]);
        }); // Si algo falla dentro de esta función no se hace ninguna, es por seguridad.

        // 3. Devolvemos la respuesta
        return response()->json([
            'error' => false,
            'message' => '¡Klyx Coins recargadas con éxito!',
            'data' => [
                'usuario' => $user->nombre,
                'monedas_actuales' => $user->monedas,
                'vip' => $user->suscripcion
            ],
            'code' => 200
        ], 200);
    }

    // Función para comprar la suscripción VIP
    public function comprarSuscripcion(Request $request)
    {
        $user = $request->user();
        $precioVip = 1000; // Puedes cambiar el precio aquí

        // 1. ¿Ya es VIP? Evitamos que gaste monedas si ya lo tiene activo
        if ($user->suscripcion && $user->fecha_fin_suscripcion > now()) {
            return response()->json([
                'error' => true,
                'message' => 'Ya eres un usuario VIP.',
                'data' => [],
                'code' => 400
            ], 400); // 400 es Bad Request (Petición incorrecta)
        }

        // 2. ¿Tiene dinero suficiente?
        if ($user->monedas < $precioVip) {
            return response()->json([
                'error' => true,
                'message' => 'No tienes suficientes Klyx Coins. Necesitas ' . $precioVip . '.',
                'data' => [],
                'code' => 400
            ], 400);
        }

        DB::transaction(function () use ($user, $precioVip) {
            // 3. Si llega aquí, todo está en orden. Procedemos al cobro.
            $user->monedas -= $precioVip; // Le restamos las monedas
            $user->suscripcion = true; // Le damos la insignia VIP
            $user->fecha_fin_suscripcion = now()->addDays(30); // Le sumamos 30 días exactos desde hoy
            $user->save(); // Guardamos los cambios en la base de datos

            // Registramos la transacción
            Transaccion::create([
                'user_id' => $user->id,
                'tipo' => 'compra_vip',
                'cantidad' => -$precioVip, // En NEGATIVO porque gasta monedas
                'descripcion' => 'Compra de suscripción VIP (30 días).'
            ]);
        }); // Si algo falla dentro de esta función no se hace ninguna, es por seguridad.

        

        // 4. Devolvemos la respuesta de éxito
        return response()->json([
            'error' => false,
            'message' => '¡Felicidades! Ya eres VIP.',
            'data' => [
                'usuario' => $user->nombre,
                'monedas_restantes' => $user->monedas,
                'vip' => $user->suscripcion,
                'fecha_fin_vip' => $user->fecha_fin_suscripcion
            ],
            'code' => 200
        ], 200);
    }

    public function historialTransacciones(Request $request)
    {
        // 1. Obtenemos al usuario que está haciendo la petición (por su token)
        $user = $request->user();

        // 2. Buscamos sus transacciones en la base de datos y las ordenamos por fecha (las más recientes primero)
        $transacciones = Transaccion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Devolvemos la respuesta al Frontend
        return response()->json([
            'error' => false,
            'message' => 'Historial de transacciones recuperado con éxito.',
            'data' => $transacciones,
            'code' => 200
        ], 200);
    }
}