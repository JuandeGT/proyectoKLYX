<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaccion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'tipo',
        'cantidad',
        'descripcion'
    ];

    // Relación: Una transacción pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}