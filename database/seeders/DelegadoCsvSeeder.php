<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class DelegadoCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('05_delegado.csv', 'delegado', 500, function (array $row): ?array {
            return [
                'id' => (int) $row['id'],
                'nombre_completo' => $row['nombre_completo'],
                'nue' => $row['nue'],
            ];
        });
        $this->command?->info("delegado: {$n} filas.");
    }
}
