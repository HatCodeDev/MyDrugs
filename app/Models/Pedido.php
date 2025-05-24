<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'repartidor_id',
        'metodo_pago_id',
        'promocion_id',
        'direccion_entrega_cifrada',
        'punto_entrega_especial',
        'subtotal_pedido',
        'descuento_aplicado',
        'total_pedido',
        'estado_pedido',
        'fecha_pedido',
        'fecha_estimada_entrega',
        'notas_cliente',
        'codigo_seguimiento',
    ];

    protected $casts = [
        'subtotal_pedido' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'total_pedido' => 'decimal:2',
        'fecha_pedido' => 'datetime',
        'fecha_estimada_entrega' => 'datetime',
    ];

    /**
     * Get the user (cliente) that owns the pedido.
     */
    public function cliente(): BelongsTo // Renombrado de 'user' a 'cliente' para claridad en el contexto del pedido
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the repartidor assigned to the pedido.
     */
    public function repartidor(): BelongsTo
    {
        return $this->belongsTo(Repartidor::class);
    }

    /**
     * Get the metodo de pago for the pedido.
     */
    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPago::class);
    }

    /**
     * Get the promocion applied to the pedido.
     */
    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class);
    }

    /**
     * Get the detalles (items) for the pedido.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }
}