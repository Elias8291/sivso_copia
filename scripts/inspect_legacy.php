<?php

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=bas_vestuario;charset=utf8mb4',
    'root',
    'Abisai1456',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
foreach (['presupuesto_limites', 'concentrado', 'propuesta'] as $t) {
    echo "=== {$t} ===\n";
    foreach ($pdo->query("DESCRIBE `{$t}`") as $row) {
        echo $row['Field'].' '.$row['Type']."\n";
    }
    $c = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
    echo "count: {$c}\n\n";
}
