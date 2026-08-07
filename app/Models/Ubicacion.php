<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicacion';

    protected $primaryKey = 'id_ubicacion';

    public $timestamps = false;

    protected $fillable = [
        'id_tipo_ubicacion',
        'id_servicio_hospital',
        'id_ubicacion_padre',
        'codigo',
        'nombre',
        'descripcion',
        'capacidad_maxima_balones',
        'capacidad_maxima_m3',
        'estado',
        'edificio',
        'piso',
        'sector',
        'sala',
        'nro_cama',
        'referencia',
        'config_json',
        'fecha_creacion',
        'id_usuario_creacion',
        'fecha_modificacion',
        'id_usuario_modificacion',
    ];

    protected $casts = [
        'config_json' => 'array',
        'capacidad_maxima_m3' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoUbicacion::class, 'id_tipo_ubicacion');
    }

    public function servicio()
    {
        return $this->belongsTo(ServicioHospital::class, 'id_servicio_hospital');
    }

    public function padre()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_padre');
    }

    public function hijos()
    {
        return $this->hasMany(Ubicacion::class, 'id_ubicacion_padre');
    }

    public function balones()
    {
        return $this->hasMany(Balon::class, 'id_ubicacion_actual', 'id_ubicacion');
    }

    public function ubicacionesActuales()
    {
        return $this->hasMany(UbicacionActual::class, 'id_ubicacion_actual', 'id_ubicacion');
    }

    public function detallesConsumo()
    {
        return $this->hasMany(DetalleConsumoPiso::class, 'id_ubicacion_servicio', 'id_ubicacion');
    }
}
