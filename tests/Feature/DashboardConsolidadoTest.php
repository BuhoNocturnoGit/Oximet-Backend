<?php

namespace Tests\Feature;

use App\Models\FichaRenoxiDiario;
use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardConsolidadoTest extends TestCase
{
    use RefreshDatabase;

    private Personal $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Personal::factory()->administrador()->create();
        Sanctum::actingAs($this->usuario);
    }

    private function crearBalanceCerrado(string $fecha, float $recarga, float $consumo): void
    {
        FichaRenoxiDiario::create([
            'fecha' => $fecha,
            'id_responsable' => $this->usuario->ID_Personal,
            'tanque_nivel_inicial' => 100,
            'tanque_volumen_inicial_m3' => 1000,
            'tanque_nivel_final' => 50,
            'tanque_volumen_final_m3' => 900,
            'total_ingresos_praxair_m3' => $recarga,
            'total_egresos_sis_m3' => $consumo,
            'total_egresos_bancadas_m3' => 0,
            'total_mermas_trasegado_m3' => 0,
            'desviacion_calculada_m3' => 0,
            'estado' => 'Cerrado',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);
    }

    public function test_agrupacion_por_mes_una_fila_por_dia(): void
    {
        $base = Carbon::now()->startOfMonth();
        $this->crearBalanceCerrado($base->copy()->toDateString(), 30, 10);
        $this->crearBalanceCerrado($base->copy()->addDay()->toDateString(), 20, 5);
        $this->crearBalanceCerrado($base->copy()->addDays(2)->toDateString(), 10, 2);

        $response = $this->getJson('/api/dashboard/consolidado?fuente_datos=Nitrogeno&periodo_analisis=Mes')
            ->assertStatus(200);

        $graficos = $response->json('graficos');

        $this->assertCount(3, $graficos);
        $this->assertLessThanOrEqual(29, count($graficos));
        $this->assertEquals(60.0, $response->json('kpis.total_recarga_m3'));
        $this->assertEquals(17.0, $response->json('kpis.total_consumo_m3'));
    }

    public function test_agrupacion_por_anio_una_fila_por_mes(): void
    {
        $anio = Carbon::now()->year;
        $this->crearBalanceCerrado("{$anio}-01-15", 10, 5);
        $this->crearBalanceCerrado("{$anio}-02-15", 20, 8);
        $this->crearBalanceCerrado("{$anio}-03-15", 30, 12);

        $response = $this->getJson('/api/dashboard/consolidado?fuente_datos=Nitrogeno&periodo_analisis=Anio')
            ->assertStatus(200);

        $graficos = $response->json('graficos');

        $this->assertCount(3, $graficos);
        $this->assertLessThanOrEqual(12, count($graficos));
        $this->assertEquals(60.0, $response->json('kpis.total_recarga_m3'));
    }

    public function test_restriccion_de_rol_operador_no_ve_periodos_largos(): void
    {
        $operador = Personal::factory()->create();
        Sanctum::actingAs($operador);

        $this->getJson('/api/dashboard/consolidado?fuente_datos=Nitrogeno&periodo_analisis=Anio')
            ->assertStatus(403);

        $this->getJson('/api/dashboard/consolidado?fuente_datos=Nitrogeno&periodo_analisis=Mes')
            ->assertStatus(403);

        $this->getJson('/api/dashboard/consolidado?fuente_datos=Nitrogeno&periodo_analisis=Dia')
            ->assertStatus(200);
    }

    public function test_supervisor_si_puede_consultar_anual(): void
    {
        $supervisor = Personal::factory()->supervisor()->create();
        Sanctum::actingAs($supervisor);

        $this->getJson('/api/dashboard/consolidado?fuente_datos=Nitrogeno&periodo_analisis=Anio')
            ->assertStatus(200);
    }

    public function test_parametros_invalidos_se_rechazan(): void
    {
        $this->getJson('/api/dashboard/consolidado?fuente_datos=Inexistente&periodo_analisis=Mes')
            ->assertStatus(422);

        $this->getJson('/api/dashboard/consolidado?fuente_datos=Nitrogeno&periodo_analisis=Siglo')
            ->assertStatus(422);
    }
}
