<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VProductoDetalle extends Model
{
    /**
     * El nombre de la tabla (o vista) asociada con el modelo.
     * Corresponde al nombre exacto de tu vista en la base de datos.
     *
     * @var string
     */
    protected $connection = 'mysql_editor';
    protected $table = 'v_productos_detalle'; // Nombre de la vista actualizado

    /**
     * La clave primaria asociada con la vista.
     * Este es el alias del ID 'producto_id' que definiste en tu vista.
     *
     * @var string
     */
    protected $primaryKey = 'producto_id'; // Clave primaria actualizada según la vista

    /**
     * Indica si la clave primaria es auto-incrementable.
     * Generalmente es 'false' para vistas, ya que no son tablas físicas con auto-incremento.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indica si el modelo debe ser gestionado por timestamps (created_at, updated_at).
     * Se establece en 'false' porque las vistas no suelen tener estos campos gestionables
     * o ya los seleccionan directamente de las tablas subyacentes.
     *
     * @var bool
     */
    public $timestamps = false;

    // Si tu vista contiene columnas que podrías querer usar para asignación masiva (mass assignment),
    // puedes definirlas en $fillable. Para una vista de solo lectura,
    // es posible que no necesites esta propiedad si no vas a guardar datos a través de ella.
    // protected $fillable = [
    //     'producto_id',
    //     'producto_nombre',
    //     'producto_descripcion',
    //     'producto_precio_unitario',
    //     'producto_unidad_medida',
    //     'producto_activo',
    //     'producto_created_at',
    //     'producto_updated_at',
    //     'categoria_id',
    //     'categoria_nombre',
    //     'categoria_descripcion',
    // ];

    // O, para permitir asignación masiva para todas las columnas de la vista (con precaución)
    // protected $guarded = [];
}
