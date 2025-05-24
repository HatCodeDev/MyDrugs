<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenProducto extends Model
{
    use HasFactory;

    protected $table = 'imagenes_producto'; // Especificar nombre de tabla si no sigue convención exacta (ImagenProductos)

    protected $fillable = [
        'producto_id',
        'url_imagen',
        'alt_text',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    /**
     * Get the producto that owns the imagen.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}