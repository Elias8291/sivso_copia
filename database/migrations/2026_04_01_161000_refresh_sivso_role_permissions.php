<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Vuelve a sembrar roles y permisos (nombres en español y permisos por acción).
     * Idempotente junto con RolePermissionSeeder.
     */
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RolePermissionSeeder',
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        //
    }
};
