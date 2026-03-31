<?php

namespace Database\Seeders;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 1) Reconcilia delegacion_id (tabla legacy delegacion).
 * 2) Exporta «empleados» al CSV del snapshot (mismas columnas que la BD) para CopiasivsoFromCsvSeeder.
 *
 *   php artisan db:seed --class=SyncEmpleadosCsvToSnapshotSeeder
 */
class SyncEmpleadosCsvToSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EmpleadosReconcileDelegacionSeeder::class);

        $connection = (string) config('sivso.csv_snapshot_connection', 'copiasivso');
        $cx = DB::connection($connection);
        $dir = CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();

        if (! $cx->getSchemaBuilder()->hasTable('empleados')) {
            $this->command?->error('No hay tabla empleados.');

            return;
        }

        CopiasivsoDatabaseCsvSnapshot::exportTable($cx, 'empleados', $dir, 2000);
        $path = $dir.DIRECTORY_SEPARATOR.'empleados.csv';
        $this->command?->info("CSV snapshot actualizado: {$path}");
    }
}
