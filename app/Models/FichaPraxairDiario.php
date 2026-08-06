<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichaPraxairDiario extends Model
{
    protected $table = 'ficha_praxair_diario';

    protected $primaryKey = 'id_praxair';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'estado',
        'volumen_m3',
        'fecha_creacion',
        'id_usuario_creacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'volumen_m3' => 'decimal:2',
    ];

    public function usuarioCreacion()
    {
        return $this->belongsTo(Personal::class, 'id_usuario_creacion', 'ID_Personal');
    }
}
