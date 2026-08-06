<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_consumo_piso', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_informe');
            $table->unsignedInteger('id_ubicacion_servicio');
            $table->string('serie_balon_lleno', 50);
            $table->unsignedInteger('id_ubicacion_balon_lleno')->nullable();
            $table->decimal('volumen_m3', 10, 2);
            $table->string('serie_balon_vacio', 50)->nullable();
            $table->unsignedInteger('id_ubicacion_balon_vacio')->nullable();
            $table->string('prefactura', 50)->nullable();
            $table->unsignedInteger('id_personal_entrega');
            $table->unsignedInteger('id_personal_recepciona')->nullable();
            $table->string('firma_recepciona', 255)->nullable();
            $table->time('hora_entrega')->nullable();
            $table->time('hora_recepcion')->nullable();
            $table->string('estado', 20)->default('Completado');
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_registro');
            $table->unsignedInteger('id_usuario_registro');

            $table->foreign('id_informe')
                ->references('id_informe')
                ->on('informe_presion_piso_diario')
                ->restrictOnDelete();

            $table->foreign('id_ubicacion_servicio')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->restrictOnDelete();

            $table->foreign('serie_balon_lleno')
                ->references('serie_balon')
                ->on('balones')
                ->restrictOnDelete();

            $table->foreign('serie_balon_vacio')
                ->references('serie_balon')
                ->on('balones')
                ->nullOnDelete();

            $table->foreign('id_personal_entrega')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();

            $table->foreign('id_personal_recepciona')
                ->references('ID_Personal')
                ->on('personal')
                ->nullOnDelete();

            $table->foreign('id_usuario_registro')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_consumo_piso');
    }
};
