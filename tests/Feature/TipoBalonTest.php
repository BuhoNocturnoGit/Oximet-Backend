<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\TipoBalon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TipoBalonTest extends TestCase
{
    use RefreshDatabase;

    private array $base;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Personal::factory()->administrador()->create();
        Sanctum::actingAs($admin);

        $this->base = [
            'capacidad_o2_m3' => '10m3',
            'material' => 'Acero',
            'modelo_valvula' => 'Válvula ISO 32',
            'color' => 'Verde',
            'norma' => 'ISO 9809',
            'capacidad_real_m3' => 10.50,
            'volumen_de_tanque' => 50.00,
        ];
    }

    public function test_rechaza_volumen_de_tanque_no_numerico(): void
    {
        $payload = $this->base;
        $payload['volumen_de_tanque'] = 'abc';

        $this->postJson('/api/admin/tipos-balon', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('volumen_de_tanque');

        $this->assertDatabaseCount('tipo_balon', 0);
    }

    public function test_registra_tipo_balon_con_imagen_valida(): void
    {
        Storage::fake('public');

        $payload = $this->base;
        $payload['peso_kg'] = 60.00;
        $payload['altura_cm'] = 150;
        $payload['imagen'] = UploadedFile::fake()->image('balon.jpg');

        $response = $this->post('/api/admin/tipos-balon', $payload)
            ->assertStatus(201);

        $ruta = $response->json('imagen_ruta');

        $this->assertNotNull($ruta);
        $this->assertStringStartsWith('tipos-balon/', $ruta);
        Storage::disk('public')->assertExists($ruta);
        $this->assertDatabaseHas('tipo_balon', ['id_tipo' => $response->json('id_tipo')]);
    }

    public function test_rechaza_imagen_con_extension_invalida(): void
    {
        Storage::fake('public');

        $payload = $this->base;
        $payload['imagen'] = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $this->withHeader('Accept', 'application/json')
            ->post('/api/admin/tipos-balon', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('imagen');

        $this->assertDatabaseCount('tipo_balon', 0);
    }

    public function test_registra_tipo_balon_omitiendo_campos_opcionales(): void
    {
        $this->postJson('/api/admin/tipos-balon', $this->base)
            ->assertStatus(201);

        $this->assertDatabaseHas('tipo_balon', [
            'capacidad_o2_m3' => '10m3',
            'capacidad_real_m3' => 10.50,
            'peso_kg' => null,
            'altura_cm' => null,
            'imagen_ruta' => null,
        ]);

        $tipo = TipoBalon::first();
        $this->assertSame(50.00, (float) $tipo->volumen_de_tanque);
    }

    public function test_no_admin_no_puede_registrar_tipo_balon(): void
    {
        $operador = Personal::factory()->create();
        Sanctum::actingAs($operador);

        $this->postJson('/api/admin/tipos-balon', $this->base)
            ->assertStatus(403);

        $this->assertDatabaseCount('tipo_balon', 0);
    }
}
