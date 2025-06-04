<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}