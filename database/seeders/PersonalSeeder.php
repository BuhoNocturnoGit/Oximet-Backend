<?php

namespace Database\Seeders;

use App\Models\Personal;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador
        Personal::create([
            'Nombre' => 'Carlos',
            'Apellidos' => 'Admin',
            'Correo' => 'admin@oximet.com',
            'Contrasena' => Hash::make('password123'),
            'id_rol' => 1,
            'estado' => 'Activo',
            'Telefono' => '1234567890',
            'Fecha_Aprobacion' => Carbon::now(),
            'Fecha_Creacion' => Carbon::now(),
            'Fecha_Modificacion' => Carbon::now(),
        ]);

        // Supervisor
        Personal::create([
            'Nombre' => 'Maria',
            'Apellidos' => 'Supervisor',
            'Correo' => 'maria@oximet.com',
            'Contrasena' => Hash::make('password123'),
            'id_rol' => 2,
            'estado' => 'Activo',
            'Telefono' => '0987654321',
            'Fecha_Creacion' => Carbon::now(),
            'Fecha_Modificacion' => Carbon::now(),
        ]);

        // Operario
        Personal::create([
            'Nombre' => 'Luis',
            'Apellidos' => 'Gomez',
            'Correo' => 'luis@oximet.com',
            'Contrasena' => Hash::make('password123'),
            'id_rol' => 3,
            'estado' => 'Activo',
            'Telefono' => '1122334455',
            'Fecha_Creacion' => Carbon::now(),
            'Fecha_Modificacion' => Carbon::now(),
        ]);
    }
}
