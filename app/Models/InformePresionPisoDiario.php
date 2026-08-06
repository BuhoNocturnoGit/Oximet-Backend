<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformePresionPisoDiario extends Model
{
    protected $table = 'informe_presion_piso_diario';

    protected $primaryKey = 'id_informe';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_responsable',
        'total_balones_entregados',
        'total_balones_recibidos',
        'volumen_total_m3',
        'estado',
        'fecha_creacion',
        'id_usuario_creacion',
        'id_usuario_modificacion',
        'fecha_modificacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_balones_entregados' => 'integer',
        'total_balones_recibidos' => 'integer',
        'volumen_total_m3' => 'decimal:2',
    ];

    public function responsable()
    {
        return $this->belongsTo(Personal::class, 'id_responsable', 'ID_Personal');
    }

    public function usuarioCreacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_creacion', 'ID_Personal');
    }

    public function usuarioModificacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_modificacion', 'ID_Personal');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleConsumoPiso::class, 'id_informe', 'id_informe');
    }
}
