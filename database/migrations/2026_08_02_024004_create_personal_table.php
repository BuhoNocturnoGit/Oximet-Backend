<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('users');

        Schema::create('personal', function (Blueprint $table) {
            $table->increments('ID_Personal');
            $table->string('Nombre', 100);
            $table->string('Apellidos', 100);
            $table->string('Correo', 100)->unique();
            $table->string('Contrasena', 255);
            $table->string('Estado_Registro', 20)->default('Pendiente');
            $table->string('Rol_Solicitado', 20)->nullable();
            $table->string('Rol_Asignado', 20)->nullable();
            $table->string('Rol', 20)->nullable();
            $table->string('Telefono', 15);
            $table->string('Firma_Digital', 255)->nullable();
            $table->boolean('Activo')->default(0);
            $table->boolean('Bloqueado')->default(0);
            $table->dateTime('Fecha_Ultimo_Acceso')->nullable();
            $table->dateTime('Fecha_Solicitud')->useCurrent();
            $table->unsignedInteger('ID_Admin_Aprobador')->nullable();
            $table->dateTime('Fecha_Aprobacion')->nullable();
            $table->text('Comentarios_Aprobacion')->nullable();
            $table->unsignedInteger('ID_Usuario_Creacion')->nullable();
            
            $table->dateTime('Fecha_Creacion')->useCurrent();
            $table->unsignedInteger('ID_Usuario_Modificacion')->nullable();
            $table->dateTime('Fecha_Modificacion')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
