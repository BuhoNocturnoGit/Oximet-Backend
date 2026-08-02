<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    // Enlace exacto a la tabla
    protected $table = 'proveedor';

    // Llave primaria personalizada (RUC)
    protected $primaryKey = 'id_proveedor';

    // Desactivar el autoincremento
    public $incrementing = false;

    // Definir el tipo de llave primaria como cadena de texto
    protected $keyType = 'string';

    // Desactivar timestamps por defecto de Laravel
    public $timestamps = false; 

    // Campos permitidos para inserción masiva
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
        'imagen_ruta'
    ];
}