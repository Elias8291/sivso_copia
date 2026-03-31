<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\SivsoPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea permisos y roles del SIVSO en la BD por defecto (tablas Spatie).
 *
 * Roles:
 * - super-admin: sin permisos en BD; pasa todo vía Gate::before (ver AppServiceProvider)
 * - administrador: todo excepto gestión de roles y permisos
 * - gestor-catalogo: catálogo vestuario, estructura (solo lectura salvo delegados), reportes, periodos solo ver
 * - consulta: solo lectura en módulos operativos + reportes
 * - delegado: mi delegación + reporte delegados
 */
class SivsoRbacSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (SivsoPermissions::all() as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );
        }

        $super = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $super->syncPermissions([]);

        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin->syncPermissions(SivsoPermissions::administrador());

        $gestor = Role::firstOrCreate(['name' => 'gestor-catalogo', 'guard_name' => 'web']);
        $gestor->syncPermissions(SivsoPermissions::gestorCatalogo());

        $consulta = Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);
        $consulta->syncPermissions(SivsoPermissions::consulta());

        $delegado = Role::firstOrCreate(['name' => 'delegado', 'guard_name' => 'web']);
        $delegado->syncPermissions(SivsoPermissions::delegado());

        $this->assignSuperAdminToSeededUser();
    }

    private function assignSuperAdminToSeededUser(): void
    {
        $rfc = strtoupper((string) env('SIVSO_SUPERUSER_RFC', 'RAJE020226G97'));
        $user = User::query()->where('rfc', $rfc)->first();
        if ($user && ! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }
    }
}
