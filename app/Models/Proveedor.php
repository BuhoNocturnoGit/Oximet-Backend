<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedors';

    protected $primaryKey = 'id_proveedor';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'nombre',
        'direccion',
        'contacto_telefonico',
        'contacto_email',
        'contacto_nombre',
        'tipo_contrato',
        'activo',
        'fecha_registro',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
    ];
}
