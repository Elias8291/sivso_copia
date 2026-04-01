<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sivso', function (Blueprint $table) {
            $table->string('clave', 64)->primary();
            $table->string('valor', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sivso');
    }
};
