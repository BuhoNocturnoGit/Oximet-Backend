<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_sis_diario', function (Blueprint $table) {
            $table->increments('id_reporte');
            $table->date('fecha')->unique();
            $table->unsignedInteger('id_responsable');
            $table->unsignedInteger('total_atenciones')->default(0);
            $table->decimal('total_m3_sis', 10, 2)->default(0);
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
        Schema::dropIfExists('reporte_sis_diario');
    }
};
