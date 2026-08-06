<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atencion_trasegado_diario', function (Blueprint $table) {
            $table->increments('id_atencion');
            $table->date('fecha');
            $table->string('estado', 20)->default('Abierto');
            $table->decimal('merma_calculada_m3', 10, 2)->default(0);
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
        Schema::dropIfExists('atencion_trasegado_diario');
    }
};
