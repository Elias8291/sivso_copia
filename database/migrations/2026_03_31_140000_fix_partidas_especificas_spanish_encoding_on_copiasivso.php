<?php

use App\Support\SpanishQuestionMarkArtifacts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'copiasivso';

    public function up(): void
    {
        $c = $this->connection;
        if (! Schema::connection($c)->hasTable('partidas_especificas')) {
            return;
        }

        $db = DB::connection($c);
        $now = now();

        foreach (['descripcion', 'clave', 'clave_partida'] as $col) {
            if (! Schema::connection($c)->hasColumn('partidas_especificas', $col)) {
                continue;
            }
            $db->table('partidas_especificas')
                ->where($col, 'like', '%?%')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($db, $col, $now) {
                    foreach ($rows as $row) {
                        $raw = $row->{$col};
                        if ($raw === null) {
                            continue;
                        }
                        $orig = (string) $raw;
                        $fixed = SpanishQuestionMarkArtifacts::fix($orig);
                        if ($fixed === $orig) {
                            continue;
                        }
                        $db->table('partidas_especificas')->where('id', $row->id)->update([
                            $col => $fixed,
                            'updated_at' => $now,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Irreversible: no se restauran los ? originales.
    }
};
