<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class ProductoLicitadoClasificacionCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('10_producto_licitado_clasificacion.csv', 'producto_licitado_clasificacion', 500, function (array $row): ?array {
            return [
                'producto_licitado_id' => (int) $row['producto_licitado_id'],
                'clasificacion_id' => (int) $row['clasificacion_id'],
            ];
        });
        $this->command?->info("producto_licitado_clasificacion: {$n} filas.");
    }
}
