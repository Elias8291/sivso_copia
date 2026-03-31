<?php

namespace Database\Seeders;

use App\Support\CopiasivsoDatabaseCsvSnapshot;
use Illuminate\Database\Seeder;

/**
 * Carga el snapshot CSV: trunca según _manifest.json y ejecuta un seeder por tabla en orden de FK.
 *
 * Alternativa monolítica (mismo resultado): CopiasivsoDatabaseCsvSnapshot::importFromDirectory (tests/herramientas).
 *
 *   php artisan db:seed --class=CopiasivsoCsvSeeder
 */
class CopiasivsoCsvSeeder extends Seeder
{
    /** @var array<string, class-string<Seeder>> */
    private const TABLE_SEEDER = [
        'concentrado' => ConcentradoSeeder::class,
        'delegaciones' => DelegacionesSeeder::class,
        'delegado' => DelegadoSeeder::class,
        'delegados' => DelegadosSeeder::class,
        'delegado_delegacion' => DelegadoDelegacionSeeder::class,
        'dependences' => DependencesSeeder::class,
        'dependencias' => DependenciasSeeder::class,
        'dependencia_delegacion' => DependenciaDelegacionSeeder::class,
        'empleados' => EmpleadosSeeder::class,
        'partidas' => PartidasSeeder::class,
        'partidas_especificas' => PartidasEspecificasSeeder::class,
        'partidas_por_ejercicio' => PartidasPorEjercicioSeeder::class,
        'periodos' => PeriodosSeeder::class,
        'permissions' => PermissionsSeeder::class,
        'model_has_permissions' => ModelHasPermissionsSeeder::class,
        'propuesta' => PropuestaSeeder::class,
        'roles' => RolesSeeder::class,
        'model_has_roles' => ModelHasRolesSeeder::class,
        'role_has_permissions' => RoleHasPermissionsSeeder::class,
        'tipos_partida_especifica' => TiposPartidaEspecificaSeeder::class,
        'cupos_presupuesto' => CuposPresupuestoSeeder::class,
        'productos' => ProductosSeeder::class,
        'producto_precios' => ProductoPreciosSeeder::class,
        'solicitudes_vestuario' => SolicitudesVestuarioSeeder::class,
        'users' => UsersSeeder::class,
    ];

    public function run(): void
    {
        $dir = CopiasivsoDatabaseCsvSnapshot::csvDirectoryAbsolute();
        $this->call(CopiasivsoTruncateCsvSnapshotSeeder::class);

        foreach (CopiasivsoDatabaseCsvSnapshot::manifestInsertOrder($dir) as $table) {
            $class = self::TABLE_SEEDER[$table] ?? null;
            if ($class === null) {
                throw new \RuntimeException(
                    "La tabla «{$table}» está en _manifest.json insert_order pero no tiene seeder en CopiasivsoCsvSeeder::TABLE_SEEDER."
                );
            }
            $this->call($class);
        }

        $this->command?->info('CopiasivsoCsvSeeder: todas las tablas del manifest procesadas.');
    }
}
