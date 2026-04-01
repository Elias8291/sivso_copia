<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->unsignedSmallInteger('anio');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('abierto')->comment('abierto|cerrado');
            $table->timestamps();

            $table->index('anio');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodo');
    }
};
