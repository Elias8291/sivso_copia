<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class CupoDependenciaPartidaCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('12_cupo_dependencia_partida.csv', 'cupo_dependencia_partida', 500, function (array $row): ?array {
            if ($row['ur'] === null || $row['ur'] === '') {
                return null;
            }

            return [
                'ur' => (int) $row['ur'],
                'numero_partida' => (int) $row['numero_partida'],
                'partida_especifica' => (int) $row['partida_especifica'],
                'anio' => (int) $row['anio'],
                'monto_limite' => $row['monto_limite'],
            ];
        });
        $this->command?->info("cupo_dependencia_partida: {$n} filas.");
    }
}
