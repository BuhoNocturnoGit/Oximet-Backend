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
        'capacidad_m3',
        'presion_actual_psi',
        'cargas_utilizadas',
        'max_cargas',
        'id_estado',
        'id_ubicacion_actual',
        'fecha_creacion',
        'id_usuario_creacion',
        'id_usuario_modificacion',
        'fecha_modificacion',
    ];

    protected $casts = [
        'capacidad_m3' => 'decimal:2',
        'presion_actual_psi' => 'decimal:2',
        'cargas_utilizadas' => 'integer',
        'max_cargas' => 'integer',
    ];

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
