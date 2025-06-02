<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repartidor extends Model
{
    use HasFactory;

    protected $table = 'repartidores';

    protected $fillable = [
        'user_id',
        'nombre_alias',
        'vehiculo_descripcion',
        'zona_operativa_preferida',
        'disponible',
        'calificacion_promedio',
        'numero_contacto_cifrado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'disponible' => 'boolean',
        'calificacion_promedio' => 'decimal:2',
    ];

    /**
     * Obtiene el usuario asociado a este repartidor (si lo hay).
     */
    public function user(): BelongsTo
    {
        // Asegúrate de que el modelo User exista en App\Models\User
        return $this->belongsTo(User::class, 'user_id');
    }
}