<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicacion';

    protected $primaryKey = 'id_ubicacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'piso',
        'estado',
        'fecha_creacion',
        'id_usuario_creacion',
    ];

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
