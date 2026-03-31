<?php

namespace Database\Seeders;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga datos desde database/seeders/csv/copiasivso según _manifest.json (TRUNCATE + INSERT).
 * Ejecuta primero las migraciones de la conexión objetivo. Requiere MySQL/MariaDB.
 */
class CopiasivsoFromCsvSeeder extends Seeder
{
    public function run(): void
    {
        $dir = CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();
        $manifest = $dir.DIRECTORY_SEPARATOR.'_manifest.json';
        if (! is_readable($manifest)) {
            throw new \RuntimeException(
                "No existe el snapshot CSV. Genera uno con: php artisan sivso:export-database-csv. Manifest esperado en: {$manifest}"
            );
        }

        $connection = DB::connection((string) config('sivso.csv_snapshot_connection'));

        $summary = CopiasivsoDatabaseCsvSnapshot::importFromDirectory($connection, $dir);

        if ($this->command !== null) {
            $this->command->info("TRUNCATE aplicado a {$summary['truncated']} tablas.");
            $total = array_sum($summary['inserted']);
            $this->command->info("Filas insertadas en total: {$total}.");
        }
    }
}
