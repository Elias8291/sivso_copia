<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clasificacion_bien', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('codigo', 40);
            $table->string('nombre', 120);

            $table->unique('codigo', 'uq_clasificacion_codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clasificacion_bien');
    }
};
