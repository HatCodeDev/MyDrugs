<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VCalificacionDetalle extends Model
{
    protected $connection = 'mysql_editor';
    protected $table = 'v_calificaciones_detalle';
    public $timestamps = false;
    protected $primaryKey = 'calificacion_id';
    public $incrementing = false;
}