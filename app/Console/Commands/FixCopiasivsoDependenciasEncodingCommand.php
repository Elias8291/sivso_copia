<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixCopiasivsoDependenciasEncodingCommand extends Command
{
    protected $signature = 'sivso:fix-dependencias-encoding
                            {--connection=copiasivso : Conexión MySQL}
                            {--min-similarity=82 : Porcentaje mínimo de similar_text para emparejar}
                            {--dry-run : Solo mostrar el plan, sin modificar}';

    protected $description = 'Fusiona dependencias duplicadas cuyo nombre tiene ? en lugar de tildes/ñ, reasigna FKs y corrige texto en concentrado';

    public function handle(): int
    {
        $conn = (string) $this->option('connection');
        $minPct = (float) $this->option('min-similarity');
        $dry = (bool) $this->option('dry-run');

        $db = DB::connection($conn);
        if (! $db->getSchemaBuilder()->hasTable('dependencias')) {
            $this->error("No existe la tabla dependencias en {$conn}.");

            return self::FAILURE;
        }

        $good = $db->table('dependencias')->where('nombre', 'not like', '%?%')->orderBy('id')->get();
        $bad = $db->table('dependencias')->where('nombre', 'like', '%?%')->orderBy('id')->get();

        if ($bad->isEmpty()) {
            $this->info('No hay dependencias con caracteres ? en el nombre.');

            return self::SUCCESS;
        }

        $goodRows = $good->all();
        $mappings = [];

        foreach ($bad as $b) {
            $best = null;
            $bestPct = 0.0;
            foreach ($goodRows as $cand) {
                similar_text($b->nombre, $cand->nombre, $pct);
                if ($pct > $bestPct) {
                    $bestPct = $pct;
                    $best = $cand;
                }
            }
            if ($best === null || $bestPct < $minPct) {
                $this->error("No hay coincidencia segura para id={$b->id}: {$b->nombre} (mejor: ".($bestPct ?? 0).'%)');

                return self::FAILURE;
            }
            $mappings[] = [
                'bad_id' => (int) $b->id,
                'good_id' => (int) $best->id,
                'bad_nombre' => $b->nombre,
                'good_nombre' => $best->nombre,
                'similarity' => $bestPct,
            ];
            $this->line(sprintf(
                '%d → %d (%.1f%%) %s',
                $b->id,
                $best->id,
                $bestPct,
                $best->nombre
            ));
        }

        if ($dry) {
            $this->warn('Dry-run: no se aplicaron cambios.');

            return self::SUCCESS;
        }

        $db->transaction(function () use ($conn, $mappings) {
            foreach ($mappings as $map) {
                $badId = $map['bad_id'];
                $goodId = $map['good_id'];
                $badNombre = $map['bad_nombre'];
                $goodNombre = $map['good_nombre'];

                $db = DB::connection($conn);

                $db->table('empleados')->where('dependencia_id', $badId)->update(['dependencia_id' => $goodId]);

                if ($db->getSchemaBuilder()->hasTable('cupos_presupuesto')) {
                    $this->mergeCuposPresupuesto($conn, $badId, $goodId);
                }

                $this->mergeDependenciaDelegacion($conn, $badId, $goodId);

                if ($db->getSchemaBuilder()->hasTable('concentrado')) {
                    $db->table('concentrado')->where('dependencia', $badNombre)->update(['dependencia' => $goodNombre]);
                    $db->table('concentrado')->where('ur_dependencia', $badNombre)->update(['ur_dependencia' => $goodNombre]);
                }

                $db->table('dependencias')->where('id', $badId)->delete();
            }
        });

        $this->info('Corrección aplicada: FKs fusionadas, concentrado actualizado y filas duplicadas eliminadas.');

        return self::SUCCESS;
    }

    private function mergeCuposPresupuesto(string $conn, int $badId, int $goodId): void
    {
        $db = DB::connection($conn);
        $cupos = $db->table('cupos_presupuesto')->where('dependencia_id', $badId)->get();
        foreach ($cupos as $cupo) {
            $conflict = $db->table('cupos_presupuesto')
                ->where('dependencia_id', $goodId)
                ->where('partida_id', $cupo->partida_id)
                ->where('tipo_partida_especifica_id', $cupo->tipo_partida_especifica_id)
                ->where('anio', $cupo->anio)
                ->exists();
            if ($conflict) {
                $db->table('cupos_presupuesto')->where('id', $cupo->id)->delete();
            } else {
                $db->table('cupos_presupuesto')->where('id', $cupo->id)->update(['dependencia_id' => $goodId]);
            }
        }
    }

    private function mergeDependenciaDelegacion(string $conn, int $badId, int $goodId): void
    {
        $db = DB::connection($conn);
        $rows = $db->table('dependencia_delegacion')->where('dependencia_id', $badId)->get();
        foreach ($rows as $row) {
            $exists = $db->table('dependencia_delegacion')
                ->where('dependencia_id', $goodId)
                ->where('delegacion_id', $row->delegacion_id)
                ->exists();
            if ($exists) {
                $db->table('dependencia_delegacion')
                    ->where('dependencia_id', $badId)
                    ->where('delegacion_id', $row->delegacion_id)
                    ->delete();
            } else {
                $db->table('dependencia_delegacion')
                    ->where('dependencia_id', $badId)
                    ->where('delegacion_id', $row->delegacion_id)
                    ->update(['dependencia_id' => $goodId]);
            }
        }
    }
}
