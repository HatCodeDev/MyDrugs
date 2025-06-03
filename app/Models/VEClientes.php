<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VEClientes extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 've_estadisticas_clientes'; // Nombre exacto de tu vista SQL

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     * Para la vista 've_estadisticas_clientes', 'cliente_id' es la clave primaria.
     *
     * @var string
     */
    protected $primaryKey = 'cliente_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int'; // Asumiendo que cliente_id es un entero.

    /**
     * The attributes that should be cast.
     * Esto ayuda a Filament y Laravel a tratar los campos correctamente.
     *
     * @var array
     */
    protected $casts = [
        'total_pedidos_realizados' => 'integer',
        'gasto_total_cliente' => 'decimal:2',
        'fecha_ultimo_pedido' => 'datetime', // O 'date' si no tiene hora
        'fecha_registro_cliente' => 'datetime', // O 'date' si no tiene hora
    ];

    // No se necesitan $fillable o $guarded para vistas de solo lectura.
}
