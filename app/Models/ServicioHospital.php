<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioHospital extends Model
{
    use HasFactory;

    protected $table = 'servicio_hospital';

    protected $primaryKey = 'id_servicio';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'descripcion',
        'telefono_interno',
        'jefe_servicio',
        'email_contacto',
        'camas_disponibles',
        'consumo_estimado_m3_dia',
        'activo',
        'fecha_creacion',
        'id_usuario_creacion',
        'fecha_modificacion',
        'id_usuario_modificacion',
        'imagen_ruta',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'consumo_estimado_m3_dia' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class, 'id_servicio_hospital', 'id_servicio');
    }
}
