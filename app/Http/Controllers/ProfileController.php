<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user(); 
        
        // Limpiamos el rol
        $user->rol = $user->getRoleNames();
        $user->makeHidden('roles');

        return response()->json([
            'error' => false,
            'message' => 'Perfil obtenido correctamente.',
            'data' => $user,
            'code' => 200
        ], 200);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20'
        ]);

        // Actualizamos solo lo que el usuario haya enviado en la petición
        if ($request->has('nombre')) {
            $user->nombre = $request->nombre;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('direccion')) {
            $user->direccion = $request->direccion;
        } 
        if ($request->has('telefono')) {
            $user->telefono = $request->telefono;
        }
        
        // Si envía contraseña, la encriptamos antes de guardarla
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Limpiamos el rol
        $user->rol = $user->getRoleNames();
        $user->makeHidden('roles');

        return response()->json([
            'error' => false,
            'message' => 'Perfil actualizado correctamente.',
            'data' => $user,
            'code' => 200
        ], 200);
    }
}