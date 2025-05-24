<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    // Si la tabla se llama 'stocks' (plural de Stock), no es necesario $table.
    // Si la llamaste 'stock' en la migración, descomenta la siguiente línea:
    // protected $table = 'stock';

    protected $fillable = [
        'producto_id',
        'cantidad_disponible',
        'lote_numero',
        'fecha_caducidad',
        'ubicacion_almacen',
        'ultima_actualizacion_stock', // Laravel maneja created_at/updated_at, pero este es específico
    ];

    protected $casts = [
        'cantidad_disponible' => 'integer',
        'fecha_caducidad' => 'date',
        'ultima_actualizacion_stock' => 'datetime',
    ];

    /**
     * Get the producto that this stock entry belongs to.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}