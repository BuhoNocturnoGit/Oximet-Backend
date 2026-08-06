<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_llenado_diario', function (Blueprint $table) {
            $table->increments('id_ficha');
            $table->date('fecha');
            $table->string('planta', 20)->default('1');
            $table->string('estado', 20)->default('Abierto');
            $table->unsignedInteger('total_balones_dia')->default(0);
            $table->decimal('presion_final_psi', 10, 2)->default(0);
            $table->decimal('total_m3_producidos_dia', 10, 2)->default(0);
            $table->decimal('pureza_final', 10, 2)->default(0);
            $table->dateTime('fecha_creacion');
            $table->unsignedInteger('id_usuario_creacion');

            $table->foreign('id_usuario_creacion')
                ->references('ID_Personal')
                ->on('personal')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_llenado_diario');
    }
};
