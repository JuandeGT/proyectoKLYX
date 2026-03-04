<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use \App\Models\HistorialApertura;

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
            // Si la base de datos falla o pasa algo raro, capturamos el error aquí
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al obtener las cajas.',
                'data' => $e->getMessage(), // Opcional: muestra el error real de Laravel
                'code' => 500 // 500 es el código de "Error interno del servidor"
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Creamos la caja con los datos que nos envíen por la petición (Thunder Client/React)
            $caja = Caja::create($request->all());

            return response()->json([
                'error' => false,
                'message' => 'Caja creada correctamente.',
                'data' => $caja,
                'code' => 201 // 201 = Created
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
    public function show($id) // OJO: Hemos cambiado "Caja $caja" por "$id"
    {
        try {
            // Buscamos la caja manualmente
            $caja = Caja::find($id);

            // Si la caja no existe (ej: buscan el ID 999)
            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja solicitada no existe.',
                    'data' => null,
                    'code' => 404 // 404 es el código de "No encontrado"
                ], 404);
            }

            // Si todo va bien y la encuentra
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
    public function update(Request $request, $id) // Cambiamos Caja $caja por $id
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

            // Si existe, la actualizamos con los datos nuevos
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
    public function destroy($id) // Cambiamos Caja $caja por $id
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

            // Si existe, la eliminamos de la base de datos
            $caja->delete();

            return response()->json([
                'error' => false,
                'message' => 'Caja eliminada correctamente.',
                'data' => null, // Como la hemos borrado, no devolvemos datos
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

    // Añadimos Request $request para poder leer el token del usuario logueado
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

            // VERSIÓN DEFINITIVA: Cogemos el usuario real que nos manda Juande a través del Token
            $usuario = $request->user();
            
            // Si alguien intenta abrir una caja sin haber iniciado sesión
            if (!$usuario) {
                return response()->json([
                    'error' => true,
                    'message' => 'No autorizado. Debes iniciar sesión para abrir cajas.',
                    'data' => null,
                    'code' => 401 // 401 = Unauthorized
                ], 401);
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

            // Extraemos la probabilidad, la pasamos a número entero (sin decimales) y la guardamos en el premio
            $premio->probabilidad = (int) $premio->pivot->probabilidad;
            
            // Ocultamos el bloque 'pivot' feo
            $premio->makeHidden('pivot');

            // Cobrar la caja
            if ($usuario->saldo < $caja->precio) {
                return response()->json([
                    'error' => true,
                    'message' => 'No tienes saldo suficiente para abrir esta caja.',
                    'data' => null,
                    'code' => 400
                ], 400);
            }

            // Le restamos el dinero y guardamos
            $usuario->saldo -= $caja->precio;
            $usuario->save();

            // Creamos el historial
            HistorialApertura::create([
                'user_id' => $usuario->id,
                'caja_id' => $caja->id,
                'objeto_id' => $premio->id
            ]);

            // Añadimos el premio al inventario del jugador
            $usuario->objetos()->attach($premio->id);

            // Devolvemos el premio al Frontend
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

    // Añadir (o actualizar probabilidad) de un objeto en una caja
    public function añadirObjeto(Request $request, $id)
    {
        try {
            // Usamos find en vez de findOrFail para controlarlo nosotros
            $caja = Caja::find($id);

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'message' => 'La caja no existe.',
                    'code' => 404
                ], 404);
            }
            
            // syncWithoutDetaching añade el objeto si no está, y si está, solo actualiza la probabilidad
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

    // Quitar un objeto de una caja (pero el objeto sigue existiendo en la tienda)
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