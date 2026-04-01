<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class ClasificacionBienCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('03_clasificacion_bien.csv', 'clasificacion_bien', 500, function (array $row): ?array {
            return [
                'id' => (int) $row['id'],
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre'],
            ];
        });
        $this->command?->info("clasificacion_bien: {$n} filas.");
    }
}
