<?php

namespace App\Http\Controllers;

use App\Models\Balon;
use App\Models\DetalleConsumoPiso;
use App\Models\EstadoBalon;
use App\Models\HistorialUbicacionBalon;
use App\Models\InformePresionPisoDiario;
use App\Models\UbicacionActual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformePisoController extends Controller
{
    public function iniciarRecorrido(Request $request)
    {
        $request->validate([
            'id_responsable' => 'required|integer',
        ]);

        $informe = InformePresionPisoDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $request->id_responsable,
            'total_balones_entregados' => 0,
            'total_balones_recibidos' => 0,
            'volumen_total_m3' => 0,
            'estado' => 'En Proceso',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $request->user()->ID_Personal,
        ]);

        return response()->json($informe, 201);
    }

    public function cerrarRecorrido(Request $request, $id_informe)
    {
        $informe = InformePresionPisoDiario::find($id_informe);

        if (! $informe) {
            return response()->json(['message' => 'Informe no encontrado'], 404);
        }

        $informe->estado = 'Completado';
        $informe->id_usuario_modificacion = $request->user()->ID_Personal;
        $informe->fecha_modificacion = now();
        $informe->save();

        return response()->json([
            'message' => 'Recorrido cerrado correctamente',
            'informe' => $informe,
        ], 200);
    }

    public function registrarIntercambio(Request $request, $id_informe)
    {
        $request->validate([
            'id_ubicacion_servicio' => 'required|integer',
            'serie_balon_lleno' => 'required|string',
            'id_ubicacion_balon_lleno' => 'nullable|integer',
            'serie_balon_vacio' => 'nullable|string',
            'id_ubicacion_balon_vacio' => 'nullable|integer',
            'prefactura' => 'nullable|string|max:50',
            'id_personal_recepciona' => 'nullable|integer',
            'firma_recepciona' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        $informe = InformePresionPisoDiario::find($id_informe);

        if (! $informe) {
            return response()->json(['message' => 'Informe no encontrado'], 404);
        }

        $balonLleno = Balon::find($request->serie_balon_lleno);

        if (! $balonLleno) {
            return response()->json(['message' => 'Balón lleno no encontrado'], 404);
        }

        $balonVacio = null;
        if ($request->serie_balon_vacio) {
            $balonVacio = Balon::find($request->serie_balon_vacio);

            if (! $balonVacio) {
                return response()->json(['message' => 'Balón vacío no encontrado'], 404);
            }
        }

        $volumenM3 = $balonLleno->capacidad_m3;
        $origenLleno = $request->id_ubicacion_balon_lleno ?? $balonLleno->id_ubicacion_actual;
        $origenVacio = $request->id_ubicacion_balon_vacio ?? ($balonVacio ? $balonVacio->id_ubicacion_actual : null);

        try {
            $detalle = DB::transaction(function () use ($request, $informe, $balonLleno, $balonVacio, $volumenM3, $origenLleno, $origenVacio) {
                $usuario = $request->user();

                $balonLleno->id_estado = EstadoBalon::idDe('En uso');
                $balonLleno->id_ubicacion_actual = $request->id_ubicacion_servicio;
                $balonLleno->id_usuario_ultima_modificacion = $usuario->ID_Personal;
                $balonLleno->fecha_ultima_modificacion = now();
                $balonLleno->save();

                UbicacionActual::updateOrCreate(
                    ['serie_balon' => $balonLleno->serie_balon],
                    [
                        'id_ubicacion_actual' => $request->id_ubicacion_servicio,
                        'estado_ubicacion' => 'En uso',
                        'fecha_ingreso' => now(),
                    ]
                );

                HistorialUbicacionBalon::create([
                    'serie_balon' => $balonLleno->serie_balon,
                    'id_ubicacion_origen' => $origenLleno,
                    'id_ubicacion_destino' => $request->id_ubicacion_servicio,
                    'tipo_movimiento' => 'Entrega',
                    'fecha_movimiento' => now(),
                    'id_responsable' => $usuario->ID_Personal,
                ]);

                $recibido = false;

                if ($balonVacio) {
                    $recibido = true;

                    $balonVacio->id_estado = EstadoBalon::idDe('Vacio');
                    $balonVacio->id_ubicacion_actual = null;
                    $balonVacio->id_usuario_ultima_modificacion = $usuario->ID_Personal;
                    $balonVacio->fecha_ultima_modificacion = now();
                    $balonVacio->save();

                    UbicacionActual::where('serie_balon', $balonVacio->serie_balon)->delete();

                    HistorialUbicacionBalon::create([
                        'serie_balon' => $balonVacio->serie_balon,
                        'id_ubicacion_origen' => $origenVacio,
                        'id_ubicacion_destino' => null,
                        'tipo_movimiento' => 'Recojo',
                        'fecha_movimiento' => now(),
                        'id_responsable' => $usuario->ID_Personal,
                    ]);
                }

                $detalle = DetalleConsumoPiso::create([
                    'id_informe' => $informe->id_informe,
                    'id_ubicacion_servicio' => $request->id_ubicacion_servicio,
                    'serie_balon_lleno' => $balonLleno->serie_balon,
                    'id_ubicacion_balon_lleno' => $origenLleno,
                    'volumen_m3' => $volumenM3,
                    'serie_balon_vacio' => $balonVacio?->serie_balon,
                    'id_ubicacion_balon_vacio' => $origenVacio,
                    'prefactura' => $request->prefactura,
                    'id_personal_entrega' => $usuario->ID_Personal,
                    'id_personal_recepciona' => $request->id_personal_recepciona,
                    'firma_recepciona' => $request->firma_recepciona,
                    'hora_entrega' => now()->format('H:i:s'),
                    'hora_recepcion' => $balonVacio ? now()->format('H:i:s') : null,
                    'estado' => 'Completado',
                    'observaciones' => $request->observaciones,
                    'fecha_registro' => now(),
                    'id_usuario_registro' => $usuario->ID_Personal,
                ]);

                $informe->increment('total_balones_entregados', 1);

                if ($recibido) {
                    $informe->increment('total_balones_recibidos', 1);
                }

                $informe->increment('volumen_total_m3', $volumenM3);

                return $detalle;
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'No se pudo registrar el intercambio', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Intercambio registrado correctamente',
            'detalle' => $detalle,
        ], 200);
    }
}
