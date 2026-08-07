<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('balones', function (Blueprint $table) {
            $table->foreign('id_tipo')
                ->references('id_tipo')
                ->on('tipo_balon');
        });
    }

    public function down(): void
    {
        Schema::table('balones', function (Blueprint $table) {
            $table->dropForeign(['id_tipo']);
        });
    }
};
