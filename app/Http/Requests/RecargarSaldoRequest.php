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
            'cantidad' => 'required|integer|min:1|max:100000', // KC enteras, máx 100000 por recarga (1000€)
        ];
    }
}