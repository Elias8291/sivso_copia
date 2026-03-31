<?php

namespace Database\Seeders;

use App\Support\LegacyEmpleadosDelegacionMapper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rellena empleados.delegacion_id usando la tabla legacy «delegacion» (NUE+UR, persona+UR).
 * Ejecutar tras CopiasivsoFromCsvSeeder si el CSV trae delegacion_id vacío pero existe «delegacion» en MySQL.
 *
 *   php artisan db:seed --class=EmpleadosReconcileDelegacionSeeder
 */
class EmpleadosReconcileDelegacionSeeder extends Seeder
{
    public function run(): void
    {
        $connection = (string) config('sivso.csv_snapshot_connection', 'copiasivso');
        $cx = DB::connection($connection);
        $schema = $cx->getSchemaBuilder();

        if (! $schema->hasTable('empleados')) {
            $this->command?->warn("No hay tabla empleados en «{$connection}».");

            return;
        }

        if (! $schema->hasTable('delegacion')) {
            $this->command?->warn('No hay tabla legacy «delegacion»; no se puede reconciliar delegacion_id. Exporta esa tabla al snapshot o corre sivso:migrate-copiasivso-legacy.');

            return;
        }

        if (! $schema->hasTable('delegaciones')) {
            $this->command?->warn('No hay tabla delegaciones.');

            return;
        }

        $delIds = $cx->table('delegaciones')->pluck('id', 'clave')->all();
        if ($delIds === []) {
            $this->command?->warn('delegaciones vacía.');

            return;
        }

        $lookup = LegacyEmpleadosDelegacionMapper::buildDelegacionClaveLookup($connection, $delIds);

        $updated = 0;
        $skipped = 0;

        $cx->table('empleados')->orderBy('id')->chunk(500, function ($rows) use ($cx, $lookup, $delIds, &$updated, &$skipped) {
            $depIds = $rows->pluck('dependencia_id')->filter()->unique()->values()->all();
            $depNombres = $depIds === []
                ? []
                : $cx->table('dependencias')->whereIn('id', $depIds)->pluck('nombre', 'id')->all();

            foreach ($rows as $e) {
                $depNombre = isset($e->dependencia_id, $depNombres[$e->dependencia_id])
                    ? (string) $depNombres[$e->dependencia_id]
                    : null;
                $newId = LegacyEmpleadosDelegacionMapper::resolveDelegacionIdForNormalizedEmpleado(
                    $e,
                    $depNombre,
                    $lookup,
                    $delIds
                );
                if ($newId === null) {
                    $skipped++;

                    continue;
                }
                if ((int) ($e->delegacion_id ?? 0) === (int) $newId) {
                    continue;
                }
                $cx->table('empleados')->where('id', $e->id)->update([
                    'delegacion_id' => $newId,
                    'updated_at' => now(),
                ]);
                $updated++;
            }
        });

        $this->command?->info("Reconciliación delegacion_id: {$updated} empleados actualizados; sin match: {$skipped} filas revisadas sin cambio o sin delegación resoluble.");
    }
}
