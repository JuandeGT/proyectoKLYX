<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Intercambio — representa una oferta de intercambio entre usuarios.
 *
 * Columnas principales:
 *   - emisor_id            → UUID del usuario que crea la oferta.
 *   - receptor_id          → UUID del destinatario (null = oferta pública).
 *   - objeto_ofrecido_id   → FK al objeto que el emisor pone sobre la mesa.
 *   - monedas_ofrecidas    → Klyx Coins que el emisor ofrece (0 si no ofrece monedas).
 *   - objeto_solicitado_id → FK al objeto que el emisor quiere recibir.
 *   - monedas_solicitadas  → Klyx Coins que el emisor quiere recibir (0 si no pide monedas).
 *   - estado               → Ciclo de vida: pendiente → aceptado / rechazado / cancelado.
 */
class Intercambio extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente (con create() o fill()).
     * El campo 'estado' se omite aquí a propósito: siempre empieza en 'pendiente'
     * gracias al valor por defecto definido en $attributes.
     */
    protected $fillable = [
        'emisor_id',
        'receptor_id',
        'objeto_ofrecido_id',
        'monedas_ofrecidas',
        'objeto_solicitado_id',
        'monedas_solicitadas',
        'estado',
    ];

    // AÑADIDO: valores por defecto a nivel de modelo (refuerzan los de la migración).
    // Así, si alguien crea un Intercambio sin pasar 'estado' o 'monedas_*', Eloquent
    // los rellena automáticamente sin depender solo de la BD.
    protected $attributes = [
        'estado'            => 'pendiente',
        'monedas_ofrecidas' => 0,
        'monedas_solicitadas' => 0,
    ];

    // AÑADIDO: casts para que Eloquent devuelva los tipos correctos en JSON.
    // Sin esto, 'monedas_ofrecidas' podría devolverse como string desde PostgreSQL.
    protected $casts = [
        'monedas_ofrecidas'  => 'integer',
        'monedas_solicitadas' => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    /** Usuario que creó la oferta. */
    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    /** Usuario destinatario (null si es una oferta pública). */
    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }

    /** Objeto (cuchillo) que el emisor ofrece. */
    public function objetoOfrecido()
    {
        return $this->belongsTo(Objeto::class, 'objeto_ofrecido_id');
    }

    /** Objeto (cuchillo) que el emisor solicita a cambio. */
    public function objetoSolicitado()
    {
        return $this->belongsTo(Objeto::class, 'objeto_solicitado_id');
    }
}
