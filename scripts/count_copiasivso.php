<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require 'c:/Users/Elias/Documents/sistema_sivso_2026/vendor/autoload.php';
$app = require 'c:/Users/Elias/Documents/sistema_sivso_2026/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$db = DB::connection('copiasivso');
echo 'concentrado: '.$db->table('concentrado')->count()."\n";
echo 'propuesta: '.$db->table('propuesta')->count()."\n";
foreach (['empleados', 'solicitudes_vestuario', 'productos'] as $t) {
    echo $t.': '.$db->table($t)->count()."\n";
}
