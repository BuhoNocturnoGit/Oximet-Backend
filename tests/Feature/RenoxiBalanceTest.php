<?php

namespace Tests\Feature;

use App\Models\AtencionTrasegadoDiario;
use App\Models\ConsumoBancadasDiario;
use App\Models\FichaPraxairDiario;
use App\Models\FichaRenoxiDiario;
use App\Models\Personal;
use App\Models\ReporteSisDiario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RenoxiBalanceTest extends TestCase
{
    use RefreshDatabase;

    private Personal $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Personal::factory()->supervisor()->create();
        Sanctum::actingAs($this->usuario);
    }

    private function abrirBalance(): int
    {
        $response = $this->postJson('/api/renoxi/balances/abrir', [
            'id_responsable' => $this->usuario->ID_Personal,
            'tanque_nivel_inicial' => 100,
            'tanque_volumen_inicial_m3' => 1000,
        ]);

        $response->assertStatus(201);

        return $response->json('id_renoxi');
    }

    public function test_abrir_balance_retorna_201_y_el_id(): void
    {
        $id = $this->abrirBalance();

        $this->assertDatabaseHas('ficha_renoxi_diario', [
            'id_renoxi' => $id,
            'estado' => 'Abierto',
            'tanque_volumen_inicial_m3' => 1000,
            'total_ingresos_praxair_m3' => 0,
            'desviacion_calculada_m3' => 0,
        ]);
    }

    public function test_prevencion_de_duplicidad_mismo_dia(): void
    {
        $this->abrirBalance();

        $this->postJson('/api/renoxi/balances/abrir', [
            'id_responsable' => $this->usuario->ID_Personal,
            'tanque_nivel_inicial' => 100,
            'tanque_volumen_inicial_m3' => 1000,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Ya existe un balance maestro para la fecha actual');
    }

    public function test_arrastre_de_cierre_bloquea_apertura(): void
    {
        FichaRenoxiDiario::create([
            'fecha' => now()->subDay()->toDateString(),
            'id_responsable' => $this->usuario->ID_Personal,
            'tanque_nivel_inicial' => 100,
            'tanque_volumen_inicial_m3' => 1000,
            'estado' => 'Abierto',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->postJson('/api/renoxi/balances/abrir', [
            'id_responsable' => $this->usuario->ID_Personal,
            'tanque_nivel_inicial' => 100,
            'tanque_volumen_inicial_m3' => 1000,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'No se puede abrir un nuevo balance, el día anterior no ha sido cerrado');
    }

    public function test_pre_cierre_excluye_documentos_abiertos_y_suma_exacta(): void
    {
        $id = $this->abrirBalance();
        $fecha = now()->toDateString();

        FichaPraxairDiario::create([
            'fecha' => $fecha, 'estado' => 'Cerrado', 'volumen_m3' => 50,
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);
        FichaPraxairDiario::create([
            'fecha' => $fecha, 'estado' => 'Abierto', 'volumen_m3' => 999,
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        ReporteSisDiario::create([
            'fecha' => $fecha, 'id_responsable' => $this->usuario->ID_Personal,
            'total_m3_sis' => 10, 'estado' => 'Cerrado',
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        ConsumoBancadasDiario::create([
            'fecha' => $fecha, 'estado' => 'Cerrado', 'total_m3_consumidos' => 15.5,
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        AtencionTrasegadoDiario::create([
            'fecha' => $fecha, 'estado' => 'Cerrado', 'merma_calculada_m3' => 2.5,
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->getJson("/api/renoxi/balances/{$id}/pre-cierre")
            ->assertStatus(200)
            ->assertJsonPath('ingresos_praxair', 50)
            ->assertJsonPath('egresos_sis', 10)
            ->assertJsonPath('egresos_bancadas', 15.5)
            ->assertJsonPath('mermas_trasegado', 2.5)
            ->assertJsonPath('total_salidas', 28);
    }

    public function test_cierre_cuadre_perfecto_desviacion_cero(): void
    {
        $id = $this->abrirBalance();
        $fecha = now()->toDateString();

        ReporteSisDiario::create([
            'fecha' => $fecha, 'id_responsable' => $this->usuario->ID_Personal,
            'total_m3_sis' => 200, 'estado' => 'Cerrado',
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);
        AtencionTrasegadoDiario::create([
            'fecha' => $fecha, 'estado' => 'Cerrado', 'merma_calculada_m3' => 10,
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->putJson("/api/renoxi/balances/{$id}/cerrar", [
            'tanque_nivel_final' => 79,
            'tanque_volumen_final_m3' => 790,
        ])->assertStatus(200)
            ->assertJsonPath('volumen_teorico_m3', 790)
            ->assertJsonPath('desviacion_calculada_m3', 0);

        $this->assertDatabaseHas('ficha_renoxi_diario', [
            'id_renoxi' => $id,
            'estado' => 'Cerrado',
            'total_egresos_sis_m3' => 200,
            'total_mermas_trasegado_m3' => 10,
            'desviacion_calculada_m3' => 0,
        ]);
    }

    public function test_cierre_detecta_fuga_de_oxigeno_negativa(): void
    {
        $id = $this->abrirBalance();
        $fecha = now()->toDateString();

        ReporteSisDiario::create([
            'fecha' => $fecha, 'id_responsable' => $this->usuario->ID_Personal,
            'total_m3_sis' => 200, 'estado' => 'Cerrado',
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);
        AtencionTrasegadoDiario::create([
            'fecha' => $fecha, 'estado' => 'Cerrado', 'merma_calculada_m3' => 10,
            'fecha_creacion' => now(), 'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->putJson("/api/renoxi/balances/{$id}/cerrar", [
            'tanque_nivel_final' => 75,
            'tanque_volumen_final_m3' => 750,
        ])->assertStatus(200)
            ->assertJsonPath('desviacion_calculada_m3', -40);

        $this->assertDatabaseHas('ficha_renoxi_diario', [
            'id_renoxi' => $id,
            'desviacion_calculada_m3' => -40,
        ]);
    }

    public function test_inmutabilidad_bloquea_segundo_cierre(): void
    {
        $id = $this->abrirBalance();

        $this->putJson("/api/renoxi/balances/{$id}/cerrar", [
            'tanque_nivel_final' => 79,
            'tanque_volumen_final_m3' => 790,
        ])->assertStatus(200);

        $this->putJson("/api/renoxi/balances/{$id}/cerrar", [
            'tanque_nivel_final' => 50,
            'tanque_volumen_final_m3' => 500,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'El balance ya fue cerrado y no puede modificarse');
    }

    public function test_pre_cierre_de_balance_cerrado_se_rechaza(): void
    {
        $id = $this->abrirBalance();

        $this->putJson("/api/renoxi/balances/{$id}/cerrar", [
            'tanque_nivel_final' => 79,
            'tanque_volumen_final_m3' => 790,
        ])->assertStatus(200);

        $this->getJson("/api/renoxi/balances/{$id}/pre-cierre")
            ->assertStatus(422);
    }
}
