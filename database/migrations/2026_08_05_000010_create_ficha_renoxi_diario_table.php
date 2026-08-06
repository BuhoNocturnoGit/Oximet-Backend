<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_renoxi_diario', function (Blueprint $table) {
            $table->increments('id_renoxi');
            $table->date('fecha')->unique();
            $table->unsignedInteger('id_responsable');
            $table->decimal('tanque_nivel_inicial', 10, 2);
            $table->decimal('tanque_volumen_inicial_m3', 10, 2);
            $table->decimal('tanque_nivel_final', 10, 2)->nullable();
            $table->decimal('tanque_volumen_final_m3', 10, 2)->nullable();
            $table->decimal('total_ingresos_praxair_m3', 10, 2)->default(0);
            $table->decimal('total_egresos_sis_m3', 10, 2)->default(0);
            $table->decimal('total_egresos_bancadas_m3', 10, 2)->default(0);
            $table->decimal('total_mermas_trasegado_m3', 10, 2)->default(0);
            $table->decimal('desviacion_calculada_m3', 10, 2)->default(0);
            $table->string('estado', 20)->default('Abierto');
            $table->dateTime('fecha_creacion');
            $table->unsignedInteger('id_usuario_creacion');

            $table->foreign('id_responsable')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();

            $table->foreign('id_usuario_creacion')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_renoxi_diario');
    }
};
