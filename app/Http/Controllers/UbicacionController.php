<?php

namespace App\Http\Controllers;

use App\Models\ServicioHospital;
use App\Models\TipoUbicacion;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UbicacionController extends Controller
{
    public function crearJerarquiaCompleta(Request $request)
    {
        $request->validate([
            'servicio.codigo' => 'required|string|max:20',
            'servicio.nombre' => 'required|string|max:100',
            'servicio.tipo' => 'required|string|max:50',
            'sub_areas' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $usuarioId = $request->user()->ID_Personal;

            $servicio = ServicioHospital::create([
                'codigo' => $request->input('servicio.codigo'),
                'nombre' => $request->input('servicio.nombre'),
                'tipo' => $request->input('servicio.tipo'),
                'descripcion' => $request->input('servicio.descripcion'),
                'telefono_interno' => $request->input('servicio.telefono_interno'),
                'jefe_servicio' => $request->input('servicio.jefe_servicio'),
                'email_contacto' => $request->input('servicio.email_contacto'),
                'camas_disponibles' => $request->input('servicio.camas_disponibles'),
                'consumo_estimado_m3_dia' => $request->input('servicio.consumo_estimado_m3_dia'),
                'activo' => true,
                'fecha_creacion' => now(),
                'id_usuario_creacion' => $usuarioId,
            ]);

            $tipoServicio = TipoUbicacion::firstOrCreate(
                ['nombre_tipo' => 'Servicio Hospital'],
                [
                    'orden' => 1,
                    'permite_balones' => true,
                    'permite_movimientos' => true,
                    'es_almacen' => false,
                    'es_produccion' => false,
                    'es_consumo' => false,
                    'es_mantenimiento' => false,
                    'es_descartado' => false,
                    'es_transito' => false,
                    'es_servicio_hospital' => true,
                    'requiere_autorizacion' => false,
                    'activo' => true,
                    'fecha_creacion' => now(),
                    'id_usuario_creacion' => $usuarioId,
                ]
            );

            $padre = Ubicacion::create([
                'id_tipo_ubicacion' => $tipoServicio->id_tipo_ubicacion,
                'id_servicio_hospital' => $servicio->id_servicio,
                'id_ubicacion_padre' => null,
                'codigo' => $request->input('servicio.codigo'),
                'nombre' => $request->input('servicio.nombre'),
                'descripcion' => $request->input('servicio.descripcion'),
                'estado' => 'Activa',
                'fecha_creacion' => now(),
                'id_usuario_creacion' => $usuarioId,
            ]);

            $hijos = [];

            foreach ($request->input('sub_areas', []) as $subArea) {
                Validator::make($subArea, [
                    'codigo' => ['required', 'string', 'max:20'],
                    'nombre' => ['required', 'string', 'max:100'],
                    'config_json' => ['nullable', 'array'],
                ])->validate();

                $hijos[] = Ubicacion::create([
                    'id_tipo_ubicacion' => $tipoServicio->id_tipo_ubicacion,
                    'id_servicio_hospital' => $servicio->id_servicio,
                    'id_ubicacion_padre' => $padre->id_ubicacion,
                    'codigo' => $subArea['codigo'],
                    'nombre' => $subArea['nombre'],
                    'config_json' => $subArea['config_json'] ?? null,
                    'estado' => 'Activa',
                    'fecha_creacion' => now(),
                    'id_usuario_creacion' => $usuarioId,
                ]);
            }

            DB::commit();

            return response()->json([
                'servicio' => $servicio,
                'ubicacion_padre' => $padre,
                'sub_areas' => $hijos,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
