<?php

return [
    'ejercicio_actual' => (int) env('SIVSO_EJERCICIO', 2025),

    /**
     * Conexión de Laravel para datos SIVSO (modelos, migraciones copiasivso, seeders CSV/xlsx).
     * Por defecto "copiasivso" en config/database.php apunta a DB_* si no defines COPIASIVSO_DB_*.
     */
    'csv_snapshot_connection' => env('SIVSO_DATA_CONNECTION', env('SIVSO_CSV_SNAPSHOT_CONNECTION', 'copiasivso')),
];
