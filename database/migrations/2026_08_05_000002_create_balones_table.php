<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balones', function (Blueprint $table) {
            $table->string('serie_balon', 50)->primary();
            $table->decimal('capacidad_m3', 10, 2);
            $table->decimal('presion_actual_psi', 8, 2)->nullable();
            $table->unsignedInteger('cargas_utilizadas')->default(0);
            $table->unsignedInteger('max_cargas')->default(3);
            $table->string('id_estado', 20)->default('Lleno');
            $table->unsignedInteger('id_ubicacion_actual')->nullable();
            $table->dateTime('fecha_creacion');
            $table->unsignedInteger('id_usuario_creacion')->nullable();
            $table->unsignedInteger('id_usuario_modificacion')->nullable();
            $table->dateTime('fecha_modificacion')->nullable();

            $table->foreign('id_ubicacion_actual')
                ->references('id_ubicacion')
                ->on('ubicacion')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balones');
    }
};
