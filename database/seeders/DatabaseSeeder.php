<?php

namespace Database\Seeders;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Orden: superusuario → RBAC → CopiasivsoCsvSeeder (CSV) → reconciliación delegación.
     * Los .xlsx (sivso:export-database-xlsx) son solo respaldo/archivo; no se cargan aquí. Importación opcional: db:seed --class=CopiasivsoExcelDirectorySeeder.
     *
     * Desactivar todo el bloque copiasivso: SIVSO_SKIP_DATA_SEED=true en .env
     */
    public function run(): void
    {
        $this->call([
            SuperUserSeeder::class,
            SivsoRbacSeeder::class,
        ]);

        $exportEmpleadosCsv = filter_var(env('SIVSO_EXPORT_EMPLEADOS_CSV', false), FILTER_VALIDATE_BOOLEAN);

        if (filter_var(env('SIVSO_SKIP_DATA_SEED', false), FILTER_VALIDATE_BOOLEAN)) {
            if ($exportEmpleadosCsv) {
                $this->call(ExportEmpleadosCsvSeeder::class);
            }

            return;
        }

        $csvDir = CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();
        $csvManifest = $csvDir.DIRECTORY_SEPARATOR.'_manifest.json';
        $csvFiles = glob($csvDir.DIRECTORY_SEPARATOR.'*.csv') ?: [];

        if (is_readable($csvManifest) && $csvFiles !== []) {
            $this->call(CopiasivsoCsvSeeder::class);
            $this->call(EmpleadosReconcileDelegacionSeeder::class);
        }

        if ($exportEmpleadosCsv) {
            $this->call(ExportEmpleadosCsvSeeder::class);
        }

        if (filter_var(env('SIVSO_SYNC_EMPLEADOS_CSV_SNAPSHOT', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(SyncEmpleadosCsvToSnapshotSeeder::class);
        }
    }
}
