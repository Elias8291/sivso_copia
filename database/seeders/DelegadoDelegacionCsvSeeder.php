<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class DelegadoDelegacionCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('06_delegado_delegacion.csv', 'delegado_delegacion', 500, function (array $row): ?array {
            return [
                'delegado_id' => (int) $row['delegado_id'],
                'delegacion_codigo' => $row['delegacion_codigo'],
            ];
        });
        $this->command?->info("delegado_delegacion: {$n} filas.");
    }
}
