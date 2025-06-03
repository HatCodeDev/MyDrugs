<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        // Aquí puedes añadir conversiones si son necesarias, por ejemplo:
        // 'algun_campo_booleano' => 'boolean',
    ];

    // Si tienes relaciones, defínelas aquí. Ejemplo:
    // public function productos()
    // {
    //     return $this->hasMany(Producto::class);
    // }
}