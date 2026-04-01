<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependencia', function (Blueprint $table) {
            $table->unsignedInteger('ur')->primary();
            $table->string('nombre');
            $table->string('nombre_corto', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependencia');
    }
};
