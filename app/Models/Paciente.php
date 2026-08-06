<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'paciente';

    protected $primaryKey = 'id_paciente';

    public $timestamps = false;

    protected $fillable = [
        'nro_expediente',
        'dni',
        'nombre',
        'apellidos',
        'tipo',
        'estado',
        'fecha_registro',
    ];

    public function atenciones()
    {
        return $this->hasMany(AtencionSisDiario::class, 'id_paciente', 'id_paciente');
    }
}
