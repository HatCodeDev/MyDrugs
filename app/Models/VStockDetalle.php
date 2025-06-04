<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VStockDetalle extends Model
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $connection = 'mysql_editor';
    protected $table = 'v_stock_detalle'; // Nombre exacto de tu VISTA SQL

    /**
     * La clave primaria para el modelo.
     * Las vistas no tienen una clave primaria real, pero Eloquent necesita una.
     * Usa el ID de la tabla principal de tu vista (stocks.id).
     *
     * @var string
     */
    protected $primaryKey = 'stock_id'; // El alias que le diste a stocks.id en la vista

    /**
     * Indica si el ID es autoincremental.
     * Generalmente falso para vistas.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indica si el modelo debe tener timestamps (created_at, updated_at).
     * Como la vista ya los selecciona (y les pusimos alias), no queremos que Eloquent los gestione.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define los campos que no se pueden asignar masivamente (opcional para vistas de solo lectura).
     * Si solo vas a leer, no es estrictamente necesario.
     *
     * @var array
     */
    protected $guarded = ['*']; // O déjalo vacío: protected $guarded = [];

    
    
}