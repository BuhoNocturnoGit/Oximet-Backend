<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleConsumoPiso extends Model
{
    protected $table = 'detalle_consumo_piso';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_informe',
        'id_ubicacion_servicio',
        'serie_balon_lleno',
        'id_ubicacion_balon_lleno',
        'volumen_m3',
        'serie_balon_vacio',
        'id_ubicacion_balon_vacio',
        'prefactura',
        'id_personal_entrega',
        'id_personal_recepciona',
        'firma_recepciona',
        'hora_entrega',
        'hora_recepcion',
        'estado',
        'observaciones',
        'fecha_registro',
        'id_usuario_registro',
    ];

    protected $casts = [
        'volumen_m3' => 'decimal:2',
    ];

    public function informe()
    {
        return $this->belongsTo(InformePresionPisoDiario::class, 'id_informe', 'id_informe');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_servicio', 'id_ubicacion');
    }

    public function balonLleno()
    {
        return $this->belongsTo(Balon::class, 'serie_balon_lleno', 'serie_balon');
    }

    public function balonVacio()
    {
        return $this->belongsTo(Balon::class, 'serie_balon_vacio', 'serie_balon');
    }

    public function personalEntrega()
    {
        return $this->belongsTo(Personal::class, 'id_personal_entrega', 'ID_Personal');
    }

    public function personalRecepciona()
    {
        return $this->belongsTo(Personal::class, 'id_personal_recepciona', 'ID_Personal');
    }
}
