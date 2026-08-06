<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente', function (Blueprint $table) {
            $table->increments('id_paciente');
            $table->string('nro_expediente', 50)->nullable()->unique();
            $table->string('dni', 15)->unique();
            $table->string('nombre', 100);
            $table->string('apellidos', 100);
            $table->string('tipo', 20)->default('SIS');
            $table->string('estado', 20)->default('Activo');
            $table->dateTime('fecha_registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente');
    }
};
