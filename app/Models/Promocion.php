<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'codigo_promocion',
        'descripcion',
        'tipo_descuento',
        'valor_descuento',
        'fecha_inicio',
        'fecha_fin',
        'usos_maximos_global',
        'usos_maximos_por_usuario',
        'activo',
        'aplicable_a_categoria_id',
        'aplicable_a_producto_id',
        'monto_minimo_pedido',
    ];

    protected $casts = [
        'valor_descuento' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'usos_maximos_global' => 'integer',
        'usos_maximos_por_usuario' => 'integer',
        'activo' => 'boolean',
        'aplicable_a_categoria_id' => 'integer',
        'aplicable_a_producto_id' => 'integer',
        'monto_minimo_pedido' => 'decimal:2',
    ];

    public function categoriaAplicable(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'aplicable_a_categoria_id');
    }

    public function productoAplicable(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'aplicable_a_producto_id');
    }
}