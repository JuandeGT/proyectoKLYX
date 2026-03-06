<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RecargarSaldoRequest;
use Illuminate\Http\Request;
use App\Models\Transaccion;
use Illuminate\Support\Facades\DB;
use App\Models\HistorialApertura;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encriptamos la contraseña
            // El saldo y la suscripción se ponen a 0 y falso por defecto en la base de datos
        ]);

        $user->assignRole('Usuario');

        return response()->json([
            'error' => false,
            'message' => 'Cuenta creada correctamente.',
            'data' => $user,
            'code' => 201
        ], 201);
    }

    public function verify(LoginUserRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => true,
                'message' => 'Credenciales incorrectas.',
                'data' => [],
                'code' => 401 
            ], 401);
        }

        if ($user->suscripcion && $user->fecha_fin_suscripcion && $user->fecha_fin_suscripcion < now()) {
            $user->suscripcion = false;
            $user->fecha_fin_suscripcion = null;
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $nombresRoles = $user->getRoleNames(); 
        $user->makeHidden('roles');

        return response()->json([
            'error' => false,
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'usuario' => $user,
                'rol' => $nombresRoles,
                'token' => $token
            ],
            'code' => 200
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'error' => false,
            'message' => 'Sesión cerrada correctamente. ¡Vuelve pronto!',
            'data' => [],
            'code' => 200
        ], 200);
    }

    public function recargar(RecargarSaldoRequest $request)
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            $user->saldo += $request->cantidad;
            $user->save();

            Transaccion::create([
                'user_id' => $user->id,
                'tipo' => 'recarga',
                'cantidad' => $request->cantidad,
                'descripcion' => 'Recarga de ' . $request->cantidad . ' Klyx Coins.'
            ]);
        }); // Si algo falla dentro de esta función no se hace ninguna, es por seguridad.

        return response()->json([
            'error' => false,
            'message' => '¡Klyx Coins recargadas con éxito!',
            'data' => [
                'usuario' => $user->nombre,
                'saldo_actual' => $user->saldo,
                'vip' => $user->suscripcion
            ],
            'code' => 200
        ], 200);
    }

    public function comprarSuscripcion(Request $request)
    {
        $user = $request->user();
        $precioVip = 1000;

        if ($user->suscripcion && $user->fecha_fin_suscripcion > now()) {
            return response()->json([
                'error' => true,
                'message' => 'Ya eres un usuario VIP.',
                'data' => [],
                'code' => 400
            ], 400);
        }

        if ($user->saldo < $precioVip) {
            return response()->json([
                'error' => true,
                'message' => 'No tienes suficientes Klyx Coins. Necesitas ' . $precioVip . '.',
                'data' => [],
                'code' => 400
            ], 400);
        }

        DB::transaction(function () use ($user, $precioVip) {
            $user->saldo -= $precioVip;
            $user->suscripcion = true;
            $user->fecha_fin_suscripcion = now()->addDays(30);
            $user->save();

            Transaccion::create([
                'user_id' => $user->id,
                'tipo' => 'compra_vip',
                'cantidad' => -$precioVip,
                'descripcion' => 'Compra de suscripción VIP (30 días).'
            ]);
        }); // Si algo falla dentro de esta función no se hace ninguna, es por seguridad.

        
        return response()->json([
            'error' => false,
            'message' => '¡Felicidades! Ya eres VIP.',
            'data' => [
                'usuario' => $user->nombre,
                'saldo_restante' => $user->saldo,
                'vip' => $user->suscripcion,
                'fecha_fin_vip' => $user->fecha_fin_suscripcion
            ],
            'code' => 200
        ], 200);
    }

    public function historialTransacciones(Request $request)
    {
        $user = $request->user();

        $transacciones = Transaccion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'error' => false,
            'message' => 'Historial de transacciones obtenido.',
            'data' => $transacciones,
            'code' => 200
        ], 200);
    }

    public function historialCajas(Request $request)
    {
        $user = $request->user();

        // Buscamos el historial del usuario y traemos también los datos de la caja y el objeto
        $historial = HistorialApertura::with(['caja', 'objeto'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'error' => false,
            'message' => 'Historial de aperturas de cajas obtenido correctamente.',
            'data' => $historial,
            'code' => 200
        ], 200);
    }

    public function miInventario(Request $request)
    {
        $user = $request->user()->load('objetos');

        $user->objetos->makeHidden('pivot'); // Ocultamos los datos de la tabla intermedia (pivot)

        return response()->json([
            'error' => false,
            'message' => 'Inventario obtenido correctamente.',
            'data' => $user->objetos,
            'code' => 200
        ], 200);
    }
}