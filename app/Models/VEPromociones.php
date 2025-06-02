<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VEPromociones extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 've_rendimiento_promociones'; // Nombre exacto de tu vista SQL

    /**
     * Indicates if the model should be timestamped.
     * Las vistas generalmente no manejan timestamps de Eloquent directamente.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     * Es crucial para Filament identificar filas.
     *
     * @var string
     */
    protected $primaryKey = 'promocion_id'; // La columna 'promocion_id' de tu vista

    /**
     * Indicates if the model's ID is auto-incrementing.
     * Las claves primarias de las vistas no suelen ser autoincrementales.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int'; // O 'string' si tu ID no es numérico

    // Si tu vista tiene columnas que quieres que sean tratadas como fechas, puedes definirlas aquí:
    // protected $dates = [
    //     'fecha_inicio',
    //     'fecha_fin',
    // ];

    // No necesitas $fillable o $guarded si la vista es de solo lectura
    // y no intentarás crear o actualizar registros a través de este modelo.
}
