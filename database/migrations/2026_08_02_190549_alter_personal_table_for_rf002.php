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
        Schema::table('personal', function (Blueprint $table) {
            $table->dropColumn(['Estado_Registro', 'Rol_Solicitado', 'Rol_Asignado', 'Rol', 'Activo', 'Bloqueado']);
            $table->unsignedInteger('id_rol')->default(3)->after('Contrasena');
            $table->string('estado', 20)->default('Activo')->after('id_rol');
            $table->foreign('id_rol')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
            $table->dropColumn(['id_rol', 'estado']);
            
            $table->string('Estado_Registro', 20)->default('Pendiente');
            $table->string('Rol_Solicitado', 20)->nullable();
            $table->string('Rol_Asignado', 20)->nullable();
            $table->string('Rol', 20)->nullable();
            $table->boolean('Activo')->default(0);
            $table->boolean('Bloqueado')->default(0);
        });
    }
};
