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
        Schema::create('proveedors', function (Blueprint $table) {
            $table->string('id_proveedor', 11)->primary();
            $table->string('nombre', 100);
            $table->string('direccion', 200);
            $table->string('contacto_telefonico', 15);
            $table->string('contacto_email', 100)->nullable();
            $table->string('contacto_nombre', 100)->nullable();
            $table->string('tipo_contrato', 50)->nullable();
            $table->boolean('activo')->default(1);
            $table->dateTime('fecha_registro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedors');
    }
};
