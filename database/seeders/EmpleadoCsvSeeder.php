<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class EmpleadoCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('07_empleado.csv', 'empleado', 1000, function (array $row): ?array {
            $s = static fn (?string $v): string => $v === null || $v === '' ? '' : $v;

            return [
                'id' => (int) $row['id'],
                'legacy_empleado_id' => $row['legacy_empleado_id'] !== null && $row['legacy_empleado_id'] !== ''
                    ? (int) $row['legacy_empleado_id']
                    : null,
                'nue' => $row['nue'] !== null && $row['nue'] !== '' ? $row['nue'] : null,
                'nombre' => $s($row['nombre'] ?? null),
                'apellido_paterno' => $s($row['apellido_paterno'] ?? null),
                'apellido_materno' => $s($row['apellido_materno'] ?? null),
                'ur' => (int) $row['ur'],
                'delegacion_codigo' => $row['delegacion_codigo'] ?? '',
            ];
        });
        $this->command?->info("empleado: {$n} filas.");
    }
}
