<?php

return [
    'ejercicio_actual' => (int) env('SIVSO_EJERCICIO', 2025),

    /** Conexión donde se importa el snapshot CSV (seeders). */
    'csv_snapshot_connection' => env('SIVSO_CSV_SNAPSHOT_CONNECTION', 'copiasivso'),
];
