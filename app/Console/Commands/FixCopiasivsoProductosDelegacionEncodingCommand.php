<?php

namespace App\Console\Commands;

use App\Support\SpanishQuestionMarkArtifacts;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class FixCopiasivsoProductosDelegacionEncodingCommand extends Command
{
    protected $signature = 'sivso:fix-productos-delegacion-encoding
                            {--connection=copiasivso : Conexión MySQL}
                            {--min-similarity=85 : Porcentaje mínimo de similar_text}
                            {--dry-run : Solo mostrar el plan, sin modificar}';

    protected $description = 'Corrige ? por tildes/ñ en textos (propuesta, concentrado, partidas, productos…) y sincroniza productos con propuesta';

    public function handle(): int
    {
        $conn = (string) $this->option('connection');
        $minPct = (float) $this->option('min-similarity');
        $dry = (bool) $this->option('dry-run');

        $db = DB::connection($conn);

        $nRepl = $this->applyKnownQuestionMarkFixesAcrossTables($db, $dry);
        $nSync = $this->fixProductosFromLinkedPropuesta($db, $dry);
        $nDel = $this->fixLegacyDelegacion($db, $minPct, $dry);
        $nProd = $this->fixProductosOrphans($db, $minPct, $dry);
        $nRepl2 = $this->applyKnownQuestionMarkFixesAcrossTables($db, $dry);

        if (! $dry) {
            $this->info("Listo: reemplazos ?→acentos {$nRepl}+{$nRepl2}, productos←propuesta {$nSync}, delegación {$nDel}, productos huérfanos {$nProd}.");
        } else {
            $this->info("Dry-run: reemplazos {$nRepl}+{$nRepl2}, sync propuesta {$nSync}, delegación {$nDel}, huérfanos {$nProd}.");
        }

        return self::SUCCESS;
    }

    /**
     * Sustituye patrones típicos donde ? reemplazó Ó, Ñ, etc. (importación / charset).
     *
     * @return int celdas actualizadas
     */
    private function applyKnownQuestionMarkFixesAcrossTables(Connection $db, bool $dry): int
    {
        $specs = [
            ['propuesta', ['descripcion']],
            ['concentrado', ['descripcion', 'partida_descripcion']],
            ['partidas', ['descripcion']],
            ['partidas_especificas', ['descripcion']],
            ['productos', ['descripcion']],
        ];

        $updated = 0;
        foreach ($specs as [$table, $columns]) {
            if (! $db->getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            foreach ($columns as $col) {
                if (! $db->getSchemaBuilder()->hasColumn($table, $col)) {
                    continue;
                }
                $db->table($table)->where($col, 'like', '%?%')->orderBy('id')->chunkById(500, function ($rows) use ($db, $table, $col, $dry, &$updated) {
                    foreach ($rows as $row) {
                        $orig = (string) $row->{$col};
                        $fixed = SpanishQuestionMarkArtifacts::fix($orig);
                        if ($fixed === $orig) {
                            continue;
                        }
                        $updated++;
                        if (! $dry) {
                            $db->table($table)->where('id', $row->id)->update([$col => $fixed]);
                        }
                        if ($this->output->isVerbose()) {
                            $this->line("{$table}.{$col} id={$row->id}");
                        }
                    }
                });
            }
        }

        return $updated;
    }

    /**
     * Copia texto correcto desde propuesta cuando el producto sigue ligado por legacy_propuesta_id.
     *
     * @return int filas actualizadas
     */
    private function fixProductosFromLinkedPropuesta(Connection $db, bool $dry): int
    {
        if (! $db->getSchemaBuilder()->hasTable('productos') || ! $db->getSchemaBuilder()->hasTable('propuesta')) {
            return 0;
        }

        $rows = $db->table('productos')
            ->where('descripcion', 'like', '%?%')
            ->whereNotNull('legacy_propuesta_id')
            ->orderBy('id')
            ->get(['id', 'legacy_propuesta_id', 'descripcion']);

        $updated = 0;
        foreach ($rows as $p) {
            $pr = $db->table('propuesta')->where('id', $p->legacy_propuesta_id)->first();
            if ($pr === null) {
                continue;
            }
            $clean = trim((string) $pr->descripcion);
            if ($clean === '' || str_contains($clean, '?')) {
                continue;
            }
            $updated++;
            if (! $dry) {
                $db->table('productos')->where('id', $p->id)->update([
                    'descripcion' => $clean,
                    'marca' => $pr->marca,
                    'unidad_medida' => $pr->unidad,
                    'codigo' => $pr->codigo,
                    'medida' => $pr->medida,
                    'updated_at' => now(),
                ]);
            }
            if ($this->output->isVerbose()) {
                $this->line("producto {$p->id} ← propuesta {$pr->id} (sync por legacy)");
            }
        }

        return $updated;
    }

    /**
     * @return int filas afectadas (o contadas en dry-run)
     */
    private function fixLegacyDelegacion(Connection $db, float $minPct, bool $dry): int
    {
        if (! $db->getSchemaBuilder()->hasTable('delegacion') || ! $db->getSchemaBuilder()->hasTable('dependencias')) {
            $this->warn('Tablas delegacion o dependencias no disponibles; se omite delegacion.');

            return 0;
        }

        $deps = $db->table('dependencias')->get(['id', 'nombre']);
        if ($deps->isEmpty()) {
            return 0;
        }

        $badRows = $db->table('delegacion')->where('dependencia', 'like', '%?%')->orderBy('id')->get();
        $count = 0;

        foreach ($badRows as $row) {
            $best = null;
            $bestPct = 0.0;
            $nombre = (string) $row->dependencia;
            foreach ($deps as $d) {
                similar_text($nombre, (string) $d->nombre, $pct);
                if ($pct > $bestPct) {
                    $bestPct = $pct;
                    $best = $d;
                }
            }
            if ($best === null || $bestPct < $minPct) {
                $this->warn("delegacion id={$row->id}: sin coincidencia ≥{$minPct}% (mejor {$bestPct}%).");

                continue;
            }
            $count++;
            if (! $dry) {
                $db->table('delegacion')->where('id', $row->id)->update(['dependencia' => $best->nombre]);
            }
            if ($this->output->isVerbose()) {
                $this->line("delegacion {$row->id} → dependencia id {$best->id} ({$bestPct}%)");
            }
        }

        return $count;
    }

    /**
     * @return int filas actualizadas
     */
    private function fixProductosOrphans(Connection $db, float $minPct, bool $dry): int
    {
        if (! $db->getSchemaBuilder()->hasTable('productos') || ! $db->getSchemaBuilder()->hasTable('propuesta')) {
            $this->warn('Tablas productos o propuesta no disponibles; se omiten productos.');

            return 0;
        }

        $partidaNoById = $db->table('partidas')->pluck('no_partida', 'id')->all();

        $propuestasByPartida = [];
        foreach ($db->table('propuesta')->get() as $pr) {
            if (str_contains((string) $pr->descripcion, '?')) {
                continue;
            }
            $no = (int) $pr->partida;
            $propuestasByPartida[$no][] = $pr;
        }

        $goodProductosByPartida = [];
        foreach (
            $db->table('productos')
                ->whereNotNull('legacy_propuesta_id')
                ->where('descripcion', 'not like', '%?%')
                ->get(['id', 'partida_id', 'descripcion', 'marca', 'unidad_medida', 'codigo', 'medida']) as $gp
        ) {
            $pid = (int) $gp->partida_id;
            $goodProductosByPartida[$pid][] = $gp;
        }

        $orphans = $db->table('productos')
            ->whereNull('legacy_propuesta_id')
            ->where('descripcion', 'like', '%?%')
            ->orderBy('id')
            ->get();

        $updated = 0;

        foreach ($orphans as $p) {
            $no = $partidaNoById[$p->partida_id] ?? null;
            if ($no === null) {
                continue;
            }

            $matchPr = $this->pickBestFreePropuesta(
                $db,
                (int) $p->id,
                $p->descripcion,
                $propuestasByPartida[$no] ?? [],
                $minPct
            );
            if ($matchPr === null && $minPct > 72) {
                $matchPr = $this->pickBestFreePropuesta(
                    $db,
                    (int) $p->id,
                    $p->descripcion,
                    $propuestasByPartida[$no] ?? [],
                    72.0
                );
            }
            if ($matchPr !== null) {
                if (! $dry) {
                    $db->table('productos')->where('id', $p->id)->update([
                        'descripcion' => $matchPr->descripcion,
                        'marca' => $matchPr->marca,
                        'unidad_medida' => $matchPr->unidad,
                        'codigo' => $matchPr->codigo,
                        'medida' => $matchPr->medida,
                        'legacy_propuesta_id' => $matchPr->id,
                        'updated_at' => now(),
                    ]);
                }
                $updated++;
                if ($this->output->isVerbose()) {
                    $this->line("producto {$p->id} ← propuesta {$matchPr->id} (partida {$no})");
                }

                continue;
            }

            $sibling = $this->bestProductoSiblingMatch(
                $p->descripcion,
                $goodProductosByPartida[(int) $p->partida_id] ?? [],
                $minPct
            );
            if ($sibling === null && $minPct > 72) {
                $sibling = $this->bestProductoSiblingMatch(
                    $p->descripcion,
                    $goodProductosByPartida[(int) $p->partida_id] ?? [],
                    72.0
                );
            }
            if ($sibling === null) {
                $sibling = $this->bestProductoSiblingMatch(
                    $p->descripcion,
                    $goodProductosByPartida[(int) $p->partida_id] ?? [],
                    65.0
                );
            }
            if ($sibling === null) {
                $this->warn("producto id={$p->id}: sin fuente UTF-8 (partida {$no}).");

                continue;
            }
            if (! $dry) {
                $db->table('productos')->where('id', $p->id)->update([
                    'descripcion' => $sibling->descripcion,
                    'marca' => $sibling->marca,
                    'unidad_medida' => $sibling->unidad_medida,
                    'codigo' => $sibling->codigo,
                    'medida' => $sibling->medida,
                    'updated_at' => now(),
                ]);
            }
            $updated++;
            if ($this->output->isVerbose()) {
                $this->line("producto {$p->id} ← hermano producto {$sibling->id} (misma partida)");
            }
        }

        return $updated;
    }

    /**
     * Elige la propuesta con mayor similaridad cuyo id aún no está tomado en `productos.legacy_propuesta_id`.
     *
     * @param  array<int, object>  $candidates
     */
    private function pickBestFreePropuesta(
        Connection $db,
        int $productoId,
        string $descripcion,
        array $candidates,
        float $minPct
    ): ?object {
        $scored = [];
        foreach ($candidates as $pr) {
            similar_text($descripcion, (string) $pr->descripcion, $pct);
            if ($pct >= $minPct) {
                $scored[] = ['pr' => $pr, 'pct' => $pct];
            }
        }
        usort($scored, fn ($a, $b) => $b['pct'] <=> $a['pct']);
        foreach ($scored as $item) {
            $pid = (int) $item['pr']->id;
            $taken = $db->table('productos')
                ->where('legacy_propuesta_id', $pid)
                ->where('id', '!=', $productoId)
                ->exists();
            if (! $taken) {
                return $item['pr'];
            }
        }

        return null;
    }

    /**
     * @param  array<int, object>  $siblings
     */
    private function bestProductoSiblingMatch(string $descripcion, array $siblings, float $minPct): ?object
    {
        $best = null;
        $bestPct = 0.0;
        foreach ($siblings as $s) {
            similar_text($descripcion, (string) $s->descripcion, $pct);
            if ($pct > $bestPct) {
                $bestPct = $pct;
                $best = $s;
            }
        }

        return $best !== null && $bestPct >= $minPct ? $best : null;
    }
}
