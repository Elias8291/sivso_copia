<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuperUserSeeder::class,
        ]);

        if (filter_var(env('SIVSO_SEED_FROM_CSV', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(CopiasivsoFromCsvSeeder::class);
        }

        if (filter_var(env('SIVSO_SEED_FROM_EXCEL', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(CopiasivsoSeeder::class);
        }
    }
}
