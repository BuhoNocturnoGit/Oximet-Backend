<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoBalon extends Model
{
    use HasFactory;

    protected $table = 'tipo_balon';

    protected $primaryKey = 'id_tipo';

    public $timestamps = false;

    protected $fillable = [
        'capacidad_o2_m3',
        'material',
        'modelo_valvula',
        'color',
        'norma',
        'capacidad_real_m3',
        'volumen_de_tanque',
        'peso_kg',
        'altura_cm',
        'imagen_ruta',
    ];

    protected $casts = [
        'capacidad_real_m3' => 'decimal:2',
        'volumen_de_tanque' => 'decimal:2',
        'peso_kg' => 'decimal:2',
        'altura_cm' => 'integer',
    ];
}
