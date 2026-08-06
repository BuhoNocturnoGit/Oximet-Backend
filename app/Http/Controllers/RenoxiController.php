<?php

namespace App\Http\Controllers;

use App\Models\AtencionTrasegadoDiario;
use App\Models\ConsumoBancadasDiario;
use App\Models\FichaPraxairDiario;
use App\Models\FichaRenoxiDiario;
use App\Models\ReporteSisDiario;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RenoxiController extends Controller
{
    public function abrirBalanceDiario(Request $request)
    {
        $validated = $request->validate([
            'id_responsable' => 'required|integer|exists:personal,ID_Personal',
            'tanque_nivel_inicial' => 'required|numeric',
            'tanque_volumen_inicial_m3' => 'required|numeric',
        ]);

        $fechaHoy = now()->toDateString();

        if (FichaRenoxiDiario::whereDate('fecha', $fechaHoy)->exists()) {
            return response()->json(['message' => 'Ya existe un balance maestro para la fecha actual'], 422);
        }

        if (FichaRenoxiDiario::where('fecha', '<', $fechaHoy)
            ->where('estado', 'Abierto')
            ->exists()) {
            return response()->json(['message' => 'No se puede abrir un nuevo balance, el día anterior no ha sido cerrado'], 422);
        }

        $reporte = FichaRenoxiDiario::create([
            'fecha' => $fechaHoy,
            'id_responsable' => $validated['id_responsable'],
            'tanque_nivel_inicial' => $validated['tanque_nivel_inicial'],
            'tanque_volumen_inicial_m3' => $validated['tanque_volumen_inicial_m3'],
            'total_ingresos_praxair_m3' => 0,
            'total_egresos_sis_m3' => 0,
            'total_egresos_bancadas_m3' => 0,
            'total_mermas_trasegado_m3' => 0,
            'desviacion_calculada_m3' => 0,
            'estado' => 'Abierto',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $request->user()->ID_Personal,
        ]);

        return response()->json(['id_renoxi' => $reporte->id_renoxi], 201);
    }

    public function previsualizarConsolidado(Request $request, $id_reporte)
    {
        $reporte = FichaRenoxiDiario::findOrFail($id_reporte);

        if ($reporte->estado !== 'Abierto') {
            return response()->json(['message' => 'El balance ya no está abierto y no puede previsualizarse'], 422);
        }

        return response()->json($this->consolidarDia($reporte->fecha));
    }

    public function cerrarBalanceDiario(Request $request, $id_reporte)
    {
        $validated = $request->validate([
            'tanque_nivel_final' => 'required|numeric',
            'tanque_volumen_final_m3' => 'required|numeric',
        ]);

        $reporte = FichaRenoxiDiario::findOrFail($id_reporte);

        if ($reporte->estado === 'Cerrado') {
            return response()->json(['message' => 'El balance ya fue cerrado y no puede modificarse'], 422);
        }

        $consolidado = $this->consolidarDia($reporte->fecha);

        $volumenTeorico = (float) $reporte->tanque_volumen_inicial_m3
            + $consolidado['ingresos_praxair']
            - $consolidado['egresos_sis']
            - $consolidado['egresos_bancadas']
            - $consolidado['mermas_trasegado'];

        $desviacion = (float) $validated['tanque_volumen_final_m3'] - $volumenTeorico;

        DB::transaction(function () use ($reporte, $validated, $consolidado, $desviacion) {
            $reporte->update([
                'tanque_nivel_final' => $validated['tanque_nivel_final'],
                'tanque_volumen_final_m3' => $validated['tanque_volumen_final_m3'],
                'total_ingresos_praxair_m3' => $consolidado['ingresos_praxair'],
                'total_egresos_sis_m3' => $consolidado['egresos_sis'],
                'total_egresos_bancadas_m3' => $consolidado['egresos_bancadas'],
                'total_mermas_trasegado_m3' => $consolidado['mermas_trasegado'],
                'desviacion_calculada_m3' => round($desviacion, 2),
                'estado' => 'Cerrado',
            ]);
        });

        return response()->json([
            'id_renoxi' => $reporte->id_renoxi,
            'volumen_teorico_m3' => round($volumenTeorico, 2),
            'desviacion_calculada_m3' => round($desviacion, 2),
            'consolidado' => $consolidado,
        ]);
    }

    private function consolidarDia($fecha): array
    {
        $fecha = $fecha instanceof CarbonInterface ? $fecha->toDateString() : (string) $fecha;

        $ingresosPraxair = (float) FichaPraxairDiario::whereDate('fecha', $fecha)
            ->where('estado', 'Cerrado')
            ->sum('volumen_m3');

        $egresosSis = (float) ReporteSisDiario::whereDate('fecha', $fecha)
            ->where('estado', 'Cerrado')
            ->sum('total_m3_sis');

        $egresosBancadas = (float) ConsumoBancadasDiario::whereDate('fecha', $fecha)
            ->where('estado', 'Cerrado')
            ->sum('total_m3_consumidos');

        $mermasTrasegado = (float) AtencionTrasegadoDiario::whereDate('fecha', $fecha)
            ->where('estado', 'Cerrado')
            ->sum('merma_calculada_m3');

        return [
            'ingresos_praxair' => $ingresosPraxair,
            'egresos_sis' => $egresosSis,
            'egresos_bancadas' => $egresosBancadas,
            'mermas_trasegado' => $mermasTrasegado,
            'total_salidas' => round($egresosSis + $egresosBancadas + $mermasTrasegado, 2),
        ];
    }
}
