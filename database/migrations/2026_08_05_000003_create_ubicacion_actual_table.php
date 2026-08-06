<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacion_actual', function (Blueprint $table) {
            $table->string('serie_balon', 50)->primary();
            $table->unsignedInteger('id_ubicacion_actual');
            $table->string('estado_ubicacion', 20)->default('En uso');
            $table->dateTime('fecha_ingreso');

            $table->foreign('serie_balon')
                ->references('serie_balon')
                ->on('balones')
                ->cascadeOnDelete();

            $table->foreign('id_ubicacion_actual')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion_actual');
    }
};
