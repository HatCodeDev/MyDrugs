<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bitacora extends Model
{
    use HasFactory;
    protected $table = 'v_bitacora';

    protected $fillable = [
        'user_id',
        'accion',
        'descripcion_detallada',
        'referencia_entidad',
        'referencia_id',
        'fecha_evento',
    ];

    protected $casts = [
        'descripcion_detallada' => 'array', 
        'fecha_evento' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent model of the log entry (polymorphic).
     * Para que esto funcione, las tablas referenciadas (ej. Producto, Pedido)
     * deben tener una relación definida como $this->morphMany(Bitacora::class, 'referencia');
     * y el campo 'referencia_entidad' almacenaría el nombre del modelo (App\Models\Producto).
     * Sin embargo, la definición actual con 'referencia_entidad' (string) y 'referencia_id' (int)
     * es más simple y no requiere relaciones polimórficas inversas estrictas.
     * Si quieres usar MorphTo, 'referencia_entidad' debería llamarse 'referencia_type'.
     */
    // public function referenciable(): MorphTo
    // {
    //    // Cambiar 'referencia_entidad' a 'referencia_type' en la migración
    //    return $this->morphTo(__FUNCTION__, 'referencia_type', 'referencia_id');
    // }
}