<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VBitacorasDetalle extends Model
{
    protected $table = 'v_bitacoras_detalle'; // Nombre de tu vista SQL

    public $timestamps = false; // La vista ya selecciona created_at y updated_at de la tabla original

    protected $primaryKey = 'bitacora_id'; // Alias del ID en tu vista

    public $incrementing = false;

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'user_id' => 'integer',
        'descripcion_detallada' => 'json', // O 'array'
        'referencia_id' => 'integer',
        'fecha_evento' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Si quisieras acceder al usuario directamente desde el modelo de la vista:
    // public function usuarioOriginal()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id');
    // }
}