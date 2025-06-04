<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio_unitario',
        'unidad_medida',
        'activo',
    ];

    protected $casts = [
        'categoria_id' => 'integer',
        'precio_unitario' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Define la relación con el modelo Categoria.
     * Un producto pertenece a una categoría.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
        
    }

    /**
     * Define la relación con el modelo ImagenProducto.
     * Un producto puede tener muchas imágenes.
     */
    public function imagenes(): HasMany
    {
        // Asegúrate de que el modelo ImagenProducto exista y esté correctamente nombrado.
        // El segundo argumento es la clave foránea en la tabla 'imagenes_producto'.
        return $this->hasMany(ImagenProducto::class, 'producto_id');
    }

    /**
     * Define la relación con el modelo Calificacion.
     * Un producto puede tener muchas calificaciones.
     */
    public function calificaciones(): HasMany
    {
        // Asegúrate de que el modelo Calificacion exista y esté correctamente nombrado.
        // El segundo argumento es la clave foránea en la tabla 'calificaciones'.
        return $this->hasMany(Calificacion::class, 'producto_id');
    }

    // Aquí puedes añadir otras relaciones si son necesarias, por ejemplo:
    //
    // public function stockItems(): HasMany
    // {
    //     return $this->hasMany(Stock::class, 'producto_id');
    // }
    //
    // public function detallesPedido(): HasMany
    // {
    //     return $this->hasMany(DetallePedido::class, 'producto_id');
    // }
}