<?php

namespace App\Http\Controllers;

use App\Models\Objeto;
use App\Models\Transaccion;             
use App\Models\HistorialApertura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjetoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $objetos = Objeto::all();
            
            return response()->json([
                'error' => false,
                'message' => 'Lista de objetos obtenida correctamente.',
                'data' => $objetos,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al obtener los objetos.',
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
            $objeto = Objeto::create($request->all());

            return response()->json([
                'error' => false,
                'message' => 'Objeto creado correctamente.',
                'data' => $objeto,
                'code' => 201 
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al crear el objeto.',
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
            $objeto = Objeto::find($id);

            if (!$objeto) {
                return response()->json([
                    'error' => true,
                    'message' => 'El objeto solicitado no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Detalles del objeto obtenidos correctamente.',
                'data' => $objeto,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al buscar el objeto.',
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
            $objeto = Objeto::find($id);

            if (!$objeto) {
                return response()->json([
                    'error' => true,
                    'message' => 'El objeto que intentas actualizar no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            $objeto->update($request->all());

            return response()->json([
                'error' => false,
                'message' => 'Objeto actualizado correctamente.',
                'data' => $objeto,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al actualizar el objeto.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    // Permite a un usuario autenticado comprar un objeto directamente por su precio, sin necesidad de abrir una caja. Usado para la sección de Oferta Semanal.
    public function comprarObjetoDirecto(Request $request, $id)
    {
        try {
            // Buscamos el objeto que se quiere comprar
            $objeto = Objeto::find($id);

            if (!$objeto) {
                return response()->json([
                    'error'   => true,
                    'message' => 'El objeto que intentas comprar no existe.',
                    'data'    => null,
                    'code'    => 404
                ], 404);
            }

            $usuario = $request->user();

            if (!$objeto->en_oferta) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Este objeto no está disponible para compra directa.',
                    'data'    => null,
                    'code'    => 403
                ], 403);
            }

            // Comprobamos que el usuario tiene saldo suficiente para pagar el precio del objeto
            if ($usuario->saldo < $objeto->precio) {
                return response()->json([
                    'error'   => true,
                    'message' => 'No tienes saldo suficiente para comprar este objeto.',
                    'data'    => [
                        'saldo_actual'    => $usuario->saldo,
                        'precio_objeto'   => $objeto->precio,
                        'diferencia'      => $objeto->precio - $usuario->saldo
                    ],
                    'code'    => 400
                ], 400);
            }

            // Ejecutamos los tres pasos de forma atómica: si uno falla, ninguno se aplica
            DB::transaction(function () use ($usuario, $objeto) {
                // Paso 1: restar el precio del saldo del usuario
                $usuario->saldo -= $objeto->precio;
                $usuario->save();

                // Paso 2: registrar la compra en el historial de transacciones
                Transaccion::create([
                    'user_id'     => $usuario->id,
                    'tipo'        => 'compra_directa',
                    'cantidad'    => -$objeto->precio,   // negativo = gasto
                    'descripcion' => 'Compra directa del objeto: ' . $objeto->nombre
                ]);

                // Paso 3: añadir el objeto al inventario del usuario
                $usuario->objetos()->attach($objeto->id);
            });

            return response()->json([
                'error'   => false,
                'message' => '¡Objeto comprado con éxito!',
                'data'    => [
                    'objeto_comprado' => $objeto,
                    'saldo_restante'  => $usuario->saldo
                ],
                'code'    => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Ocurrió un error al intentar comprar el objeto.',
                'data'    => $e->getMessage(),
                'code'    => 500
            ], 500);
        }
    }

    // Historial público de los últimos 50 objetos obtenidos en cajas, sin mostrar usuario ni caja
    public function historialPublico()
    {
        try {
            $historial = HistorialApertura::with(['objeto:id,nombre,tipo,descripcion,imagen'])
                ->select('objeto_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($entrada) {
                    return [
                        'objeto'      => $entrada->objeto,
                        'obtenido_el' => $entrada->created_at,
                    ];
                });

            return response()->json([
                'error'   => false,
                'message' => 'Historial de objetos obtenido correctamente.',
                'data'    => $historial,
                'code'    => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Ocurrió un error al obtener el historial.',
                'data'    => $e->getMessage(),
                'code'    => 500
            ], 500);
        }
    }

    // Devuelve los objetos que están en la Oferta Semanal, ruta pública para que se vea sin estar logueado
    public function ofertaSemanal()
    {
        try {
            $objetos = Objeto::where('en_oferta', true)->get();

            return response()->json([
                'error'   => false,
                'message' => 'Oferta semanal obtenida correctamente.',
                'data'    => $objetos,
                'code'    => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Ocurrió un error al obtener la oferta semanal.',
                'data'    => $e->getMessage(),
                'code'    => 500
            ], 500);
        }
    }

    // Activa o desactiva un objeto en la Oferta Semanal (solo admin), máximo 3 activos a la vez
    public function toggleOferta($id)
    {
        try {
            $objeto = Objeto::find($id);

            if (!$objeto) {
                return response()->json([
                    'error'   => true,
                    'message' => 'El objeto no existe.',
                    'data'    => null,
                    'code'    => 404
                ], 404);
            }

            // Si vamos a activarlo comprobamos que no haya ya 3
            if (!$objeto->en_oferta) {
                $activos = Objeto::where('en_oferta', true)->count();
                if ($activos >= 3) {
                    return response()->json([
                        'error'   => true,
                        'message' => 'Ya hay 3 objetos en la Oferta Semanal. Desactiva uno antes de añadir otro.',
                        'data'    => null,
                        'code'    => 422
                    ], 422);
                }
            }

            $objeto->en_oferta = !$objeto->en_oferta;
            $objeto->save();

            $estado = $objeto->en_oferta ? 'activado' : 'desactivado';

            return response()->json([
                'error'   => false,
                'message' => "'{$objeto->nombre}' {$estado} en la Oferta Semanal.",
                'data'    => $objeto,
                'code'    => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Ocurrió un error al actualizar la oferta.',
                'data'    => $e->getMessage(),
                'code'    => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $objeto = Objeto::find($id);

            if (!$objeto) {
                return response()->json([
                    'error' => true,
                    'message' => 'El objeto que intentas borrar no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            $objeto->delete();

            return response()->json([
                'error' => false,
                'message' => 'Objeto eliminado correctamente.',
                'data' => null,
                'code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ocurrió un error al borrar el objeto.',
                'data' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}