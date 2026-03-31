<?php

namespace Database\Seeders;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 1) Reconcilia delegacion_id (tabla legacy delegacion).
 * 2) Exporta «empleados» al CSV del snapshot (mismas columnas que la BD) para CopiasivsoCsvSeeder.
 *
 * Si la tabla empleados está vacía pero empleados.csv del snapshot aún tiene filas de datos, no sobrescribe
 * (evita borrar miles de filas del repo tras un TRUNCATE). Fuerza con SIVSO_SYNC_EMPLEADOS_CSV_ALLOW_EMPTY=true.
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

        $path = $dir.DIRECTORY_SEPARATOR.'empleados.csv';
        $dbCount = (int) $cx->table('empleados')->count();
        $snapshotDataRows = self::countCsvDataRowsExcludingHeader($path);
        $allowEmptyOverwrite = filter_var(env('SIVSO_SYNC_EMPLEADOS_CSV_ALLOW_EMPTY', false), FILTER_VALIDATE_BOOLEAN);

        if ($dbCount === 0 && $snapshotDataRows > 0 && ! $allowEmptyOverwrite) {
            $this->command?->error(
                "No se actualiza empleados.csv: la BD tiene 0 empleados pero el snapshot tenía {$snapshotDataRows} filas de datos. ".
                'Rellena la BD primero, o ejecuta `php artisan sivso:restore-empleados-snapshot-csv`, o define SIVSO_SYNC_EMPLEADOS_CSV_ALLOW_EMPTY=true si quieres dejar el CSV solo con cabecera.'
            );

            return;
        }

        CopiasivsoDatabaseCsvSnapshot::exportTable($cx, 'empleados', $dir, 2000);
        $this->command?->info("CSV snapshot actualizado: {$path}");
    }

    private static function countCsvDataRowsExcludingHeader(string $path): int
    {
        if (! is_readable($path)) {
            return 0;
        }
        $fh = fopen($path, 'r');
        if ($fh === false) {
            return 0;
        }
        $bom = fread($fh, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($fh);
        }
        if (CopiasivsoDatabaseCsvSnapshot::readCsvDataLine($fh) === false) {
            fclose($fh);

            return 0;
        }
        $n = 0;
        while (CopiasivsoDatabaseCsvSnapshot::readCsvDataLine($fh) !== false) {
            $n++;
        }
        fclose($fh);

        return $n;
    }
}
