<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class ConfiguracionSivsoCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('14_configuracion_sivso.csv', 'configuracion_sivso', 100, function (array $row): ?array {
            if ($row['clave'] === null || $row['clave'] === '') {
                return null;
            }

            return [
                'clave' => $row['clave'],
                'valor' => $row['valor'] ?? '',
            ];
        });
        $this->command?->info("configuracion_sivso: {$n} filas.");
    }
}
