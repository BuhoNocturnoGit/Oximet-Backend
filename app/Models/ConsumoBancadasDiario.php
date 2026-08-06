<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumoBancadasDiario extends Model
{
    protected $table = 'consumo_bancadas_diario';

    protected $primaryKey = 'id_consumo';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'bancada',
        'estado',
        'total_psi',
        'total_m3_consumidos',
        'fecha_creacion',
        'id_usuario_creacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_psi' => 'decimal:2',
        'total_m3_consumidos' => 'decimal:2',
    ];

    public function usuarioCreacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_creacion', 'ID_Personal');
    }
}
