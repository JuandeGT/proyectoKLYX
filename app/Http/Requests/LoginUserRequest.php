<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    // 1. ¿Quién puede intentar hacer login? Todo el mundo (true)
    public function authorize(): bool
    {
        return true;
    }

    // 2. Reglas: Solo exigimos que envíe un email válido y una contraseña
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}