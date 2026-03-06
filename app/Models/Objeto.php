<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objeto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'peso',
        'longitud',
        'descripcion',
        'precio',
        'imagen'
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
        ];
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'inventarios');
    }

    // Un objeto puede estar dentro de varias cajas
    public function cajas()
    {
        return $this->belongsToMany(Caja::class)->withPivot('probabilidad')->withTimestamps();
    }
}