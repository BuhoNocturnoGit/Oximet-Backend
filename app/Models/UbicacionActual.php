<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UbicacionActual extends Model
{
    protected $table = 'ubicacion_actual';

    protected $primaryKey = 'serie_balon';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'serie_balon',
        'id_ubicacion_actual',
        'estado_ubicacion',
        'fecha_ingreso',
    ];

    public function balon()
    {
        return $this->belongsTo(Balon::class, 'serie_balon', 'serie_balon');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion_actual', 'id_ubicacion');
    }
}
