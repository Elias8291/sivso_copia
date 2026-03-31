<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Exporta la tabla empleados (conexión SIVSO) a CSV UTF-8 con BOM.
 *
 * Ejecución directa:
 *   php artisan db:seed --class=ExportEmpleadosCsvSeeder
 *
 * O desde DatabaseSeeder con .env: SIVSO_EXPORT_EMPLEADOS_CSV=true
 *
 * Ruta por defecto: storage/app/exports/empleados_export_{fecha}.csv
 * Opcional: SIVSO_EXPORT_EMPLEADOS_CSV_PATH=ruta/absoluta/o/relativa/al/proyecto/archivo.csv
 */
class ExportEmpleadosCsvSeeder extends Seeder
{
    public function run(): void
    {
        $connection = (string) config('sivso.csv_snapshot_connection', 'copiasivso');
        $cx = DB::connection($connection);

        if (! $cx->getSchemaBuilder()->hasTable('empleados')) {
            $this->command?->error("No existe la tabla «empleados» en la conexión «{$connection}».");

            return;
        }

        $customPath = env('SIVSO_EXPORT_EMPLEADOS_CSV_PATH');
        if (is_string($customPath) && $customPath !== '') {
            $path = str_starts_with($customPath, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $customPath)
                ? $customPath
                : base_path($customPath);
        } else {
            $dir = storage_path('app'.DIRECTORY_SEPARATOR.'exports');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $path = $dir.DIRECTORY_SEPARATOR.'empleados_export_'.now()->format('Y-m-d_His').'.csv';
        }

        $parent = dirname($path);
        if (! is_dir($parent)) {
            mkdir($parent, 0755, true);
        }

        $rows = $cx->table('empleados as e')
            ->leftJoin('dependencias as d', 'd.id', '=', 'e.dependencia_id')
            ->leftJoin('delegaciones as del', 'del.id', '=', 'e.delegacion_id')
            ->orderBy('e.id')
            ->select([
                'e.id',
                'e.nue',
                'e.nombre',
                'e.apellido_paterno',
                'e.apellido_materno',
                'e.ur',
                'e.dependencia_id',
                'd.nombre as dependencia_nombre',
                'e.delegacion_id',
                'del.clave as delegacion_clave',
                'del.nombre as delegacion_nombre',
                'e.created_at',
                'e.updated_at',
            ])
            ->get();

        $out = fopen($path, 'w');
        if ($out === false) {
            $this->command?->error("No se pudo escribir: {$path}");

            return;
        }

        fwrite($out, "\xEF\xBB\xBF");
        $headers = [
            'id',
            'nue',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'ur',
            'dependencia_id',
            'dependencia_nombre',
            'delegacion_id',
            'delegacion_clave',
            'delegacion_nombre',
            'created_at',
            'updated_at',
        ];
        fputcsv($out, $headers, ',', '"', '');

        foreach ($rows as $r) {
            fputcsv($out, [
                $r->id,
                $r->nue,
                $r->nombre,
                $r->apellido_paterno,
                $r->apellido_materno,
                $r->ur,
                $r->dependencia_id,
                $r->dependencia_nombre,
                $r->delegacion_id,
                $r->delegacion_clave,
                $r->delegacion_nombre,
                $r->created_at,
                $r->updated_at,
            ], ',', '"', '');
        }

        fclose($out);

        $n = $rows->count();
        $this->command?->info("Empleados exportados a CSV: {$n} filas → {$path}");
    }
}
