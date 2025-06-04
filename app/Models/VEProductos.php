<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VEProductos extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $connection = 'mysql_editor';
    protected $table = 've_estadisticas_productos'; // Nombre exacto de tu vista SQL

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     * Para la vista 've_estadisticas_productos', 'producto_id' es la clave primaria.
     *
     * @var string
     */
    protected $primaryKey = 'producto_id';

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
    protected $keyType = 'int'; // Asumiendo que producto_id es un entero.

    // No se necesitan $fillable o $guarded para vistas de solo lectura.
    // No se necesitan $casts aquí si el formato se maneja en el Resource de Filament,
    // pero podrías añadirlos para 'producto_activo' (boolean) o 'precio_unitario' (decimal), etc.,
    // si quisieras un comportamiento más robusto a nivel de modelo.
    // protected $casts = [
    //     'producto_activo' => 'boolean',
    //     'precio_unitario' => 'decimal:2',
    //     'stock_disponible' => 'integer',
    //     'promedio_calificacion' => 'decimal:2',
    //     'total_ventas_generadas' => 'decimal:2',
    //     'numero_veces_en_pedidos' => 'integer',
    //     'total_unidades_vendidas' => 'integer',
    // ];
}