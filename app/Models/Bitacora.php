<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacoras';

    /**
     * Los atributos que se pueden asignar masivamente.
     * Normalmente, la bitácora se llena mediante triggers o lógica de aplicación,
     * por lo que $fillable podría no ser estrictamente necesario para creación manual masiva,
     * pero es bueno tenerlo por si acaso o para testing.
     */
    protected $fillable = [
        'user_id',
        'accion',
        'descripcion_detallada',
        'referencia_entidad',
        'referencia_id',
        'fecha_evento',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'user_id' => 'integer',
        'descripcion_detallada' => 'json', // O 'array' si prefieres trabajar con arrays en PHP
        'referencia_id' => 'integer',
        'fecha_evento' => 'datetime',
    ];

    /**
     * Obtiene el usuario que realizó la acción.
     */
    public function user(): BelongsTo
    {
        // Asegúrate de que el modelo User exista en App\Models\User
        return $this->belongsTo(User::class, 'user_id');
    }
}