<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecargarSaldoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad' => 'required|numeric|min:1',
        ];
    }
}