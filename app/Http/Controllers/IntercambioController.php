<?php

namespace App\Http\Controllers;

use App\Models\Intercambio;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntercambioController extends Controller
{
    // Solo para admin: ver todos los intercambios de la plataforma, se puede filtrar con ?estado=pendiente
    public function todosIntercambios(Request $request)
    {
        $query = Intercambio::with([
            'emisor:id,nombre',
            'receptor:id,nombre',
            'objetoOfrecido:id,nombre,imagen',
            'objetoSolicitado:id,nombre,imagen',
        ]);

        // Filtro opcional por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $intercambios = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'error'   => false,
            'message' => 'Todos los intercambios obtenidos.',
            'data'    => $intercambios,
            'code'    => 200,
        ], 200);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $intercambios = Intercambio::with(['emisor:id,nombre', 'objetoOfrecido', 'objetoSolicitado'])
            ->where('estado', 'pendiente')
            ->where('emisor_id', '!=', $userId)
            ->where(function ($q) use ($userId) {
                // Solo las públicas O las dirigidas a este usuario
                $q->whereNull('receptor_id')
                  ->orWhere('receptor_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'error'   => false,
            'message' => 'Intercambios disponibles obtenidos.',
            'data'    => $intercambios,
            'code'    => 200,
        ], 200);
    }

    public function misOfertas(Request $request)
    {
        $userId = $request->user()->id;

        $enviadas = Intercambio::with(['receptor:id,nombre', 'objetoOfrecido', 'objetoSolicitado'])
            ->where('emisor_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'error'   => false,
            'message' => 'Mis ofertas obtenidas.',
            'data'    => $enviadas,
            'code'    => 200,
        ], 200);
    }

    public function store(Request $request)
    {
        // 'nullable' en monedas para que no falle la validación si no se envían en el body
        $request->validate([
            'objeto_ofrecido_id'   => 'nullable|exists:objetos,id',
            'monedas_ofrecidas'    => 'nullable|integer|min:0',
            'objeto_solicitado_id' => 'nullable|exists:objetos,id',
            'monedas_solicitadas'  => 'nullable|integer|min:0',
            'receptor_id'          => 'nullable|exists:users,id|uuid',
        ]);

        $user = $request->user();

        if (!$request->objeto_ofrecido_id && ($request->monedas_ofrecidas ?? 0) === 0) {
            return response()->json([
                'error'   => true,
                'message' => 'Debes ofrecer al menos un objeto o Klyx Coins.',
                'code'    => 422,
            ], 422);
        }

        if (!$request->objeto_solicitado_id && ($request->monedas_solicitadas ?? 0) === 0) {
            return response()->json([
                'error'   => true,
                'message' => 'Debes solicitar al menos un objeto o Klyx Coins.',
                'code'    => 422,
            ], 422);
        }

        // Comprobamos que el objeto que ofrece lo tiene en el inventario
        if ($request->objeto_ofrecido_id) {
            $tiene = $user->objetos()->where('objetos.id', $request->objeto_ofrecido_id)->exists();
            if (!$tiene) {
                return response()->json([
                    'error'   => true,
                    'message' => 'No tienes ese objeto en tu inventario.',
                    'code'    => 403,
                ], 403);
            }
        }

        // Y que tiene las monedas que ofrece
        if (($request->monedas_ofrecidas ?? 0) > 0 && $user->saldo < $request->monedas_ofrecidas) {
            return response()->json([
                'error'   => true,
                'message' => 'No tienes suficientes Klyx Coins para esta oferta.',
                'code'    => 403,
            ], 403);
        }

        $intercambio = Intercambio::create([
            'emisor_id'            => $user->id,
            'receptor_id'          => $request->receptor_id,           // null = pública
            'objeto_ofrecido_id'   => $request->objeto_ofrecido_id,
            'monedas_ofrecidas'    => $request->monedas_ofrecidas ?? 0,
            'objeto_solicitado_id' => $request->objeto_solicitado_id,
            'monedas_solicitadas'  => $request->monedas_solicitadas ?? 0,
            // 'estado' empieza en 'pendiente' por defecto en la migración
        ]);

        // Cargamos las relaciones para devolver datos completos al frontend
        $intercambio->load(['emisor:id,nombre', 'objetoOfrecido', 'objetoSolicitado']);

        return response()->json([
            'error'   => false,
            'message' => '¡Oferta publicada correctamente!',
            'data'    => $intercambio,
            'code'    => 201,
        ], 201);
    }

    public function aceptar(Request $request, $id)
    {
        $aceptante = $request->user();

        $intercambio = Intercambio::with(['emisor', 'objetoOfrecido', 'objetoSolicitado'])
            ->where('id', $id)
            ->where('estado', 'pendiente')
            ->where('emisor_id', '!=', $aceptante->id)       // No puedes aceptar tu propia oferta
            ->where(function ($q) use ($aceptante) {
                $q->whereNull('receptor_id')                  // Pública: cualquiera puede
                  ->orWhere('receptor_id', $aceptante->id);   // Directa: solo el destinatario
            })
            ->first();

        if (!$intercambio) {
            return response()->json([
                'error'   => true,
                'message' => 'El intercambio no existe, ya no está disponible o no te pertenece.',
                'code'    => 404,
            ], 404);
        }

        $emisor = $intercambio->emisor;

        // Comprobamos antes de ejecutar que ambas partes siguen teniendo lo prometido

        if ($intercambio->objeto_ofrecido_id) {
            if (!$emisor->objetos()->where('objetos.id', $intercambio->objeto_ofrecido_id)->exists()) {
                return response()->json([
                    'error'   => true,
                    'message' => 'El emisor ya no tiene el objeto ofrecido. La oferta no es válida.',
                    'code'    => 409,
                ], 409);
            }
        }

        if ($intercambio->monedas_ofrecidas > 0 && $emisor->saldo < $intercambio->monedas_ofrecidas) {
            return response()->json([
                'error'   => true,
                'message' => 'El emisor ya no tiene suficientes Klyx Coins.',
                'code'    => 409,
            ], 409);
        }

        if ($intercambio->objeto_solicitado_id) {
            if (!$aceptante->objetos()->where('objetos.id', $intercambio->objeto_solicitado_id)->exists()) {
                return response()->json([
                    'error'   => true,
                    'message' => 'No tienes el objeto que se solicita para este intercambio.',
                    'code'    => 403,
                ], 403);
            }
        }

        if ($intercambio->monedas_solicitadas > 0 && $aceptante->saldo < $intercambio->monedas_solicitadas) {
            return response()->json([
                'error'   => true,
                'message' => 'No tienes suficientes Klyx Coins para aceptar este intercambio.',
                'code'    => 403,
            ], 403);
        }

        try {
            DB::transaction(function () use ($intercambio, $emisor, $aceptante) {

                // lockForUpdate bloquea la fila para evitar que dos usuarios acepten la misma oferta a la vez
                $intercambioLocked = Intercambio::lockForUpdate()->find($intercambio->id);

                if ($intercambioLocked->estado !== 'pendiente') {
                    throw new \Exception('Este intercambio ya fue aceptado o cancelado por otro usuario.');
                }

                // limit(1)->delete() en vez de detach() para no borrar todas las copias si el usuario tiene duplicados
                if ($intercambio->objeto_ofrecido_id) {
                    DB::table('inventarios')
                        ->where('user_id', $emisor->id)
                        ->where('objeto_id', $intercambio->objeto_ofrecido_id)
                        ->limit(1)
                        ->delete();
                    $aceptante->objetos()->attach($intercambio->objeto_ofrecido_id);
                }
                if ($intercambio->objeto_solicitado_id) {
                    DB::table('inventarios')
                        ->where('user_id', $aceptante->id)
                        ->where('objeto_id', $intercambio->objeto_solicitado_id)
                        ->limit(1)
                        ->delete();
                    $emisor->objetos()->attach($intercambio->objeto_solicitado_id);
                }

                if ($intercambio->monedas_ofrecidas > 0) {
                    $emisor->saldo    -= $intercambio->monedas_ofrecidas;
                    $aceptante->saldo += $intercambio->monedas_ofrecidas;
                }
                if ($intercambio->monedas_solicitadas > 0) {
                    $aceptante->saldo -= $intercambio->monedas_solicitadas;
                    $emisor->saldo    += $intercambio->monedas_solicitadas;
                }

                $emisor->save();
                $aceptante->save();

                Transaccion::create([
                    'user_id'     => $emisor->id,
                    'tipo'        => 'intercambio',
                    'cantidad'    => $intercambio->monedas_solicitadas - $intercambio->monedas_ofrecidas,
                    'descripcion' => 'Intercambio #' . $intercambio->id . ' completado con ' . $aceptante->nombre . '.',
                ]);
                Transaccion::create([
                    'user_id'     => $aceptante->id,
                    'tipo'        => 'intercambio',
                    'cantidad'    => $intercambio->monedas_ofrecidas - $intercambio->monedas_solicitadas,
                    'descripcion' => 'Intercambio #' . $intercambio->id . ' aceptado de ' . $emisor->nombre . '.',
                ]);

                $intercambioLocked->estado      = 'aceptado';
                $intercambioLocked->receptor_id = $aceptante->id; // Guardamos quién aceptó por si era pública
                $intercambioLocked->save();
            });

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
                'code'    => 409,
            ], 409);
        }

        return response()->json([
            'error'   => false,
            'message' => '¡Intercambio completado con éxito!',
            'data'    => [],
            'code'    => 200,
        ], 200);
    }

    public function rechazar(Request $request, $id)
    {
        $user = $request->user();

        // Buscamos la oferta dirigida a este usuario y que siga pendiente
        $intercambio = Intercambio::where('id', $id)
            ->where('receptor_id', $user->id)   // Solo el receptor designado puede rechazar
            ->where('estado', 'pendiente')
            ->first();

        if (!$intercambio) {
            return response()->json([
                'error'   => true,
                'message' => 'Oferta no encontrada o no puedes rechazarla.',
                'code'    => 404,
            ], 404);
        }

        $intercambio->estado = 'rechazado';
        $intercambio->save();

        return response()->json([
            'error'   => false,
            'message' => 'Oferta rechazada.',
            'data'    => [],
            'code'    => 200,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Buscamos la oferta enviada por este usuario que siga pendiente
        $intercambio = Intercambio::where('id', $id)
            ->where('emisor_id', $user->id)  // Solo el emisor puede cancelar
            ->where('estado', 'pendiente')
            ->first();

        if (!$intercambio) {
            return response()->json([
                'error'   => true,
                'message' => 'Oferta no encontrada o no puedes cancelarla.',
                'code'    => 404,
            ], 404);
        }

        $intercambio->estado = 'cancelado';
        $intercambio->save();

        return response()->json([
            'error'   => false,
            'message' => 'Oferta cancelada.',
            'data'    => [],
            'code'    => 200,
        ], 200);
    }
}
