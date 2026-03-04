<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialApertura extends Model
{
    use HasFactory;

    // Protegemos los campos que se pueden rellenar
    protected $fillable = [
        'user_id',
        'caja_id',
        'objeto_id'
    ];

    /**
     * Relaciones inversas para saber a quién pertenece cada ID
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function objeto()
    {
        return $this->belongsTo(Objeto::class);
    }
}