<?php

namespace App\Console\Commands;

use App\Support\CopiasivsoExcelSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportCopiasivsoDatabaseXlsxCommand extends Command
{
    protected $signature = 'sivso:export-database-xlsx
                            {--connection=copiasivso : Conexión MySQL/MariaDB (misma base que DB_* para base_probando)}
                            {--dir= : Carpeta absoluta de salida (por defecto database/seeders/xlsx/copiasivso)}
                            {--chunk=2000 : Filas por consulta al volcar cada tabla}';

    protected $description = 'Exporta cada tabla de la base a un .xlsx + _manifest.json (orden insert por FK) para CopiasivsoExcelDirectorySeeder';

    public function handle(): int
    {
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '1024M');
        }

        $connName = (string) $this->option('connection');
        $dirOption = $this->option('dir');
        $absoluteDir = is_string($dirOption) && $dirOption !== ''
            ? $dirOption
            : CopiasivsoExcelSnapshot::excelDirectoryAbsolute();
        $absoluteDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absoluteDir);

        $chunk = (int) $this->option('chunk');
        if ($chunk < 1) {
            $chunk = 2000;
        }

        $connection = DB::connection($connName);
        $driver = $connection->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->error("La exportación Excel solo está soportada con mysql/mariadb; conexión «{$connName}» usa «{$driver}».");

            return self::FAILURE;
        }

        CopiasivsoExcelSnapshot::assertPackagePresent();

        $this->info("Exportando «{$connection->getDatabaseName()}» hacia «{$absoluteDir}»…");

        $result = CopiasivsoExcelSnapshot::export($connection, $absoluteDir, null, [], $chunk);

        $this->info('Tablas: '.count($result['tables']).' — manifest: insert_order con '.count($result['order']).' tablas.');
        $this->line('Importar en otra instalación (mismo esquema): `php artisan db:seed --class=CopiasivsoExcelDirectorySeeder`.');
        $this->line('Si usas también snapshot CSV en db:seed, el Excel corre después y sustituye datos.');

        return self::SUCCESS;
    }
}
