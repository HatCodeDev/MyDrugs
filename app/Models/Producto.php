<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio_unitario',
        'unidad_medida',
        'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Get the categoria that owns the producto.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Get the imagenes for the producto.
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenProducto::class);
    }

    /**
     * Get the calificaciones for the producto.
     */
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    /**
     * Get the stock information for the producto.
     * Assuming one primary stock entry per product for simplicity,
     * or use HasMany if multiple stock batches/locations are common.
     */
    public function stock(): HasMany // O HasOne si es una entrada única de stock por producto
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get the detallesPedido associated with the producto.
     */
    public function detallesPedido(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    /**
     * Get the promociones aplicables a este producto.
     */
    public function promociones(): HasMany
    {
        return $this->hasMany(Promocion::class, 'aplicable_a_producto_id');
    }
}