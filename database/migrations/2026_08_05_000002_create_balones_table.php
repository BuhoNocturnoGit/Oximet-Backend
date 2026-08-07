<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balones', function (Blueprint $table) {
            $table->string('serie_balon', 50)->primary();
            $table->string('codigo_barras', 50)->nullable();
            $table->unsignedInteger('id_tipo');
            $table->unsignedInteger('id_estado');
            $table->string('origen', 20);
            $table->string('propiedad', 20)->default('Propio');
            $table->string('id_proveedor', 11)->nullable();
            $table->decimal('capacidad_m3', 10, 2)->nullable();
            $table->date('fecha_fabricacion');
            $table->date('fecha_vencimiento');
            $table->date('fecha_prueba_hidrostatica')->nullable();
            $table->date('fecha_cambio_valvula')->nullable();
            $table->string('numero_lote_praxair', 50)->nullable();
            $table->string('guia_remision_praxair', 50)->nullable();
            $table->integer('max_cargas')->default(3);
            $table->integer('cargas_utilizadas')->default(0);
            $table->integer('cargas_disponibles')->nullable();
            $table->string('estado_operativo', 20)->default('Operativo');
            $table->string('condicion', 10)->default('Nuevo');
            $table->text('observaciones')->nullable();
            $table->decimal('presion_actual_psi', 10, 2)->nullable();
            $table->decimal('o2_disponible_m3', 10, 2)->nullable();
            $table->decimal('pureza_actual', 5, 2)->nullable();
            $table->integer('numero_recargas_total')->default(0);
            $table->dateTime('fecha_ultima_recarga')->nullable();
            $table->dateTime('fecha_ultimo_mantenimiento')->nullable();
            $table->unsignedInteger('id_ubicacion_actual')->nullable();
            $table->dateTime('fecha_registro')->useCurrent();
            $table->unsignedInteger('id_usuario_registro')->nullable();
            $table->unsignedInteger('id_usuario_ultima_modificacion')->nullable();
            $table->dateTime('fecha_ultima_modificacion')->nullable();

            $table->foreign('id_estado')
                ->references('id_estado')
                ->on('estado_balon');

            $table->foreign('id_proveedor')
                ->references('id_proveedor')
                ->on('proveedors');

            $table->foreign('id_ubicacion_actual')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->nullOnDelete();

            $table->foreign('id_usuario_registro')
                ->references('ID_Personal')
                ->on('personal');

            $table->foreign('id_usuario_ultima_modificacion')
                ->references('ID_Personal')
                ->on('personal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balones');
    }
};
