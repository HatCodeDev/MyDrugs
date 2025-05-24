<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'user_id',
        'puntuacion',
        'comentario',
        'fecha_calificacion',
    ];

    protected $casts = [
        'puntuacion' => 'integer',
        'fecha_calificacion' => 'datetime',
    ];

    /**
     * Get the producto that was calificated.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Get the user that made the calificacion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}