<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atencion_sis_diario', function (Blueprint $table) {
            $table->increments('id_atencion');
            $table->unsignedInteger('id_reporte');
            $table->unsignedInteger('id_paciente');
            $table->string('serie_balon', 50);
            $table->decimal('psi_entregado', 8, 2);
            $table->dateTime('hora_entrega');
            $table->dateTime('hora_devolucion')->nullable();
            $table->string('estado', 20)->default('Entregado');
            $table->unsignedInteger('id_usuario_registro');

            $table->foreign('id_reporte')
                ->references('id_reporte')
                ->on('reporte_sis_diario')
                ->restrictOnDelete();

            $table->foreign('id_paciente')
                ->references('id_paciente')
                ->on('paciente')
                ->restrictOnDelete();

            $table->foreign('serie_balon')
                ->references('serie_balon')
                ->on('balones')
                ->restrictOnDelete();

            $table->foreign('id_usuario_registro')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atencion_sis_diario');
    }
};
