<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VMetodosPagoDetalle extends Model
{
    protected $connection = 'mysql_editor';
    protected $table = 'v_metodos_pago_detalle'; // Nombre de tu vista SQL

    public $timestamps = false; // Generalmente false para vistas

    protected $primaryKey = 'metodo_pago_id'; // Alias del ID en tu vista

    public $incrementing = false; // Generalmente false para vistas

    protected $casts = [
        'comision_asociada_porcentaje' => 'decimal:2',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}