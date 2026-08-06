<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionSisDiario extends Model
{
    protected $table = 'atencion_sis_diario';

    protected $primaryKey = 'id_atencion';

    public $timestamps = false;

    protected $fillable = [
        'id_reporte',
        'id_paciente',
        'serie_balon',
        'psi_entregado',
        'hora_entrega',
        'hora_devolucion',
        'estado',
        'id_usuario_registro',
    ];

    protected $casts = [
        'psi_entregado' => 'decimal:2',
        'hora_entrega' => 'datetime',
        'hora_devolucion' => 'datetime',
    ];

    public function reporte()
    {
        return $this->belongsTo(ReporteSisDiario::class, 'id_reporte', 'id_reporte');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function balon()
    {
        return $this->belongsTo(Balon::class, 'serie_balon', 'serie_balon');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_registro', 'ID_Personal');
    }
}
