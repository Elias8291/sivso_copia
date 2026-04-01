<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $legacy = config('sivso.legacy_permission_names', []);
        if ($legacy !== []) {
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $legacy)
                ->delete();
        }

        foreach (config('sivso.all_permissions', []) as $name) {
            Permission::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
        }

        $all = Permission::query()->where('guard_name', $guard)->get();

        $delegado = Role::query()->firstOrCreate(['name' => 'Delegado', 'guard_name' => $guard]);
        $delegado->syncPermissions(Permission::query()->whereIn('name', [
            'Ver mi delegación',
            'Ver empleados',
            'Crear empleados',
            'Editar empleados',
        ])->where('guard_name', $guard)->get());

        $consulta = Role::query()->firstOrCreate(['name' => 'Consulta', 'guard_name' => $guard]);
        $consulta->syncPermissions(Permission::query()->whereIn('name', [
            'Ver empleados',
            'Ver productos',
            'Ver partidas',
            'Ver partidas específicas',
            'Exportar partidas específicas',
            'Ver dependencias',
            'Ver delegaciones',
            'Ver delegados',
            'Ver periodos',
        ])->where('guard_name', $guard)->get());

        $admin = Role::query()->firstOrCreate(['name' => 'Administrador', 'guard_name' => $guard]);
        $admin->syncPermissions($all);

        foreach (config('sivso.super_admin_rfcs', []) as $rfc) {
            $user = User::query()->where('rfc', $rfc)->first();
            if ($user !== null) {
                $user->assignRole('Administrador');
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
