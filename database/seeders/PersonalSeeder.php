<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Personal;
use Carbon\Carbon;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        //Administrador 
        Personal::create([
            'Nombre' => 'Carlos',
            'Apellidos' => 'Admin',
            'Correo' => 'admin@oximet.com',
            'Contrasena' => Hash::make('password123'),
            'Estado_Registro' => 'Activo',
            'Rol_Solicitado' => 'Admin',
            'Rol_Asignado' => 'Admin',
            'Rol' => 'Admin',
            'Telefono' => '1234567890',
            'Activo' => 1,
            'Bloqueado' => 0,
            'Fecha_Solicitud' => Carbon::now(),
            'Fecha_Aprobacion' => Carbon::now(),
            'Fecha_Creacion' => Carbon::now(),
            'Fecha_Modificacion' => Carbon::now(),
        ]);

        // Operador Pendiente
        Personal::create([
            'Nombre' => 'Maria',
            'Apellidos' => 'Pendiente',
            'Correo' => 'maria@oximet.com',
            'Contrasena' => Hash::make('password123'),
            'Estado_Registro' => 'Pendiente',
            'Rol_Solicitado' => 'Operador',
            'Rol_Asignado' => null,
            'Rol' => null, // Obligatorio NULL para el panel de pendientes
            'Telefono' => '0987654321',
            'Activo' => 0,
            'Bloqueado' => 0,
            'Fecha_Solicitud' => Carbon::now(),
            'Fecha_Creacion' => Carbon::now(),
            'Fecha_Modificacion' => Carbon::now(),
        ]);
        
        //Operador Pendiente
        Personal::create([
            'Nombre' => 'Luis',
            'Apellidos' => 'Gomez',
            'Correo' => 'luis@oximet.com',
            'Contrasena' => Hash::make('password123'),
            'Estado_Registro' => 'Pendiente',
            'Rol_Solicitado' => 'Supervisor',
            'Rol_Asignado' => null,
            'Rol' => null,
            'Telefono' => '1122334455',
            'Activo' => 0,
            'Bloqueado' => 0,
            'Fecha_Solicitud' => Carbon::now(),
            'Fecha_Creacion' => Carbon::now(),
            'Fecha_Modificacion' => Carbon::now(),
        ]);
    }
}
