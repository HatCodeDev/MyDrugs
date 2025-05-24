<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repartidor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre_alias',
        'vehiculo_descripcion',
        'zona_operativa_preferida',
        'disponible',
        'calificacion_promedio',
        'numero_contacto_cifrado',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'calificacion_promedio' => 'decimal:2',
    ];

    /**
     * Get the user account associated with the repartidor (if any).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pedidos assigned to this repartidor.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}