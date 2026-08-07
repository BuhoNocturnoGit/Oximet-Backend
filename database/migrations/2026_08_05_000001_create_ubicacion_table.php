<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacion', function (Blueprint $table) {
            $table->increments('id_ubicacion');
            $table->unsignedInteger('id_tipo_ubicacion');
            $table->unsignedInteger('id_servicio_hospital')->nullable();
            $table->unsignedInteger('id_ubicacion_padre')->nullable();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion', 500)->nullable();
            $table->integer('capacidad_maxima_balones')->nullable();
            $table->decimal('capacidad_maxima_m3', 10, 2)->nullable();
            $table->string('estado', 20)->default('Activa');
            $table->string('edificio', 50)->nullable();
            $table->string('piso', 20)->nullable();
            $table->string('sector', 50)->nullable();
            $table->string('sala', 50)->nullable();
            $table->string('nro_cama', 20)->nullable();
            $table->string('referencia', 100)->nullable();
            $table->json('config_json')->nullable();
            $table->dateTime('fecha_creacion');
            $table->unsignedInteger('id_usuario_creacion')->nullable();
            $table->dateTime('fecha_modificacion')->nullable();
            $table->unsignedInteger('id_usuario_modificacion')->nullable();

            $table->foreign('id_tipo_ubicacion')->references('id_tipo_ubicacion')->on('tipo_ubicacion');
            $table->foreign('id_servicio_hospital')->references('id_servicio')->on('servicio_hospital')->nullOnDelete();
            $table->foreign('id_ubicacion_padre')->references('id_ubicacion')->on('ubicacion');
            $table->foreign('id_usuario_creacion')->references('ID_Personal')->on('personal')->nullOnDelete();
            $table->foreign('id_usuario_modificacion')->references('ID_Personal')->on('personal')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion');
    }
};
