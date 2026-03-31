<?php

namespace Database\Seeders;

use App\Support\CopiasivsoExcelSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * TRUNCATE (orden inverso al manifest) + INSERT desde database/seeders/xlsx/copiasivso/{tabla}.xlsx
 * según _manifest.json (mismo flujo que exportó sivso:export-database-xlsx).
 *
 *   php artisan db:seed --class=CopiasivsoExcelDirectorySeeder
 */
class CopiasivsoExcelDirectorySeeder extends Seeder
{
    public function run(): void
    {
        $dir = CopiasivsoExcelSnapshot::excelDirectoryAbsolute();
        $manifest = $dir.DIRECTORY_SEPARATOR.'_manifest.json';
        if (! is_readable($manifest)) {
            throw new \RuntimeException(
                "No hay snapshot Excel. Genera uno con: php artisan sivso:export-database-xlsx. Manifest: {$manifest}"
            );
        }

        $connection = DB::connection((string) config('sivso.csv_snapshot_connection'));
        $summary = CopiasivsoExcelSnapshot::importFromDirectory($connection, $dir);

        if ($this->command !== null) {
            $this->command->info("Excel: {$summary['truncated']} tablas truncadas.");
            $total = array_sum($summary['inserted']);
            $this->command->info("Filas insertadas en total: {$total}.");
        }
    }
}
