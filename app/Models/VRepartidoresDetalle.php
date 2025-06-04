<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VRepartidoresDetalle extends Model
{
    /**
     * La tabla de base de datos asociada con el modelo.
     * Nombre exacto de tu vista SQL.
     */
    protected $connection = 'mysql_editor';
    protected $table = 'v_repartidores_detalle';

    /**
     * Indica si el modelo debe ser timestampeado.
     * Para las vistas, generalmente se establece en false.
     */
    public $timestamps = false;

    /**
     * La clave primaria para el modelo.
     * Debe ser el alias que usaste para el ID en tu vista.
     */
    protected $primaryKey = 'repartidor_id'; // Alias del ID en tu vista

    /**
     * Indica si los IDs son autoincrementales.
     * Para las vistas, generalmente se establece en false.
     */
    public $incrementing = false;

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * Esto es útil si tu vista devuelve campos que necesitan conversión.
     */
    protected $casts = [
        'user_id' => 'integer',
        'disponible' => 'boolean',
        'calificacion_promedio' => 'decimal:2',
        'created_at' => 'datetime', // Asumiendo que vienen de la tabla original
        'updated_at' => 'datetime', // Asumiendo que vienen de la tabla original
    ];
}