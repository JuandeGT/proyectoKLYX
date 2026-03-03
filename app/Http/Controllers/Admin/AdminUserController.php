<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        // 1. Traemos a los usuarios y sus roles brutos de la base de datos
        $usuarios = User::with('roles')->paginate(10);

        // 2. Limpiamos los roles SIN romper la paginación
        $usuarios->getCollection()->transform(function ($user) {
            $user->rol = $user->getRoleNames();
            $user->makeHidden('roles');
            return $user;
        });

        // 3. Devolvemos la respuesta limpia
        return response()->json([
            'error' => false,
            'message' => 'Lista de usuarios obtenida correctamente.',
            'data' => $usuarios,
            'code' => 200
        ], 200);
    }

    // Función para ver los datos de UN solo usuario específico
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

    // Función para ACTUALIZAR los datos de un usuario específico
    public function update(\Illuminate\Http\Request $request, $id)
    {
        // 1. Buscamos al usuario por su UUID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Usuario no encontrado.', 'code' => 404], 404);
        }

        // 2. Actualizamos los datos que el Admin haya enviado en la petición
        // (Por ejemplo, podemos cambiarle el nombre, las monedas o si es VIP)
        if ($request->has('nombre')) $user->nombre = $request->nombre;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('monedas')) $user->monedas = $request->monedas;
        // Gestión inteligente de la suscripción VIP
        if ($request->has('suscripcion')) {
            $user->suscripcion = $request->suscripcion;
            
            if ($user->suscripcion === true) {
                // Si le damos el VIP, le ponemos 30 días desde hoy (a menos que el Admin envíe una fecha específica)
                $user->fecha_fin_suscripcion = $request->input('fecha_fin_suscripcion', now()->addDays(30));
            } else {
                // Si le quitamos el VIP, limpiamos su fecha de caducidad
                $user->fecha_fin_suscripcion = null;
            }
        } elseif ($request->has('fecha_fin_suscripcion')) {
            // Por si el Admin solo quiere modificar la fecha de alguien que ya es VIP
            $user->fecha_fin_suscripcion = $request->fecha_fin_suscripcion;
        }
        // Si el Admin envía un campo "rol" (ej: "Admin" o "Usuario"), se lo cambiamos
        if ($request->has('rol')) {
            $user->syncRoles([$request->rol]); 
        }

        $user->save(); // Guardamos los cambios

        return response()->json([
            'error' => false,
            'message' => 'Usuario actualizado correctamente.',
            'data' => $user,
            'code' => 200
        ], 200);
    }

    // Función para BORRAR a un usuario
    public function destroy(Request $request, $id)
    {
        // 1. Buscamos al usuario
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Usuario no encontrado.', 'code' => 404], 404);
        }

        // 2. Evitamos que el Admin se borre a sí mismo por accidente
        if ($user->id === $request->user()->id()) {
            return response()->json(['error' => true, 'message' => 'No puedes borrarte a ti mismo.', 'code' => 400], 400);
        }

        // 3. Lo fulminamos de la base de datos
        $user->delete();

        return response()->json([
            'error' => false,
            'message' => 'Usuario eliminado de la plataforma.',
            'data' => [],
            'code' => 200
        ], 200);
    }

}