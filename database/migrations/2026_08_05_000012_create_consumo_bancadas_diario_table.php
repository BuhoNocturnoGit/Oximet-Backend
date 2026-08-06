<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumo_bancadas_diario', function (Blueprint $table) {
            $table->increments('id_consumo');
            $table->date('fecha');
            $table->string('bancada', 20)->default('1');
            $table->string('estado', 20)->default('Abierto');
            $table->decimal('total_psi', 10, 2)->default(0);
            $table->decimal('total_m3_consumidos', 10, 2)->default(0);
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
        Schema::dropIfExists('consumo_bancadas_diario');
    }
};
