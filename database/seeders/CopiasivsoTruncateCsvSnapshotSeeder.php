<?php

namespace Database\Seeders;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Vacía todas las tablas listadas en _manifest.json (orden inverso a insert_order).
 * Debe ejecutarse una sola vez antes de los seeders por tabla CSV.
 */
class CopiasivsoTruncateCsvSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $dir = CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();
        $connection = DB::connection((string) config('sivso.csv_snapshot_connection'));

        $n = CopiasivsoDatabaseCsvSnapshot::truncateSnapshotTables($connection, $dir);
        $this->command?->info("Snapshot CSV: {$n} tablas truncadas (FK off).");
    }
}
