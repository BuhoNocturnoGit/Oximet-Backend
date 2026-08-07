<?php

namespace App\Http\Controllers;

use App\Models\ConsumoBancadasDiario;
use App\Models\DetalleConsumoPiso;
use App\Models\FichaLlenadoDiario;
use App\Models\FichaRenoxiDiario;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private const FUENTES = ['Nitrogeno', 'Bancadas', 'Planta', 'Pisos'];

    private const PERIODOS = ['Dia', 'Semana', 'Mes', 'Anio'];

    private const ROLES_LARGOS = [1, 2];

    public function obtenerConsolidado(Request $request)
    {
        $fuente = $request->input('fuente_datos');
        $periodo = $request->input('periodo_analisis');
        $fecha = $request->input('fecha') ?? now()->toDateString();

        if (! in_array($fuente, self::FUENTES, true)) {
            return response()->json(['message' => 'La fuente de datos no es válida'], 422);
        }

        if (! in_array($periodo, self::PERIODOS, true)) {
            return response()->json(['message' => 'El período de análisis no es válido'], 422);
        }

        if (in_array($periodo, ['Semana', 'Mes', 'Anio'], true) && ! in_array($request->user()->id_rol, self::ROLES_LARGOS, true)) {
            return response()->json(['message' => 'No tiene permisos para consultar este período'], 403);
        }

        [$desde, $hasta] = $this->rangoFecha($periodo, $fecha);

        $data = match ($fuente) {
            'Nitrogeno' => $this->consolidarNitrogeno($periodo, $desde, $hasta),
            'Bancadas' => $this->consolidarBancadas($periodo, $desde, $hasta),
            'Planta' => $this->consolidarPlanta($periodo, $desde, $hasta),
            'Pisos' => $this->consolidarPisos($desde, $hasta),
        };

        return response()->json($data);
    }

    private function rangoFecha(string $periodo, string $fecha): array
    {
        $base = Carbon::parse($fecha);

        return match ($periodo) {
            'Dia' => [$base->copy()->startOfDay()->toDateString(), $base->copy()->endOfDay()->toDateString()],
            'Semana' => [$base->copy()->subDays(6)->toDateString(), $base->copy()->toDateString()],
            'Mes' => [$base->copy()->startOfMonth()->toDateString(), $base->copy()->endOfMonth()->toDateString()],
            'Anio' => [$base->copy()->startOfYear()->toDateString(), $base->copy()->endOfYear()->toDateString()],
        };
    }

    private function grupoTemporal(string $periodo): string
    {
        return $periodo === 'Anio' ? "strftime('%Y-%m', fecha)" : "strftime('%Y-%m-%d', fecha)";
    }

    private function consolidarNitrogeno(string $periodo, string $desde, string $hasta): array
    {
        $filas = FichaRenoxiDiario::whereBetween('fecha', [$desde, $hasta])
            ->where('estado', 'Cerrado')
            ->selectRaw("{$this->grupoTemporal($periodo)} as x")
            ->selectRaw('SUM(total_ingresos_praxair_m3) as recarga')
            ->selectRaw('SUM(total_egresos_sis_m3 + total_egresos_bancadas_m3 + total_mermas_trasegado_m3) as consumo')
            ->groupBy('x')
            ->orderBy('x')
            ->get();

        $kpis = [
            'total_recarga_m3' => round((float) $filas->sum('recarga'), 2),
            'total_consumo_m3' => round((float) $filas->sum('consumo'), 2),
        ];

        $graficos = $filas->map(fn ($f) => [
            'x' => $f->x,
            'y' => round((float) $f->consumo, 2),
        ])->values();

        return ['kpis' => $kpis, 'graficos' => $graficos];
    }

    private function consolidarBancadas(string $periodo, string $desde, string $hasta): array
    {
        $filas = ConsumoBancadasDiario::whereBetween('fecha', [$desde, $hasta])
            ->where('estado', 'Cerrado')
            ->selectRaw("{$this->grupoTemporal($periodo)} as x")
            ->select('bancada')
            ->selectRaw('SUM(total_psi) as psi')
            ->selectRaw('SUM(total_m3_consumidos) as m3')
            ->groupBy('x', 'bancada')
            ->orderBy('x')
            ->get();

        $kpis = [
            'total_psi' => round((float) $filas->sum('psi'), 2),
            'total_m3_consumidos' => round((float) $filas->sum('m3'), 2),
        ];

        $graficos = $filas->map(fn ($f) => [
            'x' => "Bancada {$f->bancada}",
            'y' => round((float) $f->m3, 2),
        ])->values();

        return ['kpis' => $kpis, 'graficos' => $graficos];
    }

    private function consolidarPlanta(string $periodo, string $desde, string $hasta): array
    {
        $filas = FichaLlenadoDiario::whereBetween('fecha', [$desde, $hasta])
            ->where('estado', 'Cerrado')
            ->selectRaw("{$this->grupoTemporal($periodo)} as x")
            ->selectRaw('SUM(total_balones_dia) as balones')
            ->selectRaw('SUM(presion_final_psi) as psi')
            ->selectRaw('SUM(total_m3_producidos_dia) as m3')
            ->selectRaw('AVG(pureza_final) as pureza')
            ->groupBy('x')
            ->orderBy('x')
            ->get();

        $kpis = [
            'total_balones' => (int) $filas->sum('balones'),
            'total_presion_psi' => round((float) $filas->sum('psi'), 2),
            'total_volumen_m3' => round((float) $filas->sum('m3'), 2),
            'promedio_pureza' => round((float) $filas->avg('pureza'), 2),
        ];

        $graficos = $filas->map(fn ($f) => [
            'x' => $f->x,
            'y' => round((float) $f->m3, 2),
        ])->values();

        return ['kpis' => $kpis, 'graficos' => $graficos];
    }

    private function consolidarPisos(string $desde, string $hasta): array
    {
        $filas = DetalleConsumoPiso::query()
            ->join('informe_presion_piso_diario', 'informe_presion_piso_diario.id_informe', '=', 'detalle_consumo_piso.id_informe')
            ->join('ubicacion', 'ubicacion.id_ubicacion', '=', 'detalle_consumo_piso.id_ubicacion_servicio')
            ->whereBetween('informe_presion_piso_diario.fecha', [$desde, $hasta])
            ->where('informe_presion_piso_diario.estado', 'Completado')
            ->select('ubicacion.nombre as servicio')
            ->selectRaw('SUM(detalle_consumo_piso.volumen_m3) as m3')
            ->selectRaw('COUNT(*) as balones')
            ->groupBy('ubicacion.nombre')
            ->orderBy('servicio')
            ->get();

        $kpis = [
            'total_consumo_m3' => round((float) $filas->sum('m3'), 2),
            'total_balones' => (int) $filas->sum('balones'),
        ];

        $graficos = $filas->map(fn ($f) => [
            'x' => $f->servicio,
            'y' => round((float) $f->m3, 2),
        ])->values();

        return ['kpis' => $kpis, 'graficos' => $graficos];
    }
}
