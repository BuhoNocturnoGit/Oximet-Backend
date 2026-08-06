<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_praxair_diario', function (Blueprint $table) {
            $table->increments('id_praxair');
            $table->date('fecha');
            $table->string('estado', 20)->default('Abierto');
            $table->decimal('volumen_m3', 10, 2)->default(0);
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
        Schema::dropIfExists('ficha_praxair_diario');
    }
};
