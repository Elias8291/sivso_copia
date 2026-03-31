<?php

namespace Database\Seeders;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * INSERT de una tabla desde database/seeders/csv/copiasivso/{tabla}.csv (sin TRUNCATE).
 * Ejecutar después de CopiasivsoTruncateCsvSnapshotSeeder y en el orden de _manifest.json.
 */
abstract class AbstractCopiasivsoCsvTableSeeder extends Seeder
{
    abstract protected function table(): string;

    public function run(): void
    {
        $table = $this->table();
        $dir = CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();
        $path = $dir.DIRECTORY_SEPARATOR.$table.'.csv';

        $connection = DB::connection((string) config('sivso.csv_snapshot_connection'));

        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('Estos seeders requieren MySQL/MariaDB.');
        }

        $schema = $connection->getSchemaBuilder();
        if (! $schema->hasTable($table)) {
            throw new \RuntimeException("La tabla «{$table}» no existe en la conexión «{$connection->getName()}».");
        }

        if (! is_readable($path)) {
            $this->command?->warn("CSV no legible u omitido: {$path} (0 filas).");

            return;
        }

        $n = CopiasivsoDatabaseCsvSnapshot::insertFromCsvFile($connection, $table, $path);
        $this->command?->info("«{$table}»: {$n} filas.");
    }
}
