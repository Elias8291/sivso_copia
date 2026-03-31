<?php

namespace Database\Seeders;

use App\Support\CopiasivsoExcelSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * TRUNCATE de una tabla + datos desde database/seeders/xlsx/copiasivso/{tabla}.xlsx
 * (primera hoja, fila 1 = columnas como en MySQL).
 */
abstract class AbstractCopiasivsoTableSeeder extends Seeder
{
    abstract protected function table(): string;

    public function run(): void
    {
        CopiasivsoExcelSnapshot::assertPackagePresent();

        $table = $this->table();
        $dir = CopiasivsoExcelSnapshot::excelDirectoryAbsolute();
        $path = $dir.DIRECTORY_SEPARATOR.$table.'.xlsx';

        if (! is_readable($path)) {
            throw new \RuntimeException(
                "No se encuentra o no se puede leer el archivo para la tabla «{$table}»: {$path}"
            );
        }

        $connection = DB::connection((string) config('sivso.csv_snapshot_connection'));

        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('Estos seeders requieren MySQL/MariaDB.');
        }

        $schema = $connection->getSchemaBuilder();
        if (! $schema->hasTable($table)) {
            throw new \RuntimeException("La tabla «{$table}» no existe en la conexión «{$connection->getName()}».");
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        $safe = str_replace('`', '``', $table);
        $connection->statement("TRUNCATE TABLE `{$safe}`");
        $n = CopiasivsoExcelSnapshot::insertFromExcelFile($connection, $table, $path);
        $connection->statement('SET FOREIGN_KEY_CHECKS=1');

        if ($this->command !== null) {
            $this->command->info("«{$table}»: {$n} filas.");
        }
    }
}
