<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VProductosDetalle extends Model
{
    protected $table = 'v_productos_detalle'; // Nombre de tu vista SQL

    public $timestamps = false;

    protected $primaryKey = 'producto_id'; // Alias del ID en tu vista

    public $incrementing = false;

    protected $casts = [
        'categoria_id' => 'integer',
        'precio_unitario' => 'decimal:2',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}