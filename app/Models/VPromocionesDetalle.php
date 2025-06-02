<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VPromocionesDetalle extends Model
{
    protected $table = 'v_promociones_detalle'; // Nombre de tu vista SQL

    public $timestamps = false;

    protected $primaryKey = 'promocion_id'; // Alias del ID en tu vista

    public $incrementing = false;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}