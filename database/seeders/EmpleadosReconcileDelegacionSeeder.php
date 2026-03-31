<?php

namespace Database\Seeders;

use App\Support\DelegacionClaveUrParser;
use App\Support\LegacyEmpleadosDelegacionMapper;
use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rellena empleados.delegacion_id:
 * 1) Actualiza delegaciones.ur desde el sufijo numérico de la clave si ur está vacío o es 0.
 * 2) Rellena dependencia_delegacion (dependencia.ur = delegacion.ur) cuando el pivote vino vacío del CSV.
 * 3) Si existe la tabla legacy «delegacion» con datos: NUE+UR / persona+UR (LegacyEmpleadosDelegacionMapper).
 * 4) Empleados cuya dependencia tiene exactamente una fila en dependencia_delegacion.
 * 5) Usuarios con NUE + delegado_id: una sola delegación del delegado que coincide con empleado.ur.
 * 6) Opcional (SIVSO_DELEGACION_RECONCILE_FALLBACK_MIN_ID): entre delegaciones del pivote con la misma UR que el empleado, min(id).
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

        if (! $schema->hasTable('delegaciones') || ! $schema->hasTable('dependencias')) {
            $this->command?->warn('Faltan tablas delegaciones o dependencias.');

            return;
        }

        $urBackfilled = $this->backfillDelegacionesUrFromClave($cx);
        if ($urBackfilled > 0) {
            $this->command?->info("delegaciones.ur inferido desde clave: {$urBackfilled} filas.");
        }

        $pivotBefore = $schema->hasTable('dependencia_delegacion')
            ? (int) $cx->table('dependencia_delegacion')->count()
            : 0;
        $this->syncDependenciaDelegacionFromUr($cx);
        $pivotAfter = $schema->hasTable('dependencia_delegacion')
            ? (int) $cx->table('dependencia_delegacion')->count()
            : 0;
        $pivotDelta = $pivotAfter - $pivotBefore;
        if ($pivotDelta > 0) {
            $this->command?->info("dependencia_delegacion: +{$pivotDelta} filas (pares dependencia.ur = delegacion.ur).");
        }

        $legacyUpdated = 0;
        $legacySkipped = 0;
        if ($schema->hasTable('delegacion') && (int) $cx->table('delegacion')->count() > 0) {
            [$legacyUpdated, $legacySkipped] = $this->reconcileFromLegacyDelegacionTable($cx);
            $this->command?->info("Reconciliación tabla legacy «delegacion»: {$legacyUpdated} actualizados; sin match: {$legacySkipped}.");
        } else {
            $this->command?->warn('Sin tabla legacy «delegacion» o vacía: se usan pivote por UR, usuario+NUE y fallback opcional.');
        }

        $u1 = $this->reconcileEmpleadosPivotUniqueDelegacion($cx);
        $this->command?->info("Empleados con dependencia → una sola delegación en pivote: {$u1} actualizados.");

        $u2 = $this->reconcileEmpleadosFromUsersNueDelegado($cx);
        $this->command?->info("Empleados vía users.nue + delegado + UR: {$u2} actualizados.");

        $u3 = $this->reconcileEmpleadosFallbackMinDelegacionId($cx);
        if ($u3 > 0) {
            $this->command?->warn("Fallback min(id) por UR+pivote: {$u3} empleados (revisar subdelegaciones; desactiva SIVSO_DELEGACION_RECONCILE_FALLBACK_MIN_ID si no aplica).");
        }

        $stillNull = (int) $cx->table('empleados')->whereNull('delegacion_id')->count();
        if ($stillNull > 0) {
            $this->command?->warn("Quedan {$stillNull} empleados sin delegacion_id. Exporta la tabla legacy «delegacion» o activa el fallback min id.");
        }
    }

    private function backfillDelegacionesUrFromClave(Connection $cx): int
    {
        $n = 0;
        $cx->table('delegaciones')->orderBy('id')->chunkById(500, function ($rows) use ($cx, &$n) {
            foreach ($rows as $d) {
                $current = $d->ur;
                if ($current !== null && (int) $current > 0) {
                    continue;
                }
                $parsed = DelegacionClaveUrParser::urFromClave((string) $d->clave);
                if ($parsed === null) {
                    continue;
                }
                $cx->table('delegaciones')->where('id', $d->id)->update([
                    'ur' => $parsed,
                    'updated_at' => now(),
                ]);
                $n++;
            }
        });

        return $n;
    }

    private function syncDependenciaDelegacionFromUr(Connection $cx): void
    {
        if (! $cx->getSchemaBuilder()->hasTable('dependencia_delegacion')) {
            return;
        }

        $deps = $cx->table('dependencias')
            ->whereNotNull('ur')
            ->where('ur', '>', 0)
            ->get(['id', 'ur']);

        $dels = $cx->table('delegaciones')
            ->whereNotNull('ur')
            ->where('ur', '>', 0)
            ->get(['id', 'ur']);

        foreach ($deps as $d) {
            foreach ($dels as $del) {
                if ((int) $d->ur !== (int) $del->ur) {
                    continue;
                }
                $cx->table('dependencia_delegacion')->insertOrIgnore([
                    'dependencia_id' => $d->id,
                    'delegacion_id' => $del->id,
                ]);
            }
        }
    }

    /**
     * @return array{0: int, 1: int} [updated, skipped]
     */
    private function reconcileFromLegacyDelegacionTable(Connection $cx): array
    {
        $delIds = $cx->table('delegaciones')->pluck('id', 'clave')->all();
        if ($delIds === []) {
            return [0, 0];
        }

        $lookup = LegacyEmpleadosDelegacionMapper::buildDelegacionClaveLookup($cx->getName(), $delIds);

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

        return [$updated, $skipped];
    }

    private function reconcileEmpleadosPivotUniqueDelegacion(Connection $cx): int
    {
        if (! $cx->getSchemaBuilder()->hasTable('dependencia_delegacion')) {
            return 0;
        }

        $single = $cx->table('dependencia_delegacion')
            ->selectRaw('dependencia_id, MIN(delegacion_id) as delegacion_id')
            ->groupBy('dependencia_id')
            ->havingRaw('COUNT(DISTINCT delegacion_id) = 1')
            ->get();

        if ($single->isEmpty()) {
            return 0;
        }

        $updated = 0;
        foreach ($single as $row) {
            $n = $cx->table('empleados')
                ->where('dependencia_id', $row->dependencia_id)
                ->whereNull('delegacion_id')
                ->update([
                    'delegacion_id' => $row->delegacion_id,
                    'updated_at' => now(),
                ]);
            $updated += $n;
        }

        return $updated;
    }

    private function reconcileEmpleadosFromUsersNueDelegado(Connection $cx): int
    {
        if (! $cx->getSchemaBuilder()->hasTable('users')
            || ! $cx->getSchemaBuilder()->hasTable('delegado_delegacion')) {
            return 0;
        }

        $users = $cx->table('users')
            ->whereNotNull('nue')
            ->where('nue', '!=', '')
            ->whereNotNull('delegado_id')
            ->get(['nue', 'delegado_id']);

        if ($users->isEmpty()) {
            return 0;
        }

        $updated = 0;
        foreach ($users as $u) {
            $nue = trim((string) $u->nue);
            $delegadoId = (int) $u->delegado_id;

            $empleados = $cx->table('empleados')
                ->where('nue', $nue)
                ->whereNull('delegacion_id')
                ->get(['id', 'ur']);

            foreach ($empleados as $e) {
                $eur = $e->ur !== null && $e->ur !== '' ? (int) $e->ur : null;

                $q = $cx->table('delegado_delegacion as dd')
                    ->join('delegaciones as del', 'del.id', '=', 'dd.delegacion_id')
                    ->where('dd.delegado_id', $delegadoId);
                if ($eur !== null && $eur > 0) {
                    $q->where('del.ur', $eur);
                }
                $match = $q->pluck('del.id');

                if ($match->count() !== 1) {
                    continue;
                }

                $cx->table('empleados')->where('id', $e->id)->update([
                    'delegacion_id' => (int) $match->first(),
                    'updated_at' => now(),
                ]);
                $updated++;
            }
        }

        return $updated;
    }

    private function reconcileEmpleadosFallbackMinDelegacionId(Connection $cx): int
    {
        if (! filter_var(config('sivso.delegacion_reconcile_fallback_min_id'), FILTER_VALIDATE_BOOLEAN)) {
            return 0;
        }

        if (! $cx->getSchemaBuilder()->hasTable('dependencia_delegacion')) {
            return 0;
        }

        $updated = 0;
        $cx->table('empleados')->whereNull('delegacion_id')->orderBy('id')->chunkById(500, function ($rows) use ($cx, &$updated) {
            foreach ($rows as $e) {
                if ($e->dependencia_id === null || $e->ur === null || (int) $e->ur <= 0) {
                    continue;
                }

                $minId = $cx->table('dependencia_delegacion as dd')
                    ->join('delegaciones as del', 'del.id', '=', 'dd.delegacion_id')
                    ->where('dd.dependencia_id', $e->dependencia_id)
                    ->where('del.ur', (int) $e->ur)
                    ->min('del.id');

                if ($minId === null) {
                    continue;
                }

                $cx->table('empleados')->where('id', $e->id)->update([
                    'delegacion_id' => (int) $minId,
                    'updated_at' => now(),
                ]);
                $updated++;
            }
        });

        return $updated;
    }
}
