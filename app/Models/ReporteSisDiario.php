<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteSisDiario extends Model
{
    protected $table = 'reporte_sis_diario';

    protected $primaryKey = 'id_reporte';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_responsable',
        'total_atenciones',
        'total_m3_sis',
        'estado',
        'fecha_creacion',
        'id_usuario_creacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_atenciones' => 'integer',
        'total_m3_sis' => 'decimal:2',
    ];

    public function responsable()
    {
        return $this->belongsTo(Personal::class, 'id_responsable', 'ID_Personal');
    }

    public function usuarioCreacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_creacion', 'ID_Personal');
    }

    public function atenciones()
    {
        return $this->hasMany(AtencionSisDiario::class, 'id_reporte', 'id_reporte');
    }
}
