<?php

namespace App\Console\Commands;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Reconstruye database/seeders/csv/copiasivso/empleados.csv a partir de un export enriquecido
 * (storage/app/exports/empleados_export_*.csv) u otro CSV que traiga al menos las columnas de «empleados».
 */
class RestoreEmpleadosSnapshotCsvCommand extends Command
{
    protected $signature = 'sivso:restore-empleados-snapshot-csv
                            {--from= : Ruta al CSV origen (por defecto el empleados_export_*.csv más reciente en storage/app/exports)}
                            {--connection= : Conexión para leer columnas de empleados (por defecto sivso.csv_snapshot_connection)}';

    protected $description = 'Regenera el empleados.csv del snapshot desde un export (evita perder ~10k filas tras sync con BD vacía)';

    public function handle(): int
    {
        $connName = (string) ($this->option('connection') ?: config('sivso.csv_snapshot_connection', 'copiasivso'));
        $cx = DB::connection($connName);
        if (! $cx->getSchemaBuilder()->hasTable('empleados')) {
            $this->error("No existe la tabla «empleados» en «{$connName}».");

            return self::FAILURE;
        }

        $tableColumns = $cx->getSchemaBuilder()->getColumnListing('empleados');
        if ($tableColumns === []) {
            $this->error('No se pudieron leer columnas de empleados.');

            return self::FAILURE;
        }

        $fromOption = $this->option('from');
        $fromPath = is_string($fromOption) && $fromOption !== ''
            ? (str_starts_with($fromOption, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $fromOption)
                ? $fromOption
                : base_path($fromOption))
            : $this->resolveLatestExportPath();

        if ($fromPath === null || ! is_readable($fromPath)) {
            $this->error('No hay CSV origen legible. Pasa --from=ruta/al/export.csv o genera uno con ExportEmpleadosCsvSeeder.');

            return self::FAILURE;
        }

        $targetDir = CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            $this->error("No se pudo crear el directorio: {$targetDir}");

            return self::FAILURE;
        }
        $targetPath = $targetDir.DIRECTORY_SEPARATOR.'empleados.csv';

        $this->info("Origen: {$fromPath}");
        $this->info("Destino: {$targetPath}");

        $in = fopen($fromPath, 'r');
        if ($in === false) {
            throw new RuntimeException("No se pudo abrir {$fromPath}");
        }
        $bom = fread($in, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($in);
        }
        $headerLine = CopiasivsoDatabaseCsvSnapshot::readCsvDataLine($in);
        if ($headerLine === false || $headerLine === []) {
            fclose($in);
            $this->error('CSV origen sin cabecera.');

            return self::FAILURE;
        }
        $sourceHeader = array_map(fn ($h) => trim((string) $h), $headerLine);
        $indexByName = [];
        foreach ($sourceHeader as $i => $name) {
            if ($name !== '') {
                $indexByName[$name] = $i;
            }
        }

        if (! isset($indexByName['id'])) {
            fclose($in);
            $this->error('El CSV origen debe incluir la columna «id».');

            return self::FAILURE;
        }

        $out = fopen($targetPath, 'w');
        if ($out === false) {
            fclose($in);
            $this->error("No se pudo escribir {$targetPath}");

            return self::FAILURE;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $tableColumns, ',', '"', '');

        $written = 0;
        while (($data = CopiasivsoDatabaseCsvSnapshot::readCsvDataLine($in)) !== false) {
            if ($data === [null] || $data === []) {
                continue;
            }
            if (trim((string) ($data[$indexByName['id']] ?? '')) === '') {
                continue;
            }
            $line = [];
            foreach ($tableColumns as $col) {
                if (! isset($indexByName[$col])) {
                    $line[] = '';

                    continue;
                }
                $raw = $data[$indexByName[$col]] ?? '';
                $line[] = $raw === null ? '' : (string) $raw;
            }
            fputcsv($out, $line, ',', '"', '');
            $written++;
        }
        fclose($in);
        fclose($out);

        $this->info("Filas escritas (sin cabecera): {$written}");
        $this->line('Siguiente: `php artisan db:seed` o `php artisan db:seed --class=CopiasivsoCsvSeeder`');

        return self::SUCCESS;
    }

    private function resolveLatestExportPath(): ?string
    {
        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'exports');
        if (! is_dir($dir)) {
            return null;
        }
        $files = glob($dir.DIRECTORY_SEPARATOR.'empleados_export_*.csv') ?: [];
        if ($files === []) {
            return null;
        }
        usort($files, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));

        return $files[0];
    }
}
