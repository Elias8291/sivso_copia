<?php

namespace App\Console\Commands;

use App\Support\SpanishQuestionMarkArtifacts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportPartidasEspecificasCsvCommand extends Command
{
    protected $signature = 'sivso:import-partidas-especificas-csv
                            {--path= : Ruta absoluta al CSV (cabecera: id,partida_id,anio,clave,descripcion,clave_partida,created_at,updated_at)}
                            {--connection=copiasivso : Conexión MySQL}
                            {--dry-run : Validar y mostrar resumen sin escribir en BD}
                            {--no-encoding-fix : No aplicar corrección de ? → tildes/ñ al importar}
                            {--encoding=auto : auto|utf-8|windows-1252 — auto = UTF-8 si hay BOM, si no Windows-1252 (Excel ANSI)}';

    protected $description = 'Valida un CSV y reemplaza solo la tabla partidas_especificas (conserva id para no romper solicitudes)';

    public function handle(): int
    {
        $path = (string) $this->option('path');
        if ($path === '') {
            $this->error('Indica la ruta: --path="C:\\ruta\\partidas_especificas.csv"');

            return self::FAILURE;
        }
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (! is_readable($path)) {
            $this->error("No se puede leer el archivo: {$path}");

            return self::FAILURE;
        }

        $conn = (string) $this->option('connection');
        $db = DB::connection($conn);
        $dry = (bool) $this->option('dry-run');
        $applyFix = ! (bool) $this->option('no-encoding-fix');

        if (! Schema::connection($conn)->hasTable('partidas_especificas')) {
            $this->error("No existe la tabla partidas_especificas en la conexión {$conn}.");

            return self::FAILURE;
        }
        if (! Schema::connection($conn)->hasTable('partidas')) {
            $this->error("No existe la tabla partidas en la conexión {$conn}.");

            return self::FAILURE;
        }

        $partidaIds = $db->table('partidas')->pluck('id')->flip()->all();

        $fh = fopen($path, 'r');
        if ($fh === false) {
            $this->error('No se pudo abrir el CSV.');

            return self::FAILURE;
        }

        $bom = fread($fh, 3);
        $hasUtf8Bom = ($bom === "\xEF\xBB\xBF");
        if (! $hasUtf8Bom) {
            rewind($fh);
        }

        $encodingOpt = strtolower(trim((string) $this->option('encoding')));
        $csvEncoding = match ($encodingOpt) {
            'utf-8', 'utf8' => 'UTF-8',
            'windows-1252', 'cp1252', 'ansi' => 'Windows-1252',
            'auto', '' => $hasUtf8Bom ? 'UTF-8' : 'Windows-1252',
            default => null,
        };
        if ($csvEncoding === null) {
            fclose($fh);
            $this->error('encoding inválido. Usa auto, utf-8 o windows-1252.');

            return self::FAILURE;
        }
        if ($csvEncoding === 'Windows-1252') {
            $this->line('  (CSV interpretado como Windows-1252 / Excel ANSI → UTF-8 para MySQL)');
        }

        $header = fgetcsv($fh);
        if ($header === false) {
            fclose($fh);
            $this->error('CSV vacío o sin cabecera.');

            return self::FAILURE;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $expected = ['id', 'partida_id', 'anio', 'clave', 'descripcion', 'clave_partida', 'created_at', 'updated_at'];
        if ($header !== $expected) {
            fclose($fh);
            $this->error('Cabecera incorrecta. Esperada: '.implode(',', $expected));
            $this->line('Recibida: '.implode(',', $header));

            return self::FAILURE;
        }

        $rows = [];
        $errors = [];
        $lineNum = 1;
        $seenIds = [];

        while (($data = fgetcsv($fh)) !== false) {
            $lineNum++;
            if ($data === [null] || $data === false || (count($data) === 1 && trim((string) ($data[0] ?? '')) === '')) {
                continue;
            }
            if (count($data) < 8) {
                $errors[] = "Línea {$lineNum}: se esperaban 8 columnas, hay ".count($data);

                continue;
            }

            [$idRaw, $partidaIdRaw, $anioRaw, $claveRaw, $descRaw, $clavePartidaRaw, $createdRaw, $updatedRaw] = $data;

            $idRaw = $this->decodeCsvField((string) $idRaw, $csvEncoding);
            $partidaIdRaw = $this->decodeCsvField((string) $partidaIdRaw, $csvEncoding);
            $anioRaw = $this->decodeCsvField((string) $anioRaw, $csvEncoding);
            $claveRaw = $this->decodeCsvField((string) $claveRaw, $csvEncoding);
            $descRaw = $this->decodeCsvField((string) $descRaw, $csvEncoding);
            $clavePartidaRaw = $this->decodeCsvField((string) $clavePartidaRaw, $csvEncoding);
            $createdRaw = $this->decodeCsvField((string) $createdRaw, $csvEncoding);
            $updatedRaw = $this->decodeCsvField((string) $updatedRaw, $csvEncoding);

            $id = filter_var(trim((string) $idRaw), FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                $errors[] = "Línea {$lineNum}: id inválido";

                continue;
            }
            if (isset($seenIds[$id])) {
                $errors[] = "Línea {$lineNum}: id duplicado {$id}";

                continue;
            }
            $seenIds[$id] = true;

            $partidaId = filter_var(trim((string) $partidaIdRaw), FILTER_VALIDATE_INT);
            if ($partidaId === false || $partidaId < 1) {
                $errors[] = "Línea {$lineNum}: partida_id inválido";

                continue;
            }
            if (! isset($partidaIds[$partidaId])) {
                $errors[] = "Línea {$lineNum}: partida_id={$partidaId} no existe en tabla partidas";

                continue;
            }

            $anio = filter_var(trim((string) $anioRaw), FILTER_VALIDATE_INT);
            if ($anio === false || $anio < 1990 || $anio > 2100) {
                $errors[] = "Línea {$lineNum}: anio inválido";

                continue;
            }

            $clave = trim((string) $claveRaw);
            if ($clave === '' || strlen($clave) > 512) {
                $errors[] = "Línea {$lineNum}: clave vacía o mayor a 512 caracteres";

                continue;
            }

            $desc = trim((string) $descRaw);
            $desc = $desc === '' ? null : $desc;
            $clavePartida = trim((string) $clavePartidaRaw);
            $clavePartida = $clavePartida === '' ? null : $clavePartida;
            if ($clavePartida !== null && strlen($clavePartida) > 512) {
                $errors[] = "Línea {$lineNum}: clave_partida mayor a 512 caracteres";

                continue;
            }

            if ($applyFix) {
                $clave = SpanishQuestionMarkArtifacts::fix($clave);
                $desc = $desc !== null ? SpanishQuestionMarkArtifacts::fix($desc) : null;
                $clavePartida = $clavePartida !== null ? SpanishQuestionMarkArtifacts::fix($clavePartida) : null;
            }

            $createdAt = $this->parseSqlDateTime((string) $createdRaw) ?? now()->format('Y-m-d H:i:s');
            $updatedAt = $this->parseSqlDateTime((string) $updatedRaw) ?? now()->format('Y-m-d H:i:s');

            $rows[] = [
                'id' => $id,
                'partida_id' => $partidaId,
                'anio' => $anio,
                'clave' => $clave,
                'descripcion' => $desc,
                'clave_partida' => $clavePartida,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];
        }
        fclose($fh);

        if ($errors !== []) {
            $this->error('Validación fallida ('.count($errors).' problemas):');
            foreach (array_slice($errors, 0, 30) as $e) {
                $this->line('  '.$e);
            }
            if (count($errors) > 30) {
                $this->line('  … y '.(count($errors) - 30).' más.');
            }

            return self::FAILURE;
        }

        if ($rows === []) {
            $this->warn('No hay filas de datos para importar.');

            return self::INVALID;
        }

        $this->info('CSV válido: '.count($rows).' filas.');

        if ($dry) {
            $this->info('Dry-run: no se modificó la base de datos.');

            return self::SUCCESS;
        }

        usort($rows, fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        $db->unprepared('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');

        $db->statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $db->table('partidas_especificas')->truncate();
            foreach (array_chunk($rows, 250) as $chunk) {
                $db->table('partidas_especificas')->insert($chunk);
            }
            $maxId = (int) $db->table('partidas_especificas')->max('id');
            if ($maxId > 0) {
                $db->statement('ALTER TABLE partidas_especificas AUTO_INCREMENT = '.($maxId + 1));
            }
        } finally {
            $db->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Importación lista: '.count($rows).' registros en partidas_especificas (ids preservados).');

        return self::SUCCESS;
    }

    private function decodeCsvField(string $value, string $csvEncoding): string
    {
        if ($value === '' || $csvEncoding === 'UTF-8') {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', $csvEncoding);
    }

    private function parseSqlDateTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);

        return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
    }
}
