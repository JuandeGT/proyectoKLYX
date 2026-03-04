<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    /** @use HasFactory<\Database\Factories\CajaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'precio',
        'imagen'
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
        ];
    }
    
    protected $with = ['objetos'];

    // Una caja tiene muchos objetos posibles dentro
    public function objetos()
    {
        // withPivot nos permite leer la columna 'probabilidad' que acabamos de crear
        return $this->belongsToMany(Objeto::class)->withPivot('probabilidad')->withTimestamps();
    }
}