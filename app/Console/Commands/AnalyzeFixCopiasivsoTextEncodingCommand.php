<?php

namespace App\Console\Commands;

use App\Support\SpanishQuestionMarkArtifacts;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class AnalyzeFixCopiasivsoTextEncodingCommand extends Command
{
    protected $signature = 'sivso:analyze-fix-text-encoding
                            {--connection=copiasivso : Conexión MySQL}
                            {--dry-run : No escribe en BD; solo muestra conteos}
                            {--report : Tras simular corrección, lista tokens con ? no resueltos (frecuencia)}';

    protected $description = 'Analiza tablas con texto dañado (?), aplica corrección por palabras y actualiza filas';

    /** @var array<string, int> */
    private array $unresolvedTokenFreq = [];

    public function handle(): int
    {
        $conn = (string) $this->option('connection');
        $dry = (bool) $this->option('dry-run');
        $report = (bool) $this->option('report');

        $db = DB::connection($conn);

        $specs = [
            ['partidas_especificas', ['descripcion', 'clave', 'clave_partida']],
            ['partidas', ['descripcion']],
            ['productos', ['descripcion']],
            ['solicitudes_vestuario', ['clave_partida_snapshot', 'clave_para_ejercicio_snapshot']],
            ['concentrado', ['descripcion', 'partida_descripcion']],
        ];

        $totalUpdates = 0;
        foreach ($specs as [$table, $columns]) {
            if (! $db->getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            foreach ($columns as $col) {
                if (! $db->getSchemaBuilder()->hasColumn($table, $col)) {
                    continue;
                }
                $totalUpdates += $this->processColumn($db, $table, $col, $dry, $report);
            }
        }

        if ($dry) {
            $this->info("Dry-run: habría actualizado {$totalUpdates} celdas con cambios.");
        } else {
            $this->info("Actualizado: {$totalUpdates} celdas corregidas.");
        }

        if ($report && $this->unresolvedTokenFreq !== []) {
            arsort($this->unresolvedTokenFreq);
            $this->newLine();
            $this->warn('Tokens que aún contienen ? (añade en config/sivso_encoding_overrides.php):');
            $i = 0;
            foreach ($this->unresolvedTokenFreq as $token => $freq) {
                $this->line("  [{$freq}] {$token}");
                if (++$i >= 80) {
                    $this->line('  … (mostrando solo los 80 primeros)');

                    break;
                }
            }
        }

        return self::SUCCESS;
    }

    private function processColumn(Connection $db, string $table, string $col, bool $dry, bool $report): int
    {
        $updated = 0;
        $hasId = $db->getSchemaBuilder()->hasColumn($table, 'id');
        $hasUpdatedAt = $db->getSchemaBuilder()->hasColumn($table, 'updated_at');
        $processor = function ($rows) use ($db, $table, $col, $dry, $report, $hasId, $hasUpdatedAt, &$updated) {
            foreach ($rows as $row) {
                $raw = $row->{$col};
                if ($raw === null) {
                    continue;
                }
                $orig = (string) $raw;
                $fixed = SpanishQuestionMarkArtifacts::fix($orig);
                if ($report && str_contains($fixed, '?')) {
                    $this->collectUnresolvedTokens($fixed);
                }
                if ($fixed === $orig) {
                    continue;
                }
                $updated++;
                if (! $dry && $hasId) {
                    $payload = [$col => $fixed];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }
                    $db->table($table)->where('id', $row->id)->update($payload);
                }
            }
        };

        $base = $db->table($table)->where($col, 'like', '%?%');
        if ($hasId) {
            $base->orderBy('id')->chunkById(500, $processor);
        } else {
            $base->orderBy($col)->chunk(500, $processor);
        }

        return $updated;
    }

    private function collectUnresolvedTokens(string $text): void
    {
        if (! preg_match_all('/(?<![\p{L}])[\p{L}]{1,60}(?:\?[\p{L}]{1,60})+(?![\p{L}])/u', $text, $m)) {
            if (str_contains($text, '?')) {
                $this->unresolvedTokenFreq['(texto con ? sin token letra)'] = ($this->unresolvedTokenFreq['(texto con ? sin token letra)'] ?? 0) + 1;
            }

            return;
        }
        foreach ($m[0] as $token) {
            $k = (string) $token;
            $this->unresolvedTokenFreq[$k] = ($this->unresolvedTokenFreq[$k] ?? 0) + 1;
        }
    }
}
