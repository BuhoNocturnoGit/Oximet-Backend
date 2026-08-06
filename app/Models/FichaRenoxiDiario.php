<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichaRenoxiDiario extends Model
{
    protected $table = 'ficha_renoxi_diario';

    protected $primaryKey = 'id_renoxi';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_responsable',
        'tanque_nivel_inicial',
        'tanque_volumen_inicial_m3',
        'tanque_nivel_final',
        'tanque_volumen_final_m3',
        'total_ingresos_praxair_m3',
        'total_egresos_sis_m3',
        'total_egresos_bancadas_m3',
        'total_mermas_trasegado_m3',
        'desviacion_calculada_m3',
        'estado',
        'fecha_creacion',
        'id_usuario_creacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'tanque_nivel_inicial' => 'decimal:2',
        'tanque_volumen_inicial_m3' => 'decimal:2',
        'tanque_nivel_final' => 'decimal:2',
        'tanque_volumen_final_m3' => 'decimal:2',
        'total_ingresos_praxair_m3' => 'decimal:2',
        'total_egresos_sis_m3' => 'decimal:2',
        'total_egresos_bancadas_m3' => 'decimal:2',
        'total_mermas_trasegado_m3' => 'decimal:2',
        'desviacion_calculada_m3' => 'decimal:2',
    ];

    public function responsable()
    {
        return $this->belongsTo(Personal::class, 'id_responsable', 'ID_Personal');
    }

    public function usuarioCreacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_creacion', 'ID_Personal');
    }
}
