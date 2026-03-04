<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecargarSaldoRequest extends FormRequest
{
    // 1. ¿Quién puede recargar el saldo? Cualquier usuario logueado (VIP o normal)
    public function authorize(): bool
    {
        return auth()->check(); 
    }

    // 2. Reglas: Tienen que enviar una "cantidad" numérica y mínimo 1 moneda
    public function rules(): array
    {
        return [
            'cantidad' => 'required|numeric|min:1',
        ];
    }
}