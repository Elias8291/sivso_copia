<?php

namespace Database\Seeders;

use App\Support\CopiasivsoExcelSnapshot;
use Illuminate\Database\Seeder;

/**
 * Carga tabla por tabla desde .xlsx (cada seeder hace TRUNCATE + insert de un solo archivo).
 * Para importar todo el directorio de una vez (opcional): `db:seed --class=CopiasivsoExcelDirectorySeeder`.
 * La migración habitual de datos en `db:seed` es por CSV (`CopiasivsoCsvSeeder`), no por Excel.
 *
 * Ejemplo manual: php artisan db:seed --class=Database\\Seeders\\DelegacionesSeeder
 */
class CopiasivsoSeeder extends Seeder
{
    public function run(): void
    {
        $dir = CopiasivsoExcelSnapshot::excelDirectoryAbsolute();
        $manifest = $dir.DIRECTORY_SEPARATOR.'_manifest.json';
        if (! is_readable($manifest)) {
            throw new \RuntimeException(
                "Coloca _manifest.json y los .xlsx en: {$dir}"
            );
        }

        $this->call([
            ConcentradoSeeder::class,
            DelegacionesSeeder::class,
            DelegadoSeeder::class,
            DelegadosSeeder::class,
            DelegadoDelegacionSeeder::class,
            DependencesSeeder::class,
            DependenciasSeeder::class,
            DependenciaDelegacionSeeder::class,
            EmpleadosSeeder::class,
            PartidasSeeder::class,
            PartidasEspecificasSeeder::class,
            PartidasPorEjercicioSeeder::class,
            PeriodosSeeder::class,
            PermissionsSeeder::class,
            ModelHasPermissionsSeeder::class,
            PropuestaSeeder::class,
            RolesSeeder::class,
            ModelHasRolesSeeder::class,
            RoleHasPermissionsSeeder::class,
            TiposPartidaEspecificaSeeder::class,
            CuposPresupuestoSeeder::class,
            ProductosSeeder::class,
            ProductoPreciosSeeder::class,
            SolicitudesVestuarioSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
