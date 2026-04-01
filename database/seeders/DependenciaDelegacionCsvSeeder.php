<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class DependenciaDelegacionCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('04_dependencia_delegacion.csv', 'dependencia_delegacion', 500, function (array $row): ?array {
            return [
                'ur' => (int) $row['ur'],
                'delegacion_codigo' => $row['delegacion_codigo'],
            ];
        });
        $this->command?->info("dependencia_delegacion: {$n} filas.");
    }
}
