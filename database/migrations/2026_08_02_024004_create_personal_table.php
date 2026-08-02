<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id('id_personal');
            $table->string('nombre', 100);
            $table->string('apellidos', 100);
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255);
            $table->string('telefono', 15);
            $table->enum('estado_registro', ['Pendiente', 'Activo', 'Rechazado', 'Bloqueado'])->default('Pendiente');
            $table->boolean('activo')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
