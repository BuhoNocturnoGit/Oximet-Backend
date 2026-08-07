<?php

namespace Tests\Feature;

use App\Models\Balon;
use App\Models\EstadoBalon;
use App\Models\Personal;
use App\Models\TipoBalon;
use Database\Seeders\EstadoBalonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BalonTest extends TestCase
{
    use RefreshDatabase;

    private Personal $usuario;

    private TipoBalon $tipo;

    private int $estadoLleno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Personal::factory()->administrador()->create();
        $this->tipo = TipoBalon::create([
            'capacidad_o2_m3' => '10',
            'material' => 'Aluminio',
            'modelo_valvula' => 'V-01',
            'color' => 'Verde',
            'norma' => 'ISO 9809',
            'capacidad_real_m3' => 10.00,
            'volumen_de_tanque' => 47.00,
        ]);
        $this->seed(EstadoBalonSeeder::class);
        $this->estadoLleno = EstadoBalon::idDe('Lleno');

        Sanctum::actingAs($this->usuario);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'serie_balon' => 'BAL-12345',
            'id_tipo' => $this->tipo->id_tipo,
            'id_estado' => $this->estadoLleno,
            'origen' => 'HRC',
            'fecha_fabricacion' => '2023-05-01',
            'fecha_vencimiento' => '2033-05-01',
        ], $overrides);
    }

    public function test_llave_primaria_es_una_cadena_alfanumerica(): void
    {
        $this->postJson('/api/admin/balones', $this->payload(['serie_balon' => 'BAL-12345']))
            ->assertStatus(201)
            ->assertJsonPath('serie_balon', 'BAL-12345');

        $this->assertDatabaseHas('balones', ['serie_balon' => 'BAL-12345']);
    }

    public function test_aplica_valores_por_defecto_con_solo_campos_obligatorios(): void
    {
        $this->postJson('/api/admin/balones', $this->payload())
            ->assertStatus(201);

        $this->assertDatabaseHas('balones', [
            'serie_balon' => 'BAL-12345',
            'cargas_utilizadas' => 0,
            'max_cargas' => 3,
            'cargas_disponibles' => 3,
            'propiedad' => 'Propio',
            'estado_operativo' => 'Operativo',
            'condicion' => 'Nuevo',
            'numero_recargas_total' => 0,
        ]);
    }

    public function test_rechaza_id_tipo_inexistente(): void
    {
        $this->postJson('/api/admin/balones', $this->payload(['id_tipo' => 9999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('id_tipo');

        $this->assertDatabaseCount('balones', 0);
    }

    public function test_rechaza_serie_duplicada(): void
    {
        $this->postJson('/api/admin/balones', $this->payload())
            ->assertStatus(201);

        $this->postJson('/api/admin/balones', $this->payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('serie_balon');

        $this->assertSame(1, Balon::count());
    }

    public function test_rechaza_fecha_vencimiento_anterior_a_fabricacion(): void
    {
        $this->postJson('/api/admin/balones', $this->payload([
            'fecha_fabricacion' => '2030-01-01',
            'fecha_vencimiento' => '2029-01-01',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('fecha_vencimiento');
    }

    public function test_registra_auditoria_del_usuario_autenticado(): void
    {
        $this->postJson('/api/admin/balones', $this->payload())
            ->assertStatus(201);

        $this->assertDatabaseHas('balones', [
            'serie_balon' => 'BAL-12345',
            'id_usuario_registro' => $this->usuario->ID_Personal,
        ]);
    }

    public function test_no_admin_no_puede_registrar_balones(): void
    {
        Sanctum::actingAs(Personal::factory()->create());

        $this->postJson('/api/admin/balones', $this->payload())
            ->assertStatus(403);

        $this->assertDatabaseCount('balones', 0);
    }
}
