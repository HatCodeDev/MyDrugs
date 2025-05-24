<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Get the productos for the categoria.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Get the promociones aplicables a esta categoria.
     */
    public function promociones(): HasMany
    {
        return $this->hasMany(Promocion::class, 'aplicable_a_categoria_id');
    }
}