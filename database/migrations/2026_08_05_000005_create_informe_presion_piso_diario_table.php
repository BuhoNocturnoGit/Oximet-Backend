<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informe_presion_piso_diario', function (Blueprint $table) {
            $table->increments('id_informe');
            $table->date('fecha');
            $table->unsignedInteger('id_responsable');
            $table->unsignedInteger('total_balones_entregados')->default(0);
            $table->unsignedInteger('total_balones_recibidos')->default(0);
            $table->decimal('volumen_total_m3', 10, 2)->default(0);
            $table->string('estado', 20)->default('En Proceso');
            $table->dateTime('fecha_creacion');
            $table->unsignedInteger('id_usuario_creacion');
            $table->unsignedInteger('id_usuario_modificacion')->nullable();
            $table->dateTime('fecha_modificacion')->nullable();

            $table->foreign('id_responsable')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();

            $table->foreign('id_usuario_creacion')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();

            $table->foreign('id_usuario_modificacion')
                ->references('ID_Personal')
                ->on('personal')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informe_presion_piso_diario');
    }
};
