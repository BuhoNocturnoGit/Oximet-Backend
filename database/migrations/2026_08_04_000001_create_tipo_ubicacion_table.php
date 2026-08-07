<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_ubicacion', function (Blueprint $table) {
            $table->increments('id_tipo_ubicacion');
            $table->string('nombre_tipo', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->string('icono', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->integer('orden');
            $table->boolean('permite_balones');
            $table->boolean('permite_movimientos');
            $table->boolean('es_almacen');
            $table->boolean('es_produccion');
            $table->boolean('es_consumo');
            $table->boolean('es_mantenimiento');
            $table->boolean('es_descartado');
            $table->boolean('es_transito');
            $table->boolean('es_servicio_hospital');
            $table->integer('capacidad_default_balones')->nullable();
            $table->decimal('capacidad_default_m3', 10, 2)->nullable();
            $table->boolean('requiere_autorizacion');
            $table->string('nivel_autorizacion', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->dateTime('fecha_creacion');
            $table->unsignedInteger('id_usuario_creacion')->nullable();
            $table->dateTime('fecha_modificacion')->nullable();
            $table->unsignedInteger('id_usuario_modificacion')->nullable();
            $table->string('imagen_ruta', 255)->nullable();

            $table->foreign('id_usuario_creacion')->references('ID_Personal')->on('personal')->nullOnDelete();
            $table->foreign('id_usuario_modificacion')->references('ID_Personal')->on('personal')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_ubicacion');
    }
};
