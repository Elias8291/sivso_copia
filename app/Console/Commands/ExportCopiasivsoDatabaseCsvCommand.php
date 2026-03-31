<?php

namespace App\Console\Commands;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportCopiasivsoDatabaseCsvCommand extends Command
{
    protected $signature = 'sivso:export-database-csv
                            {--connection=copiasivso : Conexión MySQL/MariaDB}
                            {--dir= : Carpeta absoluta de salida (por defecto database/seeders/csv/copiasivso)}
                            {--chunk=2000 : Filas por consulta al volcar cada tabla}';

    protected $description = 'Exporta todas las tablas de la base a CSV + _manifest.json (orden de inserción por FK) para seeders';

    public function handle(): int
    {
        $connName = (string) $this->option('connection');
        $dirOption = $this->option('dir');
        $absoluteDir = is_string($dirOption) && $dirOption !== ''
            ? $dirOption
            : CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();
        $absoluteDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absoluteDir);

        $chunk = (int) $this->option('chunk');
        if ($chunk < 1) {
            $chunk = 2000;
        }

        $connection = DB::connection($connName);
        $driver = $connection->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->error("La exportación CSV solo está soportada con mysql/mariadb; conexión «{$connName}» usa «{$driver}».");

            return self::FAILURE;
        }

        $this->info("Exportando «{$connection->getDatabaseName()}» hacia «{$absoluteDir}»…");

        $result = CopiasivsoDatabaseCsvSnapshot::export($connection, $absoluteDir, null, [], $chunk);

        $this->info('Tablas: '.count($result['tables']).' — manifest: insert_order con '.count($result['order']).' tablas.');
        $this->line('Siguiente paso: `php artisan db:seed` (CSV + reconciliación empleados.delegacion_id si existe tabla «delegacion»).');
        $this->line('Regenerar solo empleados.csv: `php artisan db:seed --class=SyncEmpleadosCsvToSnapshotSeeder`.');

        return self::SUCCESS;
    }
}
