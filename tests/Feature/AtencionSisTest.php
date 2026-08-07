<?php

namespace Tests\Feature;

use App\Models\AtencionSisDiario;
use App\Models\Balon;
use App\Models\EstadoBalon;
use App\Models\Paciente;
use App\Models\Personal;
use App\Models\ReporteSisDiario;
use App\Models\TipoBalon;
use Database\Seeders\EstadoBalonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AtencionSisTest extends TestCase
{
    use RefreshDatabase;

    private Personal $usuario;

    private TipoBalon $tipo;

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
        Sanctum::actingAs($this->usuario);
    }

    private function crearBalon(string $serie, int $cargas = 0, int $max = 3, float $psi = 2000, string $estado = 'Lleno', float $capacidad = 10): Balon
    {
        return Balon::create([
            'serie_balon' => $serie,
            'id_tipo' => $this->tipo->id_tipo,
            'origen' => 'HRC',
            'capacidad_m3' => $capacidad,
            'fecha_fabricacion' => '2023-05-01',
            'fecha_vencimiento' => '2033-05-01',
            'presion_actual_psi' => $psi,
            'cargas_utilizadas' => $cargas,
            'max_cargas' => $max,
            'id_estado' => EstadoBalon::idDe($estado),
        ]);
    }

    private function abrirReporte(): ReporteSisDiario
    {
        return ReporteSisDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $this->usuario->ID_Personal,
            'total_atenciones' => 0,
            'total_m3_sis' => 0,
            'estado' => 'Abierto',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);
    }

    private function crearPaciente(string $dni = '12345678'): Paciente
    {
        return Paciente::create([
            'nro_expediente' => "EXP-$dni",
            'dni' => $dni,
            'nombre' => 'Juan',
            'apellidos' => 'Perez',
            'tipo' => 'SIS',
            'estado' => 'Activo',
            'fecha_registro' => now(),
        ]);
    }

    public function test_buscar_o_crear_paciente_upsert_por_dni(): void
    {
        $primera = $this->postJson('/api/sis/pacientes/buscar-crear', [
            'dni' => '99999999',
            'nombre' => 'Ana',
            'apellidos' => 'Lopez',
        ]);

        $primera->assertStatus(201);
        $id = $primera->json('id_paciente');

        $this->assertSame(1, Paciente::count());

        $segunda = $this->postJson('/api/sis/pacientes/buscar-crear', [
            'dni' => '99999999',
            'nombre' => 'Otro',
            'apellidos' => 'Nombre',
        ]);

        $segunda->assertStatus(200)
            ->assertJsonPath('id_paciente', $id);

        $this->assertSame(1, Paciente::count());
    }

    public function test_abrir_reporte_diario_es_unico(): void
    {
        $this->postJson('/api/sis/reportes/abrir', [
            'id_responsable' => $this->usuario->ID_Personal,
        ])->assertStatus(201);

        $this->postJson('/api/sis/reportes/abrir', [
            'id_responsable' => $this->usuario->ID_Personal,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Ya existe un reporte abierto para el día de hoy');

        $this->assertSame(1, ReporteSisDiario::count());
    }

    public function test_verificar_balon_bloquea_al_superar_limite(): void
    {
        $this->crearBalon('B-LIM', 3, 3);

        $this->getJson('/api/sis/balones/B-LIM/verificar')
            ->assertStatus(403)
            ->assertJsonPath('message', 'El balón ha superado el límite de recargas SIS. Debe pasar a inspección/mantenimiento.');
    }

    public function test_verificar_balon_devuelve_presion_para_autocompletar(): void
    {
        $this->crearBalon('B-OK', 1, 3, 1950.50);

        $this->getJson('/api/sis/balones/B-OK/verificar')
            ->assertStatus(200)
            ->assertJsonPath('presion_actual_psi', '1950.50');
    }

    public function test_asignar_balon_actualiza_psi_y_acumula_totales(): void
    {
        $reporte = $this->abrirReporte();
        $paciente = $this->crearPaciente();
        $this->crearBalon('B-OVR', 0, 3, 2000);

        $response = $this->postJson('/api/sis/atenciones/asignar', [
            'id_reporte' => $reporte->id_reporte,
            'id_paciente' => $paciente->id_paciente,
            'serie_balon' => 'B-OVR',
            'psi_real' => 1800,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('balones', [
            'serie_balon' => 'B-OVR',
            'cargas_utilizadas' => 1,
            'presion_actual_psi' => 1800,
            'id_estado' => EstadoBalon::idDe('En uso'),
        ]);

        $this->assertDatabaseHas('reporte_sis_diario', [
            'id_reporte' => $reporte->id_reporte,
            'total_atenciones' => 1,
        ]);

        $this->assertDatabaseHas('atencion_sis_diario', [
            'serie_balon' => 'B-OVR',
            'psi_entregado' => 1800,
            'estado' => 'Entregado',
        ]);
    }

    public function test_devolucion_resetea_por_limite_de_cargas(): void
    {
        $reporte = $this->abrirReporte();
        $paciente = $this->crearPaciente();
        $balon = $this->crearBalon('B-MANT', 3, 3, 1800, 'En uso');

        AtencionSisDiario::create([
            'id_reporte' => $reporte->id_reporte,
            'id_paciente' => $paciente->id_paciente,
            'serie_balon' => $balon->serie_balon,
            'psi_entregado' => 1800,
            'hora_entrega' => now(),
            'estado' => 'Entregado',
            'id_usuario_registro' => $this->usuario->ID_Personal,
        ]);

        $this->postJson('/api/sis/atenciones/devolver', [
            'serie_balon' => 'B-MANT',
        ])->assertStatus(200);

        $this->assertDatabaseHas('atencion_sis_diario', [
            'serie_balon' => 'B-MANT',
            'estado' => 'Devuelto',
        ]);

        $this->assertDatabaseHas('balones', [
            'serie_balon' => 'B-MANT',
            'id_estado' => EstadoBalon::idDe('En mantenimiento'),
            'cargas_utilizadas' => 0,
            'presion_actual_psi' => 0,
        ]);
    }

    public function test_devolucion_regular_conserva_cargas(): void
    {
        $reporte = $this->abrirReporte();
        $paciente = $this->crearPaciente();
        $balon = $this->crearBalon('B-VAC', 1, 3, 1700, 'En uso');

        AtencionSisDiario::create([
            'id_reporte' => $reporte->id_reporte,
            'id_paciente' => $paciente->id_paciente,
            'serie_balon' => $balon->serie_balon,
            'psi_entregado' => 1700,
            'hora_entrega' => now(),
            'estado' => 'Entregado',
            'id_usuario_registro' => $this->usuario->ID_Personal,
        ]);

        $this->postJson('/api/sis/atenciones/devolver', [
            'serie_balon' => 'B-VAC',
        ])->assertStatus(200);

        $this->assertDatabaseHas('balones', [
            'serie_balon' => 'B-VAC',
            'id_estado' => EstadoBalon::idDe('Vacio'),
            'cargas_utilizadas' => 1,
            'presion_actual_psi' => 0,
        ]);
    }

    public function test_cierre_diario_consolida_volumen(): void
    {
        $reporte = $this->abrirReporte();
        $paciente = $this->crearPaciente();

        foreach (['B-C1', 'B-C2', 'B-C3'] as $i => $serie) {
            $balon = $this->crearBalon($serie, 0, 3, 2000, 'En uso', 10);
            AtencionSisDiario::create([
                'id_reporte' => $reporte->id_reporte,
                'id_paciente' => $paciente->id_paciente,
                'serie_balon' => $serie,
                'psi_entregado' => 2000,
                'hora_entrega' => now(),
                'estado' => 'Entregado',
                'id_usuario_registro' => $this->usuario->ID_Personal,
            ]);
        }

        $response = $this->putJson("/api/sis/reportes/{$reporte->id_reporte}/cerrar");

        $response->assertStatus(200)
            ->assertJsonPath('consolidado.total_m3_sis', 30);

        $this->assertDatabaseHas('reporte_sis_diario', [
            'id_reporte' => $reporte->id_reporte,
            'total_m3_sis' => 30.00,
            'estado' => 'Cerrado',
        ]);
    }

    public function test_no_se_puede_cerrar_un_reporte_ya_cerrado(): void
    {
        $reporte = ReporteSisDiario::create([
            'fecha' => now()->toDateString(),
            'id_responsable' => $this->usuario->ID_Personal,
            'estado' => 'Cerrado',
            'fecha_creacion' => now(),
            'id_usuario_creacion' => $this->usuario->ID_Personal,
        ]);

        $this->putJson("/api/sis/reportes/{$reporte->id_reporte}/cerrar")
            ->assertStatus(422);
    }
}
