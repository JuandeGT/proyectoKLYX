<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use \App\Models\HistorialApertura;
use \App\Models\Transaccion;

class AdminUserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->paginate(10);

        // Limpiamos los roles SIN romper la paginación
        $usuarios->getCollection()->transform(function ($user) {
            $user->rol = $user->getRoleNames();
            $user->makeHidden('roles');
            return $user;
        });

        return response()->json([
            'error' => false,
            'message' => 'Lista de usuarios obtenida correctamente.',
            'data' => $usuarios,
            'code' => 200
        ], 200);
    }

    public function show($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Usuario no encontrado.', 'code' => 404], 404);
        }

        // Limpiamos el rol igual que en el index
        $user->rol = $user->getRoleNames();
        $user->makeHidden('roles');

        return response()->json([
            'error' => false,
            'message' => 'Detalle del usuario obtenido.',
            'data' => $user,
            'code' => 200
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Usuario no encontrado.', 'code' => 404], 404);
        }

        if ($request->has('nombre')) $user->nombre = $request->nombre;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('saldo')) $user->saldo = $request->saldo;
        if ($request->has('suscripcion')) {
            $user->suscripcion = $request->suscripcion;
            
            if ($user->suscripcion === true) {
                // Al vip le ponemos 30 días de caducidad (o lo que pase el admin por parámetro)
                $user->fecha_fin_suscripcion = $request->input('fecha_fin_suscripcion', now()->addDays(30));
            } else {
                // Si le quitamos el vip, limpiamos su fecha de caducidad
                $user->fecha_fin_suscripcion = null;
            }
        } elseif ($request->has('fecha_fin_suscripcion')) {
            $user->fecha_fin_suscripcion = $request->fecha_fin_suscripcion;
        }
        if ($request->has('rol')) {
            $user->syncRoles([$request->rol]); 
        }

        $user->save();

        return response()->json([
            'error' => false,
            'message' => 'Usuario actualizado correctamente.',
            'data' => $user,
            'code' => 200
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Usuario no encontrado.', 'code' => 404], 404);
        }

        // Evitamos que el Admin se borre a sí mismo por accidente
        if ($user->id === $request->user()->id) {
            return response()->json(['error' => true, 'message' => 'No puedes borrarte a ti mismo.', 'code' => 400], 400);
        }

        $user->delete();

        return response()->json([
            'error' => false,
            'message' => 'Usuario eliminado de la plataforma.',
            'data' => [],
            'code' => 200
        ], 200);
    }

    public function historialCajas($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'error' => true, 
                'message' => 'Usuario no encontrado.', 
                'data' => null, 
                'code' => 404
            ], 404);
        }

        // Buscamos el historial y traemos la información de la caja y el objeto
        $historial = HistorialApertura::with(['caja', 'objeto'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'error' => false, 
            'message' => 'Historial de cajas del usuario obtenido correctamente.',
            'data' => $historial, 
            'code' => 200
        ], 200);
    }

    public function historialTransacciones($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'error' => true, 
                'message' => 'Usuario no encontrado.', 
                'data' => null, 
                'code' => 404
            ], 404);
        }

        $transacciones = Transaccion::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'error' => false, 
            'message' => 'Historial de transacciones del usuario obtenido correctamente.',
            'data' => $transacciones, 
            'code' => 200
        ], 200);
    }

}