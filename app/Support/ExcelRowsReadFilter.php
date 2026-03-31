<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Limita la lectura de un .xlsx a un rango de filas (menos memoria en hojas grandes).
 */
final class ExcelRowsReadFilter implements IReadFilter
{
    private int $startRow = 1;

    private int $endRow = 1;

    public function setRows(int $startRow, int $endRow): void
    {
        $this->startRow = $startRow;
        $this->endRow = $endRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}
