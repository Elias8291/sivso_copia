<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::query()->firstOrCreate(
            ['rfc' => 'RAJE020226H97'],
            [
                'name' => 'Administrador',
                'email' => 'admin@example.com',
                'password' => Hash::make('Abisai1456'),
                'must_change_password' => false,
                'activo' => true,
            ]
        );
        $admin->syncRoles(['Administrador']);

        Role::query()
            ->where('name', 'Administrador')
            ->where('guard_name', 'web')
            ->first()
            ?->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            DependenciaCsvSeeder::class,
            DelegacionCsvSeeder::class,
            ClasificacionBienCsvSeeder::class,
            DependenciaDelegacionCsvSeeder::class,
            DelegadoCsvSeeder::class,
            DelegadoDelegacionCsvSeeder::class,
            EmpleadoCsvSeeder::class,
            ProductoLicitadoCsvSeeder::class,
            ProductoCotizadoCsvSeeder::class,
            ProductoLicitadoClasificacionCsvSeeder::class,
            ProductoCotizadoClasificacionCsvSeeder::class,
            CupoDependenciaPartidaCsvSeeder::class,
            AsignacionEmpleadoProductoCsvSeeder::class,
            ConfiguracionSivsoCsvSeeder::class,
        ]);
    }
}
