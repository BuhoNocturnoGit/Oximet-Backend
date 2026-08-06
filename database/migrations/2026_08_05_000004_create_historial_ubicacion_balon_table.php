<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_ubicacion_balon', function (Blueprint $table) {
            $table->increments('id_historial');
            $table->string('serie_balon', 50);
            $table->unsignedInteger('id_ubicacion_origen')->nullable();
            $table->unsignedInteger('id_ubicacion_destino')->nullable();
            $table->string('tipo_movimiento', 30);
            $table->dateTime('fecha_movimiento');
            $table->unsignedInteger('id_responsable')->nullable();

            $table->foreign('serie_balon')
                ->references('serie_balon')
                ->on('balones')
                ->restrictOnDelete();

            $table->foreign('id_ubicacion_origen')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->nullOnDelete();

            $table->foreign('id_ubicacion_destino')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_ubicacion_balon');
    }
};
