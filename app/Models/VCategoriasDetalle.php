<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VCategoriasDetalle extends Model
{
    /**
     * La tabla de base de datos asociada con el modelo.
     * Este debe ser el nombre exacto de tu vista SQL.
     *
     * @var string
     */
    protected $table = 'v_categorias_detalle'; // Nombre de tu vista de categorías

    /**
     * Indica si el modelo debe ser timestampeado.
     * Para las vistas, generalmente se establece en false.
     *
     * @var bool
     */
    public $timestamps = false; // [cite: 1]

    /**
     * La clave primaria para el modelo.
     * Debe ser el alias que usaste para el ID en tu vista.
     *
     * @var string
     */
    protected $primaryKey = 'categoria_id'; // Alias del ID en tu vista 'v_categorias_detalle'

    /**
     * Indica si los IDs son autoincrementales.
     * Para las vistas, generalmente se establece en false.
     *
     * @var bool
     */
    public $incrementing = false; // [cite: 1]

    // Aquí podrías definir relaciones si tu vista las incluyera
    // y quisieras acceder a ellas a través de este modelo de vista.
    // Ejemplo:
    // public function algunModeloRelacionado()
    // {
    //     // Asumiendo que 'algun_id_fk' existe en tu vista y es una FK
    //     return $this->belongsTo(OtroModelo::class, 'algun_id_fk', 'id_original_en_otro_modelo');
    // }
}