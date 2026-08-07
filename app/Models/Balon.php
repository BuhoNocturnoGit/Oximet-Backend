<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balon extends Model
{
    use HasFactory;

    protected $table = 'balones';

    protected $primaryKey = 'serie_balon';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'serie_balon',
        'codigo_barras',
        'id_tipo',
        'id_estado',
        'origen',
        'propiedad',
        'id_proveedor',
        'capacidad_m3',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'fecha_prueba_hidrostatica',
        'fecha_cambio_valvula',
        'numero_lote_praxair',
        'guia_remision_praxair',
        'max_cargas',
        'cargas_utilizadas',
        'cargas_disponibles',
        'estado_operativo',
        'condicion',
        'observaciones',
        'presion_actual_psi',
        'o2_disponible_m3',
        'pureza_actual',
        'numero_recargas_total',
        'fecha_ultima_recarga',
        'fecha_ultimo_mantenimiento',
        'id_ubicacion_actual',
        'id_usuario_registro',
        'id_usuario_ultima_modificacion',
        'fecha_ultima_modificacion',
    ];

    protected $casts = [
        'capacidad_m3' => 'decimal:2',
        'fecha_fabricacion' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_prueba_hidrostatica' => 'date',
        'fecha_cambio_valvula' => 'date',
        'max_cargas' => 'integer',
        'cargas_utilizadas' => 'integer',
        'cargas_disponibles' => 'integer',
        'presion_actual_psi' => 'decimal:2',
        'o2_disponible_m3' => 'decimal:2',
        'pureza_actual' => 'decimal:2',
        'numero_recargas_total' => 'integer',
        'fecha_ultima_recarga' => 'datetime',
        'fecha_ultimo_mantenimiento' => 'datetime',
        'fecha_registro' => 'datetime',
        'fecha_ultima_modificacion' => 'datetime',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoBalon::class, 'id_tipo', 'id_tipo');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoBalon::class, 'id_estado', 'id_estado');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function ubicacionActual()
    {
        return $this->hasOne(UbicacionActual::class, 'serie_balon', 'serie_balon');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialUbicacionBalon::class, 'serie_balon', 'serie_balon');
    }

    public function ubicacionServicioActual()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_actual', 'id_ubicacion');
    }
}
