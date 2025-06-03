<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VImagenesProductoDetalle extends Model
{
    /**
     * La tabla de base de datos asociada con el modelo.
     * Este debe ser el nombre exacto de tu vista SQL.
     * (Basado en nuestra conversación anterior: v_imagenes_producto_detalle)
     *
     * @var string
     */
    protected $table = 'v_imagenes_producto_detalle';

    /**
     * Indica si el modelo debe ser timestampeado.
     * Para las vistas, generalmente se establece en false.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * La clave primaria para el modelo.
     * Debe ser el alias que usaste para el ID en tu vista.
     * (Basado en nuestra conversación anterior: imagen_producto_id)
     *
     * @var string
     */
    protected $primaryKey = 'imagen_producto_id';

    /**
     * Indica si los IDs son autoincrementales.
     * Para las vistas, generalmente se establece en false.
     *
     * @var bool
     */
    public $incrementing = false;

    // Podrías definir atributos $casts aquí si tu vista devuelve tipos
    // que necesiten ser convertidos (ej. fechas, booleanos, etc.).
    // protected $casts = [
    //     'imagen_created_at' => 'datetime',
    //     'imagen_updated_at' => 'datetime',
    //     'orden' => 'integer',
    // ];

    // Si necesitas acceder a relaciones desde la vista (y los IDs están presentes en la vista),
    // podrías definirlas aquí, aunque es menos común para modelos de vista puros.
    // Ejemplo:
    // public function productoOriginal()
    // {
    //     return $this->belongsTo(Producto::class, 'producto_id', 'id');
    // }
}