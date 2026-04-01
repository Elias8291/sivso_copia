<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class ProductoCotizadoCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('09_producto_cotizado.csv', 'producto_cotizado', 500, function (array $row): ?array {
            return [
                'id' => (int) $row['id'],
                'anio' => (int) $row['anio'],
                'producto_licitado_id' => (int) $row['producto_licitado_id'],
                'numero_partida' => (int) $row['numero_partida'],
                'partida_especifica' => (int) $row['partida_especifica'],
                'clave' => $row['clave'],
                'descripcion' => $row['descripcion'],
                'precio_unitario' => $row['precio_unitario'],
                'importe' => $row['importe'],
                'iva' => $row['iva'],
                'total' => $row['total'],
                'precio_alterno' => $row['precio_alterno'],
                'referencia_codigo' => $row['referencia_codigo'],
                'clasificacion_principal_id' => $row['clasificacion_principal_id'] !== null ? (int) $row['clasificacion_principal_id'] : null,
            ];
        });
        $this->command?->info("producto_cotizado: {$n} filas.");
    }
}
