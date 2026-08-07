<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_hospital', function (Blueprint $table) {
            $table->increments('id_servicio');
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('tipo', 50);
            $table->string('descripcion', 500)->nullable();
            $table->string('telefono_interno')->nullable();
            $table->string('jefe_servicio')->nullable();
            $table->string('email_contacto')->nullable();
            $table->integer('camas_disponibles')->nullable();
            $table->decimal('consumo_estimado_m3_dia', 10, 2)->nullable();
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
        Schema::dropIfExists('servicio_hospital');
    }
};
