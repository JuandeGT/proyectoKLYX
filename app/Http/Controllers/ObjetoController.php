<?php

namespace App\Http\Controllers;

use App\Models\Objeto;
use Illuminate\Http\Request;

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
            // 1. La herramienta create(): Coge todo lo que nos envíen ($request->all()) 
            // y lo guarda como una nueva fila en la base de datos.
            $objeto = Objeto::create($request->all());

            // 2. Si va bien, devolvemos el JSON con código 201 (Creado)
            return response()->json([
                'error' => false,
                'message' => 'Objeto creado correctamente.',
                'data' => $objeto,
                'code' => 201 
            ], 201);

        } catch (\Exception $e) {
            // 3. Si falla algo (ej: falta un campo obligatorio), capturamos el error
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
            // Buscamos el objeto manualmente por su ID
            $objeto = Objeto::find($id);

            // Si el objeto no existe en la base de datos
            if (!$objeto) {
                return response()->json([
                    'error' => true,
                    'message' => 'El objeto solicitado no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            // Si lo encuentra correctamente
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
            // 1. Buscamos si el objeto existe en la base de datos
            $objeto = Objeto::find($id);

            // 2. Si no existe, devolvemos un error 404
            if (!$objeto) {
                return response()->json([
                    'error' => true,
                    'message' => 'El objeto que intentas actualizar no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            // 3. Si existe, lo actualizamos con los datos nuevos
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // 1. Buscamos el objeto
            $objeto = Objeto::find($id);

            // 2. Comprobamos si existe
            if (!$objeto) {
                return response()->json([
                    'error' => true,
                    'message' => 'El objeto que intentas borrar no existe.',
                    'data' => null,
                    'code' => 404
                ], 404);
            }

            // 3. Herramienta para eliminar la fila de la base de datos
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