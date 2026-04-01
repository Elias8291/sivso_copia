<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class AsignacionEmpleadoProductoCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('13_asignacion_empleado_producto.csv', 'asignacion_empleado_producto', 2000, function (array $row): ?array {
            return [
                'id' => (int) $row['id'],
                'anio' => (int) $row['anio'],
                'empleado_id' => (int) $row['empleado_id'],
                'producto_licitado_id' => (int) $row['producto_licitado_id'],
                'producto_cotizado_id' => $row['producto_cotizado_id'] !== null && $row['producto_cotizado_id'] !== ''
                    ? (int) $row['producto_cotizado_id']
                    : null,
                'clave_partida_presupuestal' => $row['clave_partida_presupuestal'],
                'cantidad' => $row['cantidad'] !== null && $row['cantidad'] !== '' ? (int) $row['cantidad'] : null,
                'talla' => $row['talla'],
                'cantidad_secundaria' => $row['cantidad_secundaria'] !== null && $row['cantidad_secundaria'] !== ''
                    ? (int) $row['cantidad_secundaria']
                    : null,
                'clave_presupuestal' => $row['clave_presupuestal'] !== null && $row['clave_presupuestal'] !== ''
                    ? (int) $row['clave_presupuestal']
                    : null,
                'legacy_concentrado_id' => $row['legacy_concentrado_id'] !== null && $row['legacy_concentrado_id'] !== ''
                    ? (int) $row['legacy_concentrado_id']
                    : null,
            ];
        });
        $this->command?->info("asignacion_empleado_producto: {$n} filas.");
    }
}
