<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_balon', function (Blueprint $table) {
            $table->increments('id_tipo');
            $table->string('capacidad_o2_m3', 10);
            $table->string('material', 20);
            $table->string('modelo_valvula', 50);
            $table->string('color', 20);
            $table->string('norma', 50);
            $table->decimal('capacidad_real_m3', 10, 2);
            $table->decimal('volumen_de_tanque', 10, 2);
            $table->decimal('peso_kg', 10, 2)->nullable();
            $table->integer('altura_cm')->nullable();
            $table->string('imagen_ruta', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_balon');
    }
};
