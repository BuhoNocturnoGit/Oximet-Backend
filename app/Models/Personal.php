<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Personal extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'personal';

    protected $primaryKey = 'ID_Personal';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Apellidos',
        'Correo',
        'Contrasena',
        'id_rol',
        'estado',
        'Telefono',
        'Firma_Digital',
        'Fecha_Ultimo_Acceso',
        'ID_Admin_Aprobador',
        'Fecha_Aprobacion',
        'Comentarios_Aprobacion',
        'ID_Usuario_Creacion',
        'ID_Usuario_Modificacion',
    ];

    protected $hidden = [
        'Contrasena',
        'remember_token',
    ];

    protected $casts = [
        'Contrasena' => 'hashed',
        'Fecha_Ultimo_Acceso' => 'datetime',
        'Fecha_Solicitud' => 'datetime',
        'Fecha_Aprobacion' => 'datetime',
        'Fecha_Creacion' => 'datetime',
        'Fecha_Modificacion' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->Contrasena;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id');
    }
}
