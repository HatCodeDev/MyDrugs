<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VPedidoDetalle extends Model
{
    /**
     * La tabla (o vista) asociada con el modelo.
     */
    protected $table = 'v_pedido_detalle';

    /**
     * Indica si el modelo debe tener timestamps (created_at, updated_at) gestionados por Eloquent.
     * Los seleccionamos en la vista, así que Eloquent no necesita gestionarlos aquí.
     */
    public $timestamps = false;

    /**
     * La clave primaria para el modelo (debe coincidir con el alias en la vista).
     */
    protected $primaryKey = 'detalle_id';

    /**
     * Indica si la ID es autoincremental. Para vistas, generalmente es false.
     */
    public $incrementing = false;

}