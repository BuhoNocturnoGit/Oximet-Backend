<?php

namespace Tests\Feature;

use App\Models\Balon;
use App\Models\HistorialUbicacionBalon;
use App\Models\InformePresionPisoDiario;
use App\Models\Personal;
use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DistribucionInformeTest extends TestCase
{
    use RefreshDatabase;

    private Personal $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Personal::factory()->administrador()->create();
        Sanctum::actingAs($this->usuario);
    }

    private function crearBalon(string $serie, string $estado = 'Lleno', ?int $ubicacion = null, float $capacidad = 10): Balon
    {
        return Balon::create([
            'serie_balon' => $serie,
            'capacidad_m3' => $capacidad,
            'presion_actual_psi' => 2000,
            'cargas_utilizadas' => 0,
            'max_cargas' => 3,
            'id_estado' => $estado,
            'id_ubicacion_actual' => $ubicacion,
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);
    }

    public function test_iniciar_recorrido_crea_cabecera_con_totales_en_cero(): void
    {
        $response = $this->postJson('/api/distribucion/informes/iniciar', [
            'id_responsable' => $this->usuario->ID_Personal,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'estado' => 'En Proceso',
                'total_balones_entregados' => 0,
                'total_balones_recibidos' => 0,
                'volumen_total_m3' => 0,
            ]);

        $this->assertDatabaseHas('informe_presion_piso_diario', [
            'id_informe' => $response->json('id_informe'),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);
    }

    public function test_cerrar_recorrido_actualiza_estado_y_auditoria(): void
    {
        $informe = InformePresionPisoDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $this->usuario->ID_Personal,
            'estado' => 'En Proceso',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $response = $this->putJson("/api/distribucion/informes/{$informe->id_informe}/cerrar");

        $response->assertStatus(200)
            ->assertJsonPath('informe.estado', 'Completado');

        $this->assertDatabaseHas('informe_presion_piso_diario', [
            'id_informe' => $informe->id_informe,
            'estado' => 'Completado',
            'id_usuario_modificacion' => $this->usuario->ID_Personal,
        ]);
    }

    public function test_intercambio_completo_swap(): void
    {
        $informe = InformePresionPisoDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $this->usuario->ID_Personal,
            'estado' => 'En Proceso',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $planta = Ubicacion::create([
            'nombre' => 'Planta',
            'piso' => 'P1',
            'estado' => 'Activo',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $uci = Ubicacion::create([
            'nombre' => 'UCI',
            'piso' => 'P2',
            'estado' => 'Activo',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $balonLleno = $this->crearBalon('B-001', 'Lleno', $planta->id_ubicacion);
        $balonVacio = $this->crearBalon('B-002', 'Vacio', $uci->id_ubicacion);

        $response = $this->postJson("/api/distribucion/informes/{$informe->id_informe}/intercambio", [
            'id_ubicacion_servicio' => $uci->id_ubicacion,
            'serie_balon_lleno' => 'B-001',
            'serie_balon_vacio' => 'B-002',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('balones', [
            'serie_balon' => 'B-001',
            'id_estado' => 'En uso',
            'id_ubicacion_actual' => $uci->id_ubicacion,
        ]);

        $this->assertDatabaseHas('balones', [
            'serie_balon' => 'B-002',
            'id_estado' => 'Vacio',
            'id_ubicacion_actual' => null,
        ]);

        $this->assertDatabaseHas('ubicacion_actual', [
            'serie_balon' => 'B-001',
            'id_ubicacion_actual' => $uci->id_ubicacion,
            'estado_ubicacion' => 'En uso',
        ]);

        $this->assertDatabaseMissing('ubicacion_actual', ['serie_balon' => 'B-002']);

        $this->assertDatabaseHas('detalle_consumo_piso', [
            'id_informe' => $informe->id_informe,
            'serie_balon_lleno' => 'B-001',
            'serie_balon_vacio' => 'B-002',
            'volumen_m3' => 10.00,
        ]);

        $this->assertSame(2, HistorialUbicacionBalon::count());

        $this->assertDatabaseHas('informe_presion_piso_diario', [
            'id_informe' => $informe->id_informe,
            'total_balones_entregados' => 1,
            'total_balones_recibidos' => 1,
            'volumen_total_m3' => 10.00,
        ]);
    }

    public function test_entrega_simple_sin_balon_vacio(): void
    {
        $informe = InformePresionPisoDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $this->usuario->ID_Personal,
            'estado' => 'En Proceso',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $uci = Ubicacion::create([
            'nombre' => 'Emergencia',
            'piso' => 'P1',
            'estado' => 'Activo',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->crearBalon('B-010', 'Lleno', null);

        $response = $this->postJson("/api/distribucion/informes/{$informe->id_informe}/intercambio", [
            'id_ubicacion_servicio' => $uci->id_ubicacion,
            'serie_balon_lleno' => 'B-010',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('detalle_consumo_piso', [
            'serie_balon_lleno' => 'B-010',
            'serie_balon_vacio' => null,
        ]);

        $this->assertDatabaseHas('informe_presion_piso_diario', [
            'id_informe' => $informe->id_informe,
            'total_balones_entregados' => 1,
            'total_balones_recibidos' => 0,
        ]);
    }

    public function test_actualizacion_de_cabecera_con_dos_entregas(): void
    {
        $informe = InformePresionPisoDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $this->usuario->ID_Personal,
            'estado' => 'En Proceso',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $servicio = Ubicacion::create([
            'nombre' => 'UCI',
            'piso' => 'P2',
            'estado' => 'Activo',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->crearBalon('B-101', 'Lleno', null, 10);
        $this->crearBalon('B-102', 'Lleno', null, 10);

        $this->postJson("/api/distribucion/informes/{$informe->id_informe}/intercambio", [
            'id_ubicacion_servicio' => $servicio->id_ubicacion,
            'serie_balon_lleno' => 'B-101',
        ])->assertStatus(200);

        $this->postJson("/api/distribucion/informes/{$informe->id_informe}/intercambio", [
            'id_ubicacion_servicio' => $servicio->id_ubicacion,
            'serie_balon_lleno' => 'B-102',
        ])->assertStatus(200);

        $this->assertDatabaseHas('informe_presion_piso_diario', [
            'id_informe' => $informe->id_informe,
            'total_balones_entregados' => 2,
            'volumen_total_m3' => 20.00,
        ]);
    }
}
