<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDetailExtendedView extends Model
{
    protected $table = 'vw_product_details_extended'; 

    public $timestamps = false;
    protected $primaryKey = 'product_id';

    public $incrementing = false; 

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'product_is_active' => 'boolean',
        'average_rating' => 'decimal:2', 
        'total_stock_available' => 'integer',
        'rating_count' => 'integer',
        'product_created_at' => 'datetime',
        'product_updated_at' => 'datetime',
    ];

}