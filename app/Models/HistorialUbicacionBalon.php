<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialUbicacionBalon extends Model
{
    protected $table = 'historial_ubicacion_balon';

    protected $primaryKey = 'id_historial';

    public $timestamps = false;

    protected $fillable = [
        'serie_balon',
        'id_ubicacion_origen',
        'id_ubicacion_destino',
        'tipo_movimiento',
        'fecha_movimiento',
        'id_responsable',
    ];

    public function balon()
    {
        return $this->belongsTo(Balon::class, 'serie_balon', 'serie_balon');
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_origen', 'id_ubicacion');
    }

    public function ubicacionDestino()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_destino', 'id_ubicacion');
    }
}
