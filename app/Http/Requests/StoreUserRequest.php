<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // Obligatorio con @ y que no exista ya en la base de datos
            'password' => 'required|string|min:8', // Mínimo 8 caracteres por seguridad
        ];
    }
}