<?php

namespace Database\Seeders;

use App\Models\TipoUbicacion;
use Illuminate\Database\Seeder;

class TipoUbicacionSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'nombre_tipo' => 'Servicio Hospital',
                'descripcion' => 'Área administrativa de un servicio médico del hospital',
                'orden' => 1,
                'permite_balones' => true,
                'permite_movimientos' => true,
                'es_servicio_hospital' => true,
            ],
            [
                'nombre_tipo' => 'Cama Hospital',
                'descripcion' => 'Cama de hospitalización con puntos de oxígeno',
                'orden' => 2,
                'permite_balones' => true,
                'permite_movimientos' => true,
                'es_consumo' => true,
            ],
            [
                'nombre_tipo' => 'Bancada',
                'descripcion' => 'Bancada de consumo de oxígeno',
                'orden' => 3,
                'permite_balones' => true,
                'permite_movimientos' => true,
                'es_consumo' => true,
            ],
            [
                'nombre_tipo' => 'Almacén',
                'descripcion' => 'Almacén de balones de oxígeno',
                'orden' => 4,
                'permite_balones' => true,
                'permite_movimientos' => true,
                'es_almacen' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            $flags = [
                'es_almacen' => false,
                'es_produccion' => false,
                'es_consumo' => false,
                'es_mantenimiento' => false,
                'es_descartado' => false,
                'es_transito' => false,
                'es_servicio_hospital' => false,
                'requiere_autorizacion' => false,
                'activo' => true,
            ];

            TipoUbicacion::create(array_merge([
                'icono' => null,
                'color' => null,
                'capacidad_default_balones' => null,
                'capacidad_default_m3' => null,
                'nivel_autorizacion' => null,
                'fecha_creacion' => now(),
                'id_usuario_creacion' => null,
            ], $flags, $tipo));
        }
    }
}
