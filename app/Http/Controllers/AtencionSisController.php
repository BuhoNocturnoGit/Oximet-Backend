<?php

namespace App\Http\Controllers;

use App\Models\AtencionSisDiario;
use App\Models\Balon;
use App\Models\Paciente;
use App\Models\ReporteSisDiario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtencionSisController extends Controller
{
    public function buscarOCrearPaciente(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|max:15',
        ]);

        $paciente = Paciente::where('dni', $request->dni)->first();

        if ($paciente) {
            return response()->json($paciente, 200);
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'nro_expediente' => 'nullable|string|max:50',
        ]);

        $paciente = Paciente::create([
            'nro_expediente' => $request->nro_expediente,
            'dni' => $request->dni,
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'tipo' => $request->tipo ?? 'SIS',
            'estado' => 'Activo',
            'fecha_registro' => now(),
        ]);

        return response()->json($paciente, 201);
    }

    public function abrirReporteDiario(Request $request)
    {
        $request->validate([
            'id_responsable' => 'required|integer',
        ]);

        $existeAbierto = ReporteSisDiario::where('estado', 'Abierto')
            ->orWhere('fecha', now()->toDateString())
            ->exists();

        if ($existeAbierto) {
            return response()->json([
                'message' => 'Ya existe un reporte abierto para el día de hoy',
            ], 422);
        }

        $reporte = ReporteSisDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $request->id_responsable,
            'total_atenciones' => 0,
            'total_m3_sis' => 0,
            'estado' => 'Abierto',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $request->user()->ID_Personal,
        ]);

        return response()->json([
            'id_reporte' => $reporte->id_reporte,
            'reporte' => $reporte,
        ], 201);
    }

    public function verificarBalonSIS($serie_balon)
    {
        $balon = Balon::find($serie_balon);

        if (! $balon) {
            return response()->json(['message' => 'Balón no encontrado'], 404);
        }

        if ($balon->cargas_utilizadas >= $balon->max_cargas) {
            return response()->json([
                'message' => 'El balón ha superado el límite de recargas SIS. Debe pasar a inspección/mantenimiento.',
            ], 403);
        }

        return response()->json($balon, 200);
    }

    public function asignarBalon(Request $request)
    {
        $request->validate([
            'id_reporte' => 'required|integer',
            'id_paciente' => 'required|integer',
            'serie_balon' => 'required|string',
            'psi_real' => 'required|numeric',
        ]);

        $reporte = ReporteSisDiario::find($request->id_reporte);

        if (! $reporte) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        if ($reporte->estado !== 'Abierto') {
            return response()->json(['message' => 'El reporte ya se encuentra cerrado'], 422);
        }

        $balon = Balon::find($request->serie_balon);

        if (! $balon) {
            return response()->json(['message' => 'Balón no encontrado'], 404);
        }

        if ($balon->cargas_utilizadas >= $balon->max_cargas) {
            return response()->json([
                'message' => 'El balón ha superado el límite de recargas SIS. Debe pasar a inspección/mantenimiento.',
            ], 403);
        }

        try {
            $atencion = DB::transaction(function () use ($request, $reporte, $balon) {
                $usuario = $request->user();

                $atencion = AtencionSisDiario::create([
                    'id_reporte' => $reporte->id_reporte,
                    'id_paciente' => $request->id_paciente,
                    'serie_balon' => $balon->serie_balon,
                    'psi_entregado' => $request->psi_real,
                    'hora_entrega' => now(),
                    'estado' => 'Entregado',
                    'id_usuario_registro' => $usuario->ID_Personal,
                ]);

                $balon->cargas_utilizadas = $balon->cargas_utilizadas + 1;
                $balon->presion_actual_psi = $request->psi_real;
                $balon->id_estado = 'En uso';
                $balon->id_usuario_modificacion = $usuario->ID_Personal;
                $balon->fecha_modificacion = now();
                $balon->save();

                $reporte->increment('total_atenciones', 1);

                return $atencion;
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'No se pudo asignar el balón', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Balón asignado correctamente',
            'atencion' => $atencion,
        ], 200);
    }

    public function devolverBalon(Request $request)
    {
        $request->validate([
            'serie_balon' => 'required|string',
        ]);

        $atencion = AtencionSisDiario::where('serie_balon', $request->serie_balon)
            ->where('estado', 'Entregado')
            ->orderByDesc('hora_entrega')
            ->first();

        if (! $atencion) {
            return response()->json(['message' => 'No existe una atención pendiente para este balón'], 404);
        }

        $balon = Balon::find($request->serie_balon);

        if (! $balon) {
            return response()->json(['message' => 'Balón no encontrado'], 404);
        }

        try {
            DB::transaction(function () use ($request, $atencion, $balon) {
                $atencion->estado = 'Devuelto';
                $atencion->hora_devolucion = now();
                $atencion->save();

                if ($balon->cargas_utilizadas >= $balon->max_cargas) {
                    $balon->id_estado = 'Mantenimiento';
                    $balon->cargas_utilizadas = 0;
                    $balon->presion_actual_psi = 0;
                } else {
                    $balon->id_estado = 'Vacio';
                    $balon->presion_actual_psi = 0;
                }

                $balon->id_usuario_modificacion = $request->user()->ID_Personal;
                $balon->fecha_modificacion = now();
                $balon->save();
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'No se pudo procesar la devolución', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Balón devuelto correctamente',
        ], 200);
    }

    public function cerrarReporteDiario(Request $request, $id_reporte)
    {
        $reporte = ReporteSisDiario::find($id_reporte);

        if (! $reporte) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        if ($reporte->estado !== 'Abierto') {
            return response()->json(['message' => 'El reporte ya se encuentra cerrado'], 422);
        }

        $totalM3 = AtencionSisDiario::query()
            ->join('balones', 'balones.serie_balon', '=', 'atencion_sis_diario.serie_balon')
            ->where('atencion_sis_diario.id_reporte', $id_reporte)
            ->where('atencion_sis_diario.estado', 'Entregado')
            ->sum('balones.capacidad_m3');

        $reporte->total_m3_sis = $totalM3;
        $reporte->estado = 'Cerrado';
        $reporte->save();

        return response()->json([
            'message' => 'Reporte cerrado correctamente',
            'consolidado' => [
                'id_reporte' => $reporte->id_reporte,
                'total_atenciones' => $reporte->total_atenciones,
                'total_m3_sis' => $totalM3,
            ],
        ], 200);
    }
}
