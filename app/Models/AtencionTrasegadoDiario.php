<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionTrasegadoDiario extends Model
{
    protected $table = 'atencion_trasegado_diario';

    protected $primaryKey = 'id_atencion';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'estado',
        'merma_calculada_m3',
        'fecha_creacion',
        'id_usuario_creacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'merma_calculada_m3' => 'decimal:2',
    ];

    public function usuarioCreacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_creacion', 'ID_Personal');
    }
}
