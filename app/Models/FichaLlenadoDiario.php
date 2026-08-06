<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichaLlenadoDiario extends Model
{
    protected $table = 'ficha_llenado_diario';

    protected $primaryKey = 'id_ficha';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'planta',
        'estado',
        'total_balones_dia',
        'presion_final_psi',
        'total_m3_producidos_dia',
        'pureza_final',
        'fecha_creacion',
        'id_usuario_creacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_balones_dia' => 'integer',
        'presion_final_psi' => 'decimal:2',
        'total_m3_producidos_dia' => 'decimal:2',
        'pureza_final' => 'decimal:2',
    ];

    public function usuarioCreacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_creacion', 'ID_Personal');
    }
}
