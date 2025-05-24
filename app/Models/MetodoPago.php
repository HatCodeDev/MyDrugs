<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'nombre_metodo',
        'descripcion_instrucciones',
        'comision_asociada_porcentaje',
        'activo',
        'logo_url',
    ];

    protected $casts = [
        'comision_asociada_porcentaje' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Get the pedidos that use this metodo de pago.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}