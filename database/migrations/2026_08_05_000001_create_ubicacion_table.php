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
            $table->string('nombre', 100);
            $table->string('piso', 20)->nullable();
            $table->string('estado', 20)->default('Activo');
            $table->dateTime('fecha_creacion');
            $table->unsignedInteger('id_usuario_creacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion');
    }
};
