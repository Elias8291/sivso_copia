<?php

return [
    'ejercicio_actual' => (int) env('SIVSO_EJERCICIO', 2025),

    /**
     * Conexión de Laravel para datos SIVSO (modelos, migraciones, seeders CSV). Igual base que DB_*.
     */
    'csv_snapshot_connection' => env('SIVSO_DATA_CONNECTION', env('SIVSO_CSV_SNAPSHOT_CONNECTION', 'copiasivso')),
];
