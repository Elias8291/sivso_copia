<?php

namespace App\Support;

use Illuminate\Database\Connection;
use RuntimeException;

/**
 * Exporta / importa filas de una base MySQL a CSV para seeders (conexión copiasivso u otra).
 */
final class CopiasivsoDatabaseCsvSnapshot
{
    public const DEFAULT_RELATIVE_DIR = 'database/seeders/csv/copiasivso';

    /**
     * Tablas que no se exportan (sistema Laravel u otras).
     *
     * @var array<int, string>
     */
    private static array $defaultExclude = ['migrations', 'failed_jobs', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches'];

    /**
     * @return array<int, string> orden de inserción respetando FKs (padres antes que hijos)
     */
    public static function topologicalInsertOrder(Connection $connection, ?array $onlyTables = null): array
    {
        $database = $connection->getDatabaseName();
        if ($database === '' || $database === null) {
            throw new RuntimeException('Conexión sin nombre de base de datos.');
        }

        $all = self::listTables($connection);
        if ($onlyTables !== null) {
            $onlySet = array_flip($onlyTables);
            $all = array_values(array_filter($all, fn (string $t) => isset($onlySet[$t])));
        }

        $allSet = array_flip($all);

        $rows = $connection->select(
            'SELECT TABLE_NAME as tbl, REFERENCED_TABLE_NAME as ref
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND REFERENCED_TABLE_SCHEMA = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $database]
        );

        $inDegree = array_fill_keys($all, 0);
        $graph = []; // P -> hijos que referencian a P
        $seenEdge = [];
        foreach ($rows as $r) {
            $child = (string) $r->tbl;
            $parent = (string) $r->ref;
            if (! isset($allSet[$child]) || ! isset($allSet[$parent]) || $child === $parent) {
                continue;
            }
            $k = $child."\0".$parent;
            if (isset($seenEdge[$k])) {
                continue;
            }
            $seenEdge[$k] = true;
            $inDegree[$child]++;
            $graph[$parent][] = $child;
        }

        $queue = [];
        foreach ($all as $t) {
            if ($inDegree[$t] === 0) {
                $queue[] = $t;
            }
        }
        sort($queue);

        $order = [];
        while ($queue !== []) {
            $u = array_shift($queue);
            $order[] = $u;
            foreach ($graph[$u] ?? [] as $v) {
                $inDegree[$v]--;
                if ($inDegree[$v] === 0) {
                    $queue[] = $v;
                }
            }
            sort($queue);
        }

        if (count($order) !== count($all)) {
            $missing = array_diff($all, $order);
            throw new RuntimeException(
                'No se pudo ordenar tablas por FK (ciclo o metadata incompleta). Revisa: '.implode(', ', $missing)
            );
        }

        return $order;
    }

    /**
     * @return array<int, string>
     */
    public static function listTables(Connection $connection): array
    {
        $database = $connection->getDatabaseName();
        $rows = $connection->select(
            'SELECT TABLE_NAME as n FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = \'BASE TABLE\' ORDER BY TABLE_NAME',
            [$database]
        );
        $names = array_map(fn ($r) => (string) $r->n, $rows);

        return array_values(array_filter($names, fn (string $t) => ! in_array($t, self::$defaultExclude, true)));
    }

    /**
     * @param  array<int, string>|null  $onlyTables
     * @param  array<int, string>  $excludeTables
     * @return array{dir: string, tables: array<int, string>, order: array<int, string>}
     */
    public static function export(
        Connection $connection,
        string $absoluteDir,
        ?array $onlyTables = null,
        array $excludeTables = [],
        int $chunkSize = 2000
    ): array {
        if (! is_dir($absoluteDir) && ! mkdir($absoluteDir, 0755, true) && ! is_dir($absoluteDir)) {
            throw new RuntimeException("No se pudo crear el directorio: {$absoluteDir}");
        }

        $tables = self::listTables($connection);
        $excludeSet = array_flip(array_merge(self::$defaultExclude, $excludeTables));
        $tables = array_values(array_filter($tables, fn (string $t) => ! isset($excludeSet[$t])));

        if ($onlyTables !== null) {
            $want = array_flip($onlyTables);
            $tables = array_values(array_filter($tables, fn (string $t) => isset($want[$t])));
        }

        $order = self::topologicalInsertOrder($connection, $tables);

        foreach ($tables as $table) {
            self::exportTable($connection, $table, $absoluteDir, $chunkSize);
        }

        $manifest = [
            'connection' => $connection->getName(),
            'database' => $connection->getDatabaseName(),
            'exported_at' => now()->toIso8601String(),
            'tables' => $tables,
            'insert_order' => $order,
        ];
        file_put_contents(
            $absoluteDir.DIRECTORY_SEPARATOR.'_manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n"
        );

        return ['dir' => $absoluteDir, 'tables' => $tables, 'order' => $order];
    }

    /**
     * @return array<int, string>
     */
    private static function primaryKeyOrderColumns(Connection $connection, string $table): array
    {
        $database = $connection->getDatabaseName();
        if ($database === '' || $database === null) {
            return [];
        }
        $rows = $connection->select(
            'SELECT COLUMN_NAME AS c FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = \'PRIMARY\'
             ORDER BY ORDINAL_POSITION',
            [$database, $table]
        );

        return array_values(array_filter(array_map(fn ($r) => (string) $r->c, $rows)));
    }

    public static function exportTable(Connection $connection, string $table, string $dir, int $chunkSize = 2000): void
    {
        $path = $dir.DIRECTORY_SEPARATOR.$table.'.csv';
        $fh = fopen($path, 'w');
        if ($fh === false) {
            throw new RuntimeException("No se pudo escribir {$path}");
        }
        fwrite($fh, "\xEF\xBB\xBF");

        $schema = $connection->getSchemaBuilder();
        $columns = $schema->getColumnListing($table);
        if ($columns === []) {
            fclose($fh);

            return;
        }
        fputcsv($fh, $columns, ',', '"', '');

        $pk = self::primaryKeyOrderColumns($connection, $table);
        $offset = 0;
        do {
            $q = $connection->table($table);
            $ordered = false;
            if ($pk !== []) {
                foreach ($pk as $col) {
                    if (in_array($col, $columns, true)) {
                        $q->orderBy($col);
                        $ordered = true;
                    }
                }
            }
            if (! $ordered) {
                if ($schema->hasColumn($table, 'id')) {
                    $q->orderBy('id');
                } else {
                    $q->orderBy($columns[0]);
                }
            }
            $rows = $q->offset($offset)->limit($chunkSize)->get();

            foreach ($rows as $row) {
                $arr = (array) $row;
                $line = [];
                foreach ($columns as $col) {
                    $line[] = self::scalarToCsvCell($arr[$col] ?? null);
                }
                fputcsv($fh, $line, ',', '"', '');
            }

            $offset += $chunkSize;
        } while ($rows->count() === $chunkSize);

        fclose($fh);
    }

    private static function scalarToCsvCell(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d H:i:s');
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }

        return (string) $v;
    }

    /**
     * fgetcsv con parámetros explícitos (evita deprecaciones PHP 8.4+ que disparan un log por fila y agotan memoria).
     *
     * @return array<int, string>|false
     */
    private static function readCsvLine($fh): array|false
    {
        return fgetcsv($fh, 0, ',', '"', '');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function readCsvRows(string $csvPath): array
    {
        $fh = fopen($csvPath, 'r');
        if ($fh === false) {
            return [];
        }
        $bom = fread($fh, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($fh);
        }
        $header = self::readCsvLine($fh);
        if ($header === false || $header === [null] || $header === []) {
            fclose($fh);

            return [];
        }
        $header = array_map(fn ($h) => trim((string) $h), $header);
        $out = [];
        while (($data = self::readCsvLine($fh)) !== false) {
            if ($data === [null] || $data === []) {
                continue;
            }
            $row = [];
            foreach ($header as $i => $col) {
                if ($col === '') {
                    continue;
                }
                $row[$col] = self::parseCsvCell($data[$i] ?? null);
            }
            $out[] = $row;
        }
        fclose($fh);

        return $out;
    }

    /**
     * Lee el CSV en bloques e inserta sin cargar toda la tabla en memoria.
     */
    public static function insertFromCsvFile(Connection $connection, string $table, string $csvPath, int $chunkSize = 500): int
    {
        $fh = fopen($csvPath, 'r');
        if ($fh === false) {
            return 0;
        }
        $bom = fread($fh, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($fh);
        }
        $headerLine = self::readCsvLine($fh);
        if ($headerLine === false || $headerLine === [null] || $headerLine === []) {
            fclose($fh);

            return 0;
        }
        $header = array_map(fn ($h) => trim((string) $h), $headerLine);
        $buffer = [];
        $total = 0;
        try {
            while (($data = self::readCsvLine($fh)) !== false) {
                if ($data === [null] || $data === []) {
                    continue;
                }
                $row = [];
                foreach ($header as $i => $col) {
                    if ($col === '') {
                        continue;
                    }
                    $row[$col] = self::parseCsvCell($data[$i] ?? null);
                }
                $buffer[] = $row;
                if (count($buffer) >= $chunkSize) {
                    $connection->table($table)->insert($buffer);
                    $total += count($buffer);
                    $buffer = [];
                }
            }
            if ($buffer !== []) {
                $connection->table($table)->insert($buffer);
                $total += count($buffer);
            }
        } finally {
            fclose($fh);
        }

        return $total;
    }

    private static function parseCsvCell(mixed $v): mixed
    {
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }
        if ($s === '0' || $s === '1') {
            return $s;
        }

        return $s;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function insertRows(Connection $connection, string $table, array $rows, int $chunkSize = 400): int
    {
        if ($rows === []) {
            return 0;
        }
        $n = 0;
        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $connection->table($table)->insert($chunk);
            $n += count($chunk);
        }

        return $n;
    }

    /**
     * Vacía las tablas del manifest (orden inverso a inserción) y vuelve a cargar desde los CSV del mismo directorio.
     * Solo MySQL/MariaDB.
     *
     * @return array{truncated: int, inserted: array<string, int>}
     */
    public static function importFromDirectory(Connection $connection, string $absoluteDir): array
    {
        $manifestPath = $absoluteDir.DIRECTORY_SEPARATOR.'_manifest.json';
        if (! is_readable($manifestPath)) {
            throw new RuntimeException("No se encontró o no se puede leer el manifest: {$manifestPath}");
        }
        $decoded = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($decoded) || ! isset($decoded['insert_order']) || ! is_array($decoded['insert_order'])) {
            throw new RuntimeException('Manifest inválido: se requiere la clave insert_order (array).');
        }
        /** @var array<int, string> $insertOrder */
        $insertOrder = array_values(array_filter(array_map('strval', $decoded['insert_order'])));

        $driver = $connection->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("La importación desde CSV solo está soportada con mysql/mariadb; conexión actual: {$driver}");
        }

        $schema = $connection->getSchemaBuilder();
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        $truncated = 0;
        foreach (array_reverse($insertOrder) as $table) {
            if (! $schema->hasTable($table)) {
                continue;
            }
            $safe = str_replace('`', '``', $table);
            $connection->statement("TRUNCATE TABLE `{$safe}`");
            $truncated++;
        }

        $inserted = [];
        foreach ($insertOrder as $table) {
            $csvPath = $absoluteDir.DIRECTORY_SEPARATOR.$table.'.csv';
            if (! $schema->hasTable($table) || ! is_readable($csvPath)) {
                $inserted[$table] = 0;

                continue;
            }
            $inserted[$table] = self::insertFromCsvFile($connection, $table, $csvPath);
        }
        $connection->statement('SET FOREIGN_KEY_CHECKS=1');

        return ['truncated' => $truncated, 'inserted' => $inserted];
    }

    public static function csvDirectoryAbsolute(): string
    {
        return base_path(self::DEFAULT_RELATIVE_DIR);
    }
}
