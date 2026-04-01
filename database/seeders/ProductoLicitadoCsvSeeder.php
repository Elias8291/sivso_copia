<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class ProductoLicitadoCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('08_producto_licitado.csv', 'producto_licitado', 500, function (array $row): ?array {
            return [
                'id' => (int) $row['id'],
                'anio' => (int) $row['anio'],
                'numero_partida' => (int) $row['numero_partida'],
                'lote' => (int) $row['lote'],
                'partida_especifica' => (int) $row['partida_especifica'],
                'codigo_catalogo' => $row['codigo_catalogo'],
                'descripcion' => $row['descripcion'],
                'cantidad_propuesta' => (int) $row['cantidad_propuesta'],
                'unidad' => $row['unidad'],
                'marca' => $row['marca'],
                'precio_unitario' => $row['precio_unitario'],
                'subtotal' => $row['subtotal'],
                'proveedor' => $row['proveedor'],
                'medida' => $row['medida'],
                'clasificacion_principal_id' => $row['clasificacion_principal_id'] !== null ? (int) $row['clasificacion_principal_id'] : null,
            ];
        });
        $this->command?->info("producto_licitado: {$n} filas.");
    }
}
