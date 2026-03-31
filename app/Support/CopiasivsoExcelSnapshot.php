<?php

namespace App\Support;

use Illuminate\Database\Connection;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

/**
 * Importa filas desde archivos .xlsx (una hoja por libro, fila 1 = nombres de columnas como en la BD).
 */
final class CopiasivsoExcelSnapshot
{
    public const DEFAULT_RELATIVE_DIR = 'database/seeders/xlsx/copiasivso';

    public static function assertPackagePresent(): void
    {
        if (! class_exists(IOFactory::class)) {
            throw new RuntimeException(
                'Falta PhpSpreadsheet. Ejecuta: composer require phpoffice/phpspreadsheet'
            );
        }
    }

    public static function excelDirectoryAbsolute(): string
    {
        return base_path(self::DEFAULT_RELATIVE_DIR);
    }

    /**
     * Convierte el valor de celda a algo insertable en MySQL (fechas Excel incluidas).
     */
    public static function cellValueToScalar(Cell $cell): mixed
    {
        $v = $cell->getValue();
        if ($v === null || $v === '') {
            return null;
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d H:i:s');
        }
        if (Date::isDateTime($cell)) {
            try {
                return Date::excelToDateTimeObject((float) $v)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                // continúa como escalar
            }
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }
        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }
        if ($s === '0' || $s === '1') {
            return $s;
        }

        return $s;
    }

    /**
     * @return array<int, string>
     */
    public static function readHeaderRowFromSheet(Worksheet $sheet): array
    {
        $row = $sheet->getRowIterator(1, 1)->current();
        if ($row === null) {
            return [];
        }
        $it = $row->getCellIterator();
        $it->setIterateOnlyExistingCells(false);
        $header = [];
        foreach ($it as $cell) {
            $header[] = trim((string) $cell->getValue());
        }

        return $header;
    }

    /**
     * @param  array<int, string>  $header
     * @return array<string, mixed>
     */
    public static function dataRowToAssoc(array $header, Row $row): array
    {
        $it = $row->getCellIterator();
        $it->setIterateOnlyExistingCells(false);
        $assoc = [];
        $i = 0;
        foreach ($it as $cell) {
            if ($i >= count($header)) {
                break;
            }
            $name = $header[$i];
            if ($name !== '') {
                $assoc[$name] = self::cellValueToScalar($cell);
            }
            $i++;
        }

        return $assoc;
    }

    /**
     * Inserta filas desde un .xlsx (primera hoja). Lee por bloques para no cargar toda la hoja en RAM.
     */
    public static function insertFromExcelFile(
        Connection $connection,
        string $table,
        string $xlsxPath,
        int $rowChunkSize = 500
    ): int {
        self::assertPackagePresent();
        if (! is_readable($xlsxPath)) {
            return 0;
        }

        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $filter = new ExcelRowsReadFilter;
        $reader->setReadFilter($filter);

        $infos = $reader->listWorksheetInfo($xlsxPath);
        if ($infos === []) {
            return 0;
        }
        $totalRows = (int) ($infos[0]['totalRows'] ?? 0);
        if ($totalRows < 1) {
            return 0;
        }

        $filter->setRows(1, 1);
        $spreadsheet = $reader->load($xlsxPath);
        $sheet = $spreadsheet->getActiveSheet();
        $header = self::readHeaderRowFromSheet($sheet);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if ($header === [] || $header === ['']) {
            return 0;
        }

        $inserted = 0;
        $buffer = [];
        $insertBatchSize = 400;
        for ($start = 2; $start <= $totalRows; $start += $rowChunkSize) {
            $end = min($start + $rowChunkSize - 1, $totalRows);
            $filter->setRows($start, $end);
            $spreadsheet = $reader->load($xlsxPath);
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($sheet->getRowIterator($start, $end) as $row) {
                $assoc = self::dataRowToAssoc($header, $row);
                if ($assoc === []) {
                    continue;
                }
                $buffer[] = $assoc;
                if (count($buffer) >= $insertBatchSize) {
                    CopiasivsoDatabaseCsvSnapshot::insertRows($connection, $table, $buffer, $insertBatchSize);
                    $inserted += count($buffer);
                    $buffer = [];
                }
            }
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }

        if ($buffer !== []) {
            CopiasivsoDatabaseCsvSnapshot::insertRows($connection, $table, $buffer, $insertBatchSize);
            $inserted += count($buffer);
        }

        return $inserted;
    }

    /**
     * @return array{truncated: int, inserted: array<string, int>}
     */
    public static function importFromDirectory(Connection $connection, string $absoluteDir): array
    {
        self::assertPackagePresent();
        $manifestPath = $absoluteDir.DIRECTORY_SEPARATOR.'_manifest.json';
        if (! is_readable($manifestPath)) {
            throw new RuntimeException("No se encontró o no se puede leer el manifest: {$manifestPath}");
        }
        $decoded = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($decoded) || ! isset($decoded['insert_order']) || ! is_array($decoded['insert_order'])) {
            throw new RuntimeException('Manifest inválido: se requiere la clave insert_order (array).');
        }
        /** @var array<int, string> $insertOrder */
        $insertOrder = array_values(array_filter(array_map('strval', $decoded['insert_order'])));

        $driver = $connection->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("La importación desde Excel solo está soportada con mysql/mariadb; conexión actual: {$driver}");
        }

        $schema = $connection->getSchemaBuilder();
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        $truncated = 0;
        foreach (array_reverse($insertOrder) as $tbl) {
            if (! $schema->hasTable($tbl)) {
                continue;
            }
            $safe = str_replace('`', '``', $tbl);
            $connection->statement("TRUNCATE TABLE `{$safe}`");
            $truncated++;
        }

        $inserted = [];
        foreach ($insertOrder as $tbl) {
            $xlsxPath = $absoluteDir.DIRECTORY_SEPARATOR.$tbl.'.xlsx';
            if (! $schema->hasTable($tbl) || ! is_readable($xlsxPath)) {
                $inserted[$tbl] = 0;

                continue;
            }
            $inserted[$tbl] = self::insertFromExcelFile($connection, $tbl, $xlsxPath);
        }
        $connection->statement('SET FOREIGN_KEY_CHECKS=1');

        return ['truncated' => $truncated, 'inserted' => $inserted];
    }
}
