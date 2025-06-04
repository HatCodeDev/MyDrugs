<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VUserDetalle extends Model
{
    /**
     * La tabla asociada con el modelo (la vista).
     */
    protected $connection = 'mysql_editor';
    protected $table = 'v_users_detalle';

    /**
     * La clave primaria para el modelo.
     * Usamos el ID del usuario de la vista.
     */
    protected $primaryKey = 'user_id';

    /**
     * Indica si el ID es autoincremental.
     * Generalmente falso para vistas.
     */
    public $incrementing = false;

    /**
     * Indica si el modelo debe tener timestamps (created_at, updated_at).
     * La vista ya los selecciona (y les pusimos alias),
     * por lo que no queremos que Eloquent los gestione.
     */
    public $timestamps = false; // O true si quieres que Eloquent maneje user_created_at y user_updated_at como si fueran created_at y updated_at

    /**
     * Define los campos que no se pueden asignar masivamente.
     * Para vistas de solo lectura, puedes proteger todos los campos.
     */
    protected $guarded = ['*'];
}