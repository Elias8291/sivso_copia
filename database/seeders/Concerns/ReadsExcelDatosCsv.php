<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;

trait ReadsExcelDatosCsv
{
    /**
     * @param  callable(array<string, string|null>): array<string, mixed>|null  $mapRow  return null to skip row
     */
    protected function seedFromCsv(string $fileName, string $table, int $chunkSize = 500, ?callable $mapRow = null): int
    {
        $path = database_path('seeders/excel_datos/'.$fileName);

        if (! is_readable($path)) {
            if ($this->command) {
                $this->command->warn("CSV no encontrado: {$fileName}");
            }

            return 0;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return 0;
        }

        $header = fgetcsv($handle);
        if ($header === false || $header === [null] || $header === []) {
            fclose($handle);

            return 0;
        }

        $header = array_map(static function ($h): string {
            $h = trim((string) $h);
            $bom = preg_replace('/^\xEF\xBB\xBF/', '', $h);

            return is_string($bom) ? $bom : $h;
        }, $header);

        $inserted = 0;
        $chunk = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === []) {
                continue;
            }

            $assoc = [];
            foreach ($header as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $val = array_key_exists($i, $row) ? $row[$i] : null;
                if (is_string($val)) {
                    $val = trim($val);
                }
                $assoc[$key] = ($val === '') ? null : $val;
            }

            if ($this->rowIsEmpty($assoc)) {
                continue;
            }

            if ($mapRow !== null) {
                $mapped = $mapRow($assoc);
                if ($mapped === null) {
                    continue;
                }
                $assoc = $mapped;
            } else {
                $assoc = $this->defaultNormalizeRow($assoc);
            }

            $chunk[] = $assoc;
            if (count($chunk) >= $chunkSize) {
                DB::table($table)->insert($chunk);
                $inserted += count($chunk);
                $chunk = [];
            }
        }

        fclose($handle);

        if ($chunk !== []) {
            DB::table($table)->insert($chunk);
            $inserted += count($chunk);
        }

        return $inserted;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function defaultNormalizeRow(array $row): array
    {
        $out = [];
        foreach ($row as $k => $v) {
            $out[$k] = $v === null || $v === '' ? null : $v;
        }

        return $out;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }

        return true;
    }
}
