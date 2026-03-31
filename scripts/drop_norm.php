<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

require 'c:/Users/Elias/Documents/sistema_sivso_2026/vendor/autoload.php';
$app = require 'c:/Users/Elias/Documents/sistema_sivso_2026/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$c = 'copiasivso';
$tables = ['solicitudes_vestuario', 'partidas_especificas', 'producto_precios', 'productos', 'cupos_presupuesto', 'partidas_por_ejercicio', 'partidas', 'empleados', 'delegado_delegacion', 'delegados', 'dependencia_delegacion', 'delegaciones', 'dependencias', 'tipos_partida_especifica'];
Schema::connection($c)->disableForeignKeyConstraints();
foreach ($tables as $t) {
    Schema::connection($c)->dropIfExists($t);
}
Schema::connection($c)->enableForeignKeyConstraints();
echo "dropped\n";
