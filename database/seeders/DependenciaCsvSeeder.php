<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ReadsExcelDatosCsv;
use Illuminate\Database\Seeder;

class DependenciaCsvSeeder extends Seeder
{
    use ReadsExcelDatosCsv;

    public function run(): void
    {
        $n = $this->seedFromCsv('01_dependencia.csv', 'dependencia');
        $this->command?->info("dependencia: {$n} filas.");
    }
}
