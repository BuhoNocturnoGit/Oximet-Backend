<?php

namespace Database\Seeders;

use App\Models\EstadoBalon;
use Illuminate\Database\Seeder;

class EstadoBalonSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            'Lleno',
            'Vacio',
            'En uso',
            'En mantenimiento',
            'Dado de baja',
            'Malogrado',
            'Reservado',
        ];

        foreach ($estados as $nombre) {
            EstadoBalon::firstOrCreate(['nombre_estado' => $nombre]);
        }
    }
}
