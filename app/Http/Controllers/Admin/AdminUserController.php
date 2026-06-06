<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Objeto;
use App\Models\Intercambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\HistorialApertura;
use App\Models\Transaccion;

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

    public function estadisticas()
    {
        $totalUsuarios  = User::count();
        $usuariosVip    = User::where('suscripcion', true)->count();
        $usuariosConKC  = User::where('saldo', '>', 0)->count();

        $kcCirculacion  = User::sum('saldo');
        $kcMedia        = $totalUsuarios > 0 ? round(User::avg('saldo'), 0) : 0;

        $totalAperturas = HistorialApertura::count();

        $intercambios = Intercambio::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $transacciones = Transaccion::select(
                'tipo',
                DB::raw('count(*) as total'),
                DB::raw('sum(cantidad) as suma_kc')
            )
            ->groupBy('tipo')
            ->get();

        $totalObjetos   = Objeto::count();
        $objetosEnOferta = Objeto::where('en_oferta', true)->count();

        return response()->json([
            'error'   => false,
            'message' => 'Estadísticas globales obtenidas.',
            'data'    => [
                'usuarios' => [
                    'total'       => $totalUsuarios,
                    'vip_activos' => $usuariosVip,
                    'con_saldo'   => $usuariosConKC,
                ],
                'economia' => [
                    'kc_en_circulacion' => (int) $kcCirculacion,
                    'kc_media_usuario'  => (int) $kcMedia,
                ],
                'cajas' => [
                    'total_aperturas' => $totalAperturas,
                ],
                'intercambios' => [
                    'pendientes'  => (int) ($intercambios['pendiente']  ?? 0),
                    'aceptados'   => (int) ($intercambios['aceptado']   ?? 0),
                    'rechazados'  => (int) ($intercambios['rechazado']  ?? 0),
                    'cancelados'  => (int) ($intercambios['cancelado']  ?? 0),
                    'total'       => (int) array_sum($intercambios->toArray()),
                ],
                'transacciones' => $transacciones,
                'objetos' => [
                    'total'      => $totalObjetos,
                    'en_oferta'  => $objetosEnOferta,
                ],
            ],
            'code' => 200,
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