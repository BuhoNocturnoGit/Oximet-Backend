<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Ubicacion;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UbicacionJerarquiaTest extends TestCase
{
    use RefreshDatabase;

    private Personal $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Personal::factory()->administrador()->create();
        Sanctum::actingAs($this->usuario);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'servicio' => [
                'codigo' => 'UCI',
                'nombre' => 'Unidad de Cuidados Intensivos',
                'tipo' => 'Hospitalización',
                'jefe_servicio' => 'Dr. Pérez',
            ],
            'sub_areas' => [
                ['codigo' => 'UCI-CAMA-101', 'nombre' => 'Cama 101'],
                ['codigo' => 'UCI-CAMA-102', 'nombre' => 'Cama 102'],
                ['codigo' => 'UCI-RESP', 'nombre' => 'Respaldo'],
            ],
        ], $overrides);
    }

    public function test_rollback_total_cuando_una_cama_tiene_dato_invalido(): void
    {
        $data = $this->payload();
        $data['sub_areas'][] = ['nombre' => 'Cama 104'];

        $this->postJson('/api/admin/ubicaciones/jerarquia', $data)
            ->assertStatus(422);

        $this->assertDatabaseCount('servicio_hospital', 0);
        $this->assertDatabaseCount('ubicacion', 0);
    }

    public function test_auditoria_automatica_en_las_tres_tablas(): void
    {
        $this->postJson('/api/admin/ubicaciones/jerarquia', $this->payload())
            ->assertStatus(201);

        $this->assertDatabaseHas('servicio_hospital', [
            'codigo' => 'UCI',
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->assertDatabaseHas('tipo_ubicacion', [
            'nombre_tipo' => 'Servicio Hospital',
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->assertSame(4, Ubicacion::count());
        $this->assertSame(4, Ubicacion::where('id_usuario_creacion', $this->usuario->ID_Personal)->count());
    }

    public function test_guardado_de_json_en_camas_especiales(): void
    {
        $data = $this->payload();
        $data['sub_areas'][] = [
            'codigo' => 'UCI-CAMA-201',
            'nombre' => 'Cama 201',
            'config_json' => ['puntos_oxigeno' => 2, 'tiene_ventilador' => true],
        ];

        $response = $this->postJson('/api/admin/ubicaciones/jerarquia', $data)
            ->assertStatus(201);

        $cama = collect($response->json('sub_areas'))->firstWhere('codigo', 'UCI-CAMA-201');

        $this->assertSame(2, $cama['config_json']['puntos_oxigeno']);
        $this->assertTrue($cama['config_json']['tiene_ventilador']);

        $this->assertDatabaseHas('ubicacion', [
            'codigo' => 'UCI-CAMA-201',
            'id_ubicacion_padre' => $response->json('ubicacion_padre.id_ubicacion'),
        ]);
    }

    public function test_no_se_puede_eliminar_padre_con_hijos_por_llave_foranea(): void
    {
        $response = $this->postJson('/api/admin/ubicaciones/jerarquia', $this->payload())
            ->assertStatus(201);

        $padre = Ubicacion::find($response->json('ubicacion_padre.id_ubicacion'));

        try {
            $padre->delete();
            $this->fail('Debería lanzarse una violación de llave foránea');
        } catch (QueryException $e) {
            $this->assertStringContainsStringIgnoringCase('foreign key', $e->getMessage());
        }

        $this->assertDatabaseHas('ubicacion', ['id_ubicacion' => $padre->id_ubicacion]);
    }

    public function test_no_admin_no_puede_crear_jerarquia(): void
    {
        $operador = Personal::factory()->create();
        Sanctum::actingAs($operador);

        $this->postJson('/api/admin/ubicaciones/jerarquia', $this->payload())
            ->assertStatus(403);

        $this->assertDatabaseCount('servicio_hospital', 0);
    }
}
