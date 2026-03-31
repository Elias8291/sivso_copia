<?php

return [
    'ejercicio_actual' => (int) env('SIVSO_EJERCICIO', 2025),

    /**
     * Conexión de Laravel para datos SIVSO (modelos, migraciones, seeders CSV). Igual base que DB_*.
     */
    'csv_snapshot_connection' => env('SIVSO_DATA_CONNECTION', env('SIVSO_CSV_SNAPSHOT_CONNECTION', 'copiasivso')),

    /**
     * Conexión MySQL de solo lectura para tablas legacy (concentrado, propuesta, dependences, delegacion, delegado).
     * Por defecto «sivso_legacy_source» apunta a SIVSO_LEGACY_SOURCE_DATABASE o, si no se define, a la misma DB que DB_*.
     */
    'legacy_source_connection' => env('SIVSO_LEGACY_SOURCE_CONNECTION', 'sivso_legacy_source'),

    /**
     * Si tras reconciliar por tabla legacy / pivote único / usuario+NUE aún hay varias delegaciones
     * con la misma UR para la dependencia del empleado, asigna la delegación con id mínimo (orden estable).
     * Por defecto true para que delegacion_id no quede vacío sin tabla legacy; en producción con
     * varias subdelegaciones reales, pon SIVSO_DELEGACION_RECONCILE_FALLBACK_MIN_ID=false y usa «delegacion» legacy.
     */
    'delegacion_reconcile_fallback_min_id' => filter_var(
        env('SIVSO_DELEGACION_RECONCILE_FALLBACK_MIN_ID', 'true'),
        FILTER_VALIDATE_BOOLEAN
    ),
];
