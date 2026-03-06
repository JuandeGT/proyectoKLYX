<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use \App\Models\HistorialApertura;
use App\Models\Transaccion;

class CajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $cajas = Caja::all();
            
            return response()->json([
                'error' => false,
                'message' => 'Lista de cajas obtenida correctamente.',
                'data' => $cajas,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al obtener las cajas.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $caja = Caja::create($request->all());

            return response()->json([
                'error' => false,
                'message' => 'Caja creada correctamente.',
                'data' => $caja,
                'code' => 201
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al crear la caja.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $caja = Caja::find($id);

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja solicitada no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Detalles de la caja obtenidos correctamente.',
                'data' => $caja,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al buscar la caja.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $caja = Caja::find($id);

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja que intentas actualizar no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            $caja->update($request->all());

            return response()->json([
                'error' => false,
                'message' => 'Caja actualizada correctamente.',
                'data' => $caja,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al actualizar la caja.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $caja = Caja::find($id);

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja que intentas borrar no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            $caja->delete();

            return response()->json([
                'error' => false,
                'message' => 'Caja eliminada correctamente.',
                'data' => null,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al borrar la caja.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    public function abrir(Request $request, $id)
    {
        try {
            $caja = Caja::find($id);

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja que intentas abrir no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            $usuario = $request->user();
            
            // Si alguien intenta abrir una caja sin haber iniciado sesión
            if (!$usuario) {
                return response()->json([
                    'error' => true,
                    'message' => 'No autorizado. Debes iniciar sesión para abrir cajas.',
                    'data' => null,
                    'code' => 401
                ], 401);
            }

            // Si alguien intenta abrir una caja vip sin serlo
            if ($caja->vip && !$usuario->suscripcion) {
                return response()->json([
                    'error' => true,
                    'message' => 'Esta caja es exclusiva para usuarios VIP.',
                    'data' => null,
                    'code' => 403 
                ], 403);
            }

            $premio = $caja->objetos()->inRandomOrder()->first();

            if (!$premio) {
                return response()->json([
                    'error' => true,
                    'message' => 'No hay objetos disponibles en la tienda.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            $premio->probabilidad = (int) $premio->pivot->probabilidad;
            
            $premio->makeHidden('pivot');

            if ($usuario->saldo < $caja->precio) {
                return response()->json([
                    'error' => true,
                    'message' => 'No tienes saldo suficiente para abrir esta caja.',
                    'data' => null,
                    'code' => 400
                ], 400);
            }

            $usuario->saldo -= $caja->precio;
            $usuario->save();

            // Registramos la transacción
            Transaccion::create([
                'user_id' => $usuario->id,
                'tipo' => 'apertura_caja',
                'cantidad' => -$caja->precio,
                'descripcion' => 'Apertura de caja: ' . $caja->nombre
            ]);

            // Creamos el historial
            HistorialApertura::create([
                'user_id' => $usuario->id,
                'caja_id' => $caja->id,
                'objeto_id' => $premio->id
            ]);

            // Añadimos el premio al inventario del usuario
            $usuario->objetos()->attach($premio->id);

            return response()->json([
                'error' => false,
                'message' => '¡Caja abierta con éxito!',
                'data' => [
                    'caja_abierta' => $caja->nombre,
                    'premio_obtenido' => $premio,
                    'saldo_restante' => $usuario->saldo
                ],
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al intentar abrir la caja.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    public function añadirObjeto(Request $request, $id)
    {
        try {
            $caja = Caja::find($id);

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja no existe.',
                    'code' => 404
                ], 404);
            }
            
            // "syncWithoutDetaching" añade el objeto si no está, y si está, solo actualiza la probabilidad
            $caja->objetos()->syncWithoutDetaching([
                $request->objeto_id => ['probabilidad' => $request->probabilidad]
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Objeto vinculado a la caja con éxito',
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al vincular el objeto a la caja.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    public function quitarObjeto($id, $objeto_id)
    {
        try {
            $caja = Caja::find($id);

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja no existe.',
                    'code' => 404
                ], 404);
            }

            if (!$caja->objetos()->where('objeto_id', $objeto_id)->exists()) {
                return response()->json([
                    'error' => true,
                    'message' => 'El objeto no se encuentra en esta caja.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            $caja->objetos()->detach($objeto_id);

            return response()->json([
                'error' => false,
                'message' => 'Objeto retirado de la caja correctamente.',
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al quitar el objeto.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}