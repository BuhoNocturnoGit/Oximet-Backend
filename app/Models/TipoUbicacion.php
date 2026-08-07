<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUbicacion extends Model
{
    use HasFactory;

    protected $table = 'tipo_ubicacion';

    protected $primaryKey = 'id_tipo_ubicacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo',
        'descripcion',
        'icono',
        'color',
        'orden',
        'permite_balones',
        'permite_movimientos',
        'es_almacen',
        'es_produccion',
        'es_consumo',
        'es_mantenimiento',
        'es_descartado',
        'es_transito',
        'es_servicio_hospital',
        'capacidad_default_balones',
        'capacidad_default_m3',
        'requiere_autorizacion',
        'nivel_autorizacion',
        'activo',
        'fecha_creacion',
        'id_usuario_creacion',
        'fecha_modificacion',
        'id_usuario_modificacion',
        'imagen_ruta',
    ];

    protected $casts = [
        'permite_balones' => 'boolean',
        'permite_movimientos' => 'boolean',
        'es_almacen' => 'boolean',
        'es_produccion' => 'boolean',
        'es_consumo' => 'boolean',
        'es_mantenimiento' => 'boolean',
        'es_descartado' => 'boolean',
        'es_transito' => 'boolean',
        'es_servicio_hospital' => 'boolean',
        'requiere_autorizacion' => 'boolean',
        'activo' => 'boolean',
        'capacidad_default_m3' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class, 'id_tipo_ubicacion', 'id_tipo_ubicacion');
    }
}
