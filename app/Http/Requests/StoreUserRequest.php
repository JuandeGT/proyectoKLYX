<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    // 1. ¿Quién tiene permiso para usar esto? Todo el mundo (true) porque es para registrarse
    public function authorize(): bool
    {
        return true; 
    }

    // 2. Las reglas estrictas que debe cumplir el usuario
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // Obligatorio, con @, y que no exista ya en la BD
            'password' => 'required|string|min:8', // Mínimo 8 caracteres por seguridad
        ];
    }
}