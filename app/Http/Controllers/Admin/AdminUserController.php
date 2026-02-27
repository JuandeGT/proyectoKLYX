<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        // 1. Traemos a todos los usuarios de la base de datos
        $usuarios = User::all();

        // 2. Devolvemos la respuesta estándar de tu app
        return response()->json([
            'error' => false,
            'message' => 'Lista de usuarios obtenida correctamente.',
            'data' => $usuarios,
            'code' => 200
        ], 200);
    }

    // Por ahora solo haremos el index, pero gracias a apiResource, 
    // podrías añadir aquí el método 'destroy' para borrar usuarios fácilmente.
}