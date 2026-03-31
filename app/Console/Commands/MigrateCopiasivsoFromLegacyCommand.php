<?php

namespace App\Console\Commands;

use App\Support\SpanishQuestionMarkArtifacts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateCopiasivsoFromLegacyCommand extends Command
{
    protected $signature = 'sivso:migrate-copiasivso-legacy
                            {--anio=2025 : Año para precios, partidas por ejercicio y solicitudes}
                            {--force : Vacía tablas normalizadas y vuelve a importar}';

    protected $description = 'Migra datos de concentrado, propuesta, dependences, delegacion y delegado (BD copiasivso) al esquema normalizado';

    public function handle(): int
    {
        $conn = 'copiasivso';
        $anio = (int) $this->option('anio');
        $db = DB::connection($conn);

        if (! $db->getSchemaBuilder()->hasTable('concentrado')) {
            $this->error('La conexión copiasivso no tiene tabla concentrado.');

            return self::FAILURE;
        }

        if ($this->option('force')) {
            $this->truncateNormalized($conn);
        }

        if (Schema::connection($conn)->hasTable('empleados')
            && $db->table('empleados')->count() > 0
            && ! $this->option('force')) {
            $this->warn('Ya hay empleados en el esquema normalizado. Usa --force para reimportar.');

            return self::INVALID;
        }

        $empleadoIds = [];
        $productoIdsByLegacyPropuesta = [];
        $partidaEspecMap = [];
        $now = now();
        $t0 = microtime(true);
        $step = function (string $label) use (&$t0): void {
            $ms = round((microtime(true) - $t0) * 1000);
            $this->line("  ✓ {$label} ({$ms} ms)");
            $t0 = microtime(true);
        };

        $this->info('Importando catálogos, partidas, productos y empleados (sin transacción única; va mostrando cada paso)…');
        $tiposByCodigo = $this->seedTiposPartidaEspecifica($conn, $now);
        $this->ensureTiposFromPropuesta($conn, $now, $tiposByCodigo);
        $step('Tipos partida específica');
        $depIds = $this->importDependencias($conn, $now);
        $step('Dependencias');
        $delIds = $this->importDelegaciones($conn, $now);
        $step('Delegaciones');
        $this->importDependenciaDelegacion($conn, $depIds, $delIds);
        $step('Relación dependencia–delegación');
        $this->importDelegados($conn, $now, $delIds);
        $step('Delegados');
        $partidaIds = $this->importPartidas($conn, $now);
        $step('Partidas');
        $partidaEspecMap = $this->importPartidasEspecificas($conn, $now, $partidaIds, $anio);
        $step('Partidas específicas (DISTINCT clave2025, descripcion)');
        $this->importPartidasPorEjercicio($conn, $now, $partidaIds, $anio);
        $step('Partidas por ejercicio');
        $productoIdsByLegacyPropuesta = $this->importProductosYPrecios($conn, $now, $partidaIds, $tiposByCodigo, $anio);
        $step('Productos y precios');
        $empleadoIds = $this->importEmpleados($conn, $now, $depIds, $delIds);
        $step('Empleados');

        $this->info('Importando solicitudes desde concentrado (suele ser el paso más largo)…');
        $this->importSolicitudes($conn, now(), $empleadoIds, $productoIdsByLegacyPropuesta, $anio, $partidaEspecMap);

        $this->info('Migración completada (conexión copiasivso).');

        return self::SUCCESS;
    }

    private function truncateNormalized(string $conn): void
    {
        $db = DB::connection($conn);
        $db->statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'solicitudes_vestuario',
            'partidas_especificas',
            'producto_precios',
            'productos',
            'cupos_presupuesto',
            'partidas_por_ejercicio',
            'partidas',
            'empleados',
            'delegado_delegacion',
            'delegados',
            'dependencia_delegacion',
            'delegaciones',
            'dependencias',
            'tipos_partida_especifica',
        ] as $table) {
            if ($db->getSchemaBuilder()->hasTable($table)) {
                $db->table($table)->truncate();
            }
        }
        $db->statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('Tablas normalizadas vaciadas.');
    }

    private function seedTiposPartidaEspecifica(string $conn, $now): array
    {
        $rows = [
            ['codigo' => 244, 'nombre' => 'Partida específica 244'],
            ['codigo' => 246, 'nombre' => 'Partida específica 246'],
        ];
        $map = [];
        foreach ($rows as $r) {
            $id = DB::connection($conn)->table('tipos_partida_especifica')->insertGetId([
                'codigo' => $r['codigo'],
                'nombre' => $r['nombre'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $map[$r['codigo']] = $id;
        }

        return $map;
    }

    private function ensureTiposFromPropuesta(string $conn, $now, array &$tiposByCodigo): void
    {
        $codes = DB::connection($conn)->table('propuesta')->select('partida_especifica')->distinct()->pluck('partida_especifica');
        foreach ($codes as $codigo) {
            if ($codigo === null) {
                continue;
            }
            $c = (int) $codigo;
            if (isset($tiposByCodigo[$c])) {
                continue;
            }
            $id = DB::connection($conn)->table('tipos_partida_especifica')->insertGetId([
                'codigo' => $c,
                'nombre' => 'Partida específica '.$c,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $tiposByCodigo[$c] = $id;
        }
    }

    private function importDependencias(string $conn, $now): array
    {
        $this->line('    … dependencias: importando tabla dependences y fusionando con concentrado (puede tardar si hay muchas UR distintas)');
        $byKey = [];
        foreach (DB::connection($conn)->table('dependences')->orderBy('id')->get() as $row) {
            $key = trim((string) $row->key);
            $id = DB::connection($conn)->table('dependencias')->insertGetId([
                'codigo' => $key !== '' ? $key : null,
                'ur' => null,
                'nombre' => $row->name,
                'ur_texto' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $byKey[$key] = $id;
            $byKey[strtolower(trim($row->name))] = $id;
        }

        $nameById = [];
        foreach (DB::connection($conn)->table('dependencias')->get(['id', 'nombre']) as $d) {
            $nameById[(int) $d->id] = (string) $d->nombre;
        }

        $seenUrNombre = [];
        foreach (DB::connection($conn)->table('concentrado')->select('ur', 'dependencia', 'ur_dependencia')->distinct()->get() as $row) {
            $nombre = trim((string) $row->dependencia);
            if ($nombre === '') {
                continue;
            }
            $ur = $row->ur !== null ? (int) $row->ur : null;
            $k = $ur.'|'.strtolower($nombre);
            if (isset($seenUrNombre[$k])) {
                continue;
            }
            $seenUrNombre[$k] = true;

            $existing = DB::connection($conn)->table('dependencias')->where('nombre', $row->dependencia)->first();
            if ($existing) {
                if ($ur !== null && $existing->ur === null) {
                    $this->assignUrToDependenciaIfFree($conn, (int) $existing->id, $ur, $row->ur_dependencia, $now);
                }

                continue;
            }

            $similarId = $this->findDependenciaIdBySimilarity($nombre, $nameById);
            if ($similarId !== null) {
                $match = DB::connection($conn)->table('dependencias')->where('id', $similarId)->first();
                if ($match && $ur !== null && $match->ur === null) {
                    $this->assignUrToDependenciaIfFree($conn, (int) $match->id, $ur, $row->ur_dependencia, $now);
                }

                continue;
            }

            $urInsert = $ur;
            if ($ur !== null && $this->dependenciaUrTaken($conn, $ur, null)) {
                $urInsert = null;
            }
            $newId = (int) DB::connection($conn)->table('dependencias')->insertGetId([
                'codigo' => $urInsert !== null ? 'U'.$urInsert : ($ur !== null ? 'U'.$ur : null),
                'ur' => $urInsert,
                'nombre' => $row->dependencia,
                'ur_texto' => $row->ur_dependencia,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $nameById[$newId] = (string) $row->dependencia;
        }

        return $this->dependenciasMapByNombreUr($conn);
    }

    private function dependenciaUrTaken(string $conn, int $ur, ?int $exceptId): bool
    {
        $q = DB::connection($conn)->table('dependencias')->where('ur', $ur);
        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }

    private function assignUrToDependenciaIfFree(string $conn, int $dependenciaId, int $ur, $urTexto, $now): void
    {
        if ($this->dependenciaUrTaken($conn, $ur, $dependenciaId)) {
            return;
        }
        DB::connection($conn)->table('dependencias')->where('id', $dependenciaId)->update([
            'ur' => $ur,
            'ur_texto' => $urTexto,
            'updated_at' => $now,
        ]);
    }

    /**
     * Evita duplicar dependencias cuando concentrado trae "?" en lugar de tildes/ñ
     * y ya existe el nombre correcto (p. ej. desde dependences).
     *
     * @param  array<int, string>  $nameById
     */
    private function findDependenciaIdBySimilarity(string $nombre, array $nameById): ?int
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return null;
        }
        $len = strlen($nombre);
        $bestId = null;
        $bestPct = 0.0;
        foreach ($nameById as $id => $candNombre) {
            if ($candNombre === $nombre) {
                return (int) $id;
            }
            $cLen = strlen($candNombre);
            if ($cLen === 0 || $len === 0) {
                continue;
            }
            $longer = max($len, $cLen);
            $shorter = min($len, $cLen);
            if ($shorter > 0 && ($longer / $shorter) > 2.0) {
                continue;
            }
            similar_text($nombre, $candNombre, $pct);
            if ($pct > $bestPct) {
                $bestPct = $pct;
                $bestId = (int) $id;
            }
        }

        return $bestPct >= 90.0 ? $bestId : null;
    }

    private function dependenciasMapByNombreUr(string $conn): array
    {
        $map = ['nombre' => [], 'ur' => []];
        foreach (DB::connection($conn)->table('dependencias')->get() as $d) {
            $map['nombre'][strtolower(trim($d->nombre))] = $d->id;
            if ($d->ur !== null) {
                $map['ur'][(int) $d->ur] = $d->id;
            }
        }

        return $map;
    }

    private function importDelegaciones(string $conn, $now): array
    {
        $hasDelegacion = Schema::connection($conn)->hasTable('delegacion');
        $hasDelegado = Schema::connection($conn)->hasTable('delegado');

        if (! $hasDelegacion && ! $hasDelegado) {
            $this->warn('No hay tablas legacy `delegacion` ni `delegado`: se omiten delegaciones, pivotes y NUE→delegación.');

            return [];
        }

        $urByClave = [];
        if ($hasDelegacion) {
            foreach (DB::connection($conn)->table('delegacion')->select('delegacion', 'ur')->distinct()->get() as $row) {
                $clave = trim((string) $row->delegacion);
                if ($clave !== '' && $row->ur !== null) {
                    $urByClave[$clave] = (int) $row->ur;
                }
            }
        }

        $claves = collect();
        if ($hasDelegacion) {
            foreach (DB::connection($conn)->table('delegacion')->select('delegacion')->distinct()->get() as $row) {
                $claves->push(trim((string) $row->delegacion));
            }
        }
        if ($hasDelegado) {
            foreach (DB::connection($conn)->table('delegado')->select('delegacion')->distinct()->get() as $row) {
                $claves->push(trim((string) $row->delegacion));
            }
        }
        $claves = $claves->filter()->unique()->values();
        $ids = [];
        foreach ($claves as $clave) {
            if ($clave === '') {
                continue;
            }
            $ur = $urByClave[$clave] ?? 0;
            $id = DB::connection($conn)->table('delegaciones')->insertGetId([
                'clave' => $clave,
                'nombre' => null,
                'ur' => $ur,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $ids[$clave] = $id;
        }

        return $ids;
    }

    private function importDependenciaDelegacion(string $conn, array $depIds, array $delIds): void
    {
        if (! Schema::connection($conn)->hasTable('delegacion') || $delIds === []) {
            return;
        }
        $nombreMap = $depIds['nombre'];
        foreach (DB::connection($conn)->table('delegacion')->get() as $row) {
            $depNombre = strtolower(trim((string) $row->dependencia));
            $clave = trim((string) $row->delegacion);
            if (! isset($nombreMap[$depNombre], $delIds[$clave])) {
                continue;
            }
            DB::connection($conn)->table('dependencia_delegacion')->insertOrIgnore([
                'dependencia_id' => $nombreMap[$depNombre],
                'delegacion_id' => $delIds[$clave],
            ]);
        }
    }

    private function importDelegados(string $conn, $now, array $delIds): void
    {
        if (! Schema::connection($conn)->hasTable('delegado') || $delIds === []) {
            return;
        }
        foreach (DB::connection($conn)->table('delegado')->get() as $row) {
            $nombre = trim((string) $row->nombre);
            $clave = trim((string) $row->delegacion);
            if ($nombre === '' || ! isset($delIds[$clave])) {
                continue;
            }
            $delegadoId = DB::connection($conn)->table('delegados')->insertGetId([
                'nombre_completo' => $nombre,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::connection($conn)->table('delegado_delegacion')->insert([
                'delegado_id' => $delegadoId,
                'delegacion_id' => $delIds[$clave],
            ]);
        }
    }

    /**
     * Catálogo partidas: no_partida proviene de concentrado ∪ propuesta.
     * Descripción de partida: primero propuesta (campo descripcion por número de partida); si falta, partida_descripcion en concentrado.
     * La clave presupuestal de la partida (clave_partida) no va aquí: va en partidas_por_ejercicio y en cada partida_especifica.
     */
    private function importPartidas(string $conn, $now): array
    {
        $nums = collect();
        foreach (DB::connection($conn)->table('concentrado')->whereNotNull('no_partida')->select('no_partida')->distinct()->get() as $row) {
            $nums->push((int) $row->no_partida);
        }
        foreach (DB::connection($conn)->table('propuesta')->select('partida')->distinct()->get() as $row) {
            $nums->push((int) $row->partida);
        }
        $nums = $nums->unique()->filter()->values();

        $descProp = [];
        foreach (DB::connection($conn)->table('propuesta')->whereNotNull('descripcion')->orderBy('id')->get(['partida', 'descripcion']) as $r) {
            $no = (int) $r->partida;
            $t = trim((string) $r->descripcion);
            if ($t === '') {
                continue;
            }
            $len = strlen($t);
            if (! isset($descProp[$no]) || $len > $descProp[$no]['len']) {
                $descProp[$no] = ['len' => $len, 'text' => $t];
            }
        }

        $descConc = [];
        DB::connection($conn)->table('concentrado')
            ->whereNotNull('no_partida')
            ->whereNotNull('partida_descripcion')
            ->orderBy('id')
            ->chunk(10000, function ($chunk) use (&$descConc) {
                foreach ($chunk as $r) {
                    $no = (int) $r->no_partida;
                    $t = trim((string) $r->partida_descripcion);
                    if ($t === '') {
                        continue;
                    }
                    $len = strlen($t);
                    if (! isset($descConc[$no]) || $len > $descConc[$no]['len']) {
                        $descConc[$no] = ['len' => $len, 'text' => $t];
                    }
                }
            });

        $map = [];
        foreach ($nums as $no) {
            $desc = $descProp[$no]['text'] ?? null;
            if (! $desc) {
                $desc = $descConc[$no]['text'] ?? null;
            }
            $id = DB::connection($conn)->table('partidas')->insertGetId([
                'no_partida' => $no,
                'descripcion' => $desc,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $map[$no] = $id;
        }

        return $map;
    }

    /**
     * Equivale a SELECT DISTINCT clave2025, descripcion FROM concentrado (sin filas con clave2025 vacía).
     * Cada par único es una partida específica; partida_id es la primera no_partida válida vista para ese par
     * (la tabla normalizada exige FK a partidas).
     *
     * @return array<string, int> clave interna DISTINCT + "|{$anio}" => id
     */
    private function importPartidasEspecificas(string $conn, $now, array $partidaIds, int $anio): array
    {
        if (! Schema::connection($conn)->hasTable('partidas_especificas')) {
            return [];
        }

        $agg = [];
        $peChunk = 0;
        DB::connection($conn)->table('concentrado')
            ->whereNotNull('clave2025')
            ->where('clave2025', '!=', '')
            ->orderBy('id')
            ->chunk(5000, function ($chunk) use (&$agg, &$peChunk, $partidaIds) {
                $peChunk++;
                if ($peChunk === 1 || $peChunk % 10 === 0) {
                    $this->line('    … partidas específicas: DISTINCT clave2025+descripcion, bloque '.$peChunk);
                }
                foreach ($chunk as $r) {
                    $clave = trim((string) $r->clave2025);
                    if ($clave === '') {
                        continue;
                    }
                    $k = $this->partidaEspecificaDistinctKey($clave, $r->descripcion);
                    if (! isset($agg[$k])) {
                        $agg[$k] = [
                            'clave' => $clave,
                            'descripcion' => $r->descripcion,
                            'no_partida' => null,
                            'clave_partida' => null,
                        ];
                    }
                    if ($agg[$k]['no_partida'] === null && $r->no_partida !== null) {
                        $no = (int) $r->no_partida;
                        if (isset($partidaIds[$no])) {
                            $agg[$k]['no_partida'] = $no;
                        }
                    }
                    if ($agg[$k]['clave_partida'] === null && $r->clave_partida !== null) {
                        $cp = trim((string) $r->clave_partida);
                        if ($cp !== '') {
                            $agg[$k]['clave_partida'] = $cp;
                        }
                    }
                }
            });

        $map = [];
        $skipped = 0;
        foreach ($agg as $k => $meta) {
            if ($meta['no_partida'] === null) {
                $skipped++;

                continue;
            }
            $partidaId = $partidaIds[$meta['no_partida']];
            $id = (int) DB::connection($conn)->table('partidas_especificas')->insertGetId([
                'partida_id' => $partidaId,
                'anio' => $anio,
                'clave' => SpanishQuestionMarkArtifacts::fix($meta['clave']),
                'descripcion' => SpanishQuestionMarkArtifacts::fixNullable(
                    $meta['descripcion'] === null ? null : (string) $meta['descripcion']
                ),
                'clave_partida' => SpanishQuestionMarkArtifacts::fixNullable($meta['clave_partida']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $map[$k.'|'.$anio] = $id;
        }
        if ($skipped > 0) {
            $this->warn("    Partidas específicas: se omitieron {$skipped} pares DISTINCT sin no_partida resoluble.");
        }

        return $map;
    }

    /**
     * Clave estable para el par (clave ejercicio, descripcion) como en DISTINCT SQL.
     */
    private function partidaEspecificaDistinctKey(string $claveTrimmed, $descripcion): string
    {
        return json_encode([$claveTrimmed, $descripcion], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function importPartidasPorEjercicio(string $conn, $now, array $partidaIds, int $anio): void
    {
        $metaPorNo = [];
        DB::connection($conn)->table('concentrado')
            ->whereNotNull('no_partida')
            ->orderBy('id')
            ->chunk(10000, function ($chunk) use (&$metaPorNo) {
                foreach ($chunk as $r) {
                    $no = (int) $r->no_partida;
                    if (! isset($metaPorNo[$no])) {
                        $metaPorNo[$no] = ['clave_partida' => null, 'clave_presupuestal' => null];
                    }
                    if ($metaPorNo[$no]['clave_partida'] === null && $r->clave_partida !== null) {
                        $cp = trim((string) $r->clave_partida);
                        if ($cp !== '') {
                            $metaPorNo[$no]['clave_partida'] = $r->clave_partida;
                        }
                    }
                    if ($metaPorNo[$no]['clave_presupuestal'] === null && $r->clave_presupuestal !== null) {
                        $metaPorNo[$no]['clave_presupuestal'] = (int) $r->clave_presupuestal;
                    }
                }
            });

        foreach ($partidaIds as $no => $partidaId) {
            $m = $metaPorNo[$no] ?? ['clave_partida' => null, 'clave_presupuestal' => null];
            DB::connection($conn)->table('partidas_por_ejercicio')->insert([
                'partida_id' => $partidaId,
                'anio' => $anio,
                'no_partida_snapshot' => $no,
                'clave_como_se_uso' => $m['clave_partida'],
                'clave_para_ejercicio' => null,
                'clave_presupuestal' => $m['clave_presupuestal'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function importProductosYPrecios(string $conn, $now, array $partidaIds, array $tiposByCodigo, int $anio): array
    {
        $map = [];
        foreach (DB::connection($conn)->table('propuesta')->orderBy('id')->get() as $p) {
            $no = (int) $p->partida;
            $tipoCod = (int) $p->partida_especifica;
            if (! isset($partidaIds[$no], $tiposByCodigo[$tipoCod])) {
                continue;
            }
            $productoId = DB::connection($conn)->table('productos')->insertGetId([
                'partida_id' => $partidaIds[$no],
                'tipo_partida_especifica_id' => $tiposByCodigo[$tipoCod],
                'lote' => (int) $p->lote,
                'descripcion' => $p->descripcion,
                'marca' => $p->marca,
                'unidad_medida' => $p->unidad,
                'codigo' => $p->codigo,
                'medida' => $p->medida,
                'legacy_propuesta_id' => $p->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $map[$p->id] = $productoId;
            DB::connection($conn)->table('producto_precios')->insert([
                'producto_id' => $productoId,
                'anio' => $anio,
                'precio_unitario' => $p->precio_unitario,
                'subtotal' => $p->subtotal,
                'proveedor' => $p->proveedor,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $map;
    }

    private function importEmpleados(string $conn, $now, array $depIds, array $delIds): array
    {
        $nombreMap = $depIds['nombre'];
        $urMap = $depIds['ur'];
        $keys = [];
        foreach (DB::connection($conn)->table('concentrado')->select('nue', 'nombre_trab', 'apellp_trab', 'apellm_trab', 'ur', 'dependencia')->distinct()->get() as $row) {
            $k = $this->empleadoKey($row);
            $keys[$k] = $row;
        }
        $delegacionByNue = [];
        if (Schema::connection($conn)->hasTable('delegacion')) {
            foreach (DB::connection($conn)->table('delegacion')->get() as $d) {
                $delegacionByNue[trim((string) $d->nue)] = trim((string) $d->delegacion);
            }
        }
        $empleadoIds = [];
        foreach ($keys as $k => $row) {
            $depId = null;
            if ($row->dependencia) {
                $depId = $nombreMap[strtolower(trim((string) $row->dependencia))] ?? null;
            }
            if ($depId === null && $row->ur !== null) {
                $depId = $urMap[(int) $row->ur] ?? null;
            }
            $delId = null;
            $nueTrim = trim((string) $row->nue);
            if ($nueTrim !== '' && isset($delegacionByNue[$nueTrim], $delIds[$delegacionByNue[$nueTrim]])) {
                $delId = $delIds[$delegacionByNue[$nueTrim]];
            }
            $id = DB::connection($conn)->table('empleados')->insertGetId([
                'nue' => $nueTrim !== '' ? $nueTrim : null,
                'nombre' => $row->nombre_trab,
                'apellido_paterno' => $row->apellp_trab,
                'apellido_materno' => $row->apellm_trab,
                'dependencia_id' => $depId,
                'delegacion_id' => $delId,
                'ur' => $row->ur !== null ? (int) $row->ur : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $empleadoIds[$k] = $id;
        }

        return $empleadoIds;
    }

    private function empleadoKey(object $row): string
    {
        return implode('|', [
            strtolower(trim((string) $row->nue)),
            strtolower(trim((string) $row->nombre_trab)),
            strtolower(trim((string) $row->apellp_trab)),
            strtolower(trim((string) $row->apellm_trab)),
            (string) ($row->ur ?? ''),
            strtolower(trim((string) $row->dependencia)),
        ]);
    }

    private function importSolicitudes(string $conn, $now, array $empleadoIds, array $productoIdsByLegacyPropuesta, int $anio, array $partidaEspecMap = []): void
    {
        $db = DB::connection($conn);
        $propuestaByMatch = [];
        foreach ($db->table('propuesta')->get() as $p) {
            $mk = (int) $p->partida.'
'.strtolower(trim((string) $p->descripcion)).'
'.(string) $p->precio_unitario;
            if (! isset($propuestaByMatch[$mk])) {
                $propuestaByMatch[$mk] = $p->id;
            }
        }
        $tiposByCodigo = $db->table('tipos_partida_especifica')->pluck('id', 'codigo')->all();
        $partidaNoToId = $db->table('partidas')->pluck('id', 'no_partida')->all();
        $defaultTipoId = $tiposByCodigo[244] ?? reset($tiposByCodigo);

        $empleadoDepMap = [];
        foreach ($db->table('empleados')->select('id', 'dependencia_id')->get() as $e) {
            $empleadoDepMap[(int) $e->id] = $e->dependencia_id;
        }

        $productoMeta = [];
        foreach ($db->table('productos')->select('id', 'partida_id', 'tipo_partida_especifica_id')->get() as $p) {
            $productoMeta[(int) $p->id] = [
                'partida_id' => $p->partida_id,
                'tipo_pe_id' => $p->tipo_partida_especifica_id,
            ];
        }

        $orphanProductoCache = [];
        $batch = [];
        $chunkNum = 0;

        $db->table('concentrado')->orderBy('id')->chunk(2000, function ($rows) use (
            &$batch,
            &$orphanProductoCache,
            &$productoMeta,
            &$chunkNum,
            $conn,
            $now,
            $empleadoIds,
            $empleadoDepMap,
            $productoIdsByLegacyPropuesta,
            $anio,
            $propuestaByMatch,
            $partidaNoToId,
            $defaultTipoId,
            $partidaEspecMap
        ) {
            $chunkNum++;
            if ($chunkNum === 1 || $chunkNum % 5 === 0) {
                $this->line('  … concentrado: bloque '.$chunkNum.' (~'.number_format($chunkNum * 2000).' filas leídas)');
            }
            foreach ($rows as $c) {
                $rowKey = $this->empleadoKey($c);
                if (! isset($empleadoIds[$rowKey])) {
                    continue;
                }
                $empleadoId = $empleadoIds[$rowKey];
                $propuestaId = null;
                if ($c->no_partida !== null && $c->descripcion !== null) {
                    $mk = (int) $c->no_partida.'
'.strtolower(trim((string) $c->descripcion)).'
'.(string) $c->precio_unitario;
                    $propuestaId = $propuestaByMatch[$mk] ?? null;
                }
                $productoId = $propuestaId !== null ? ($productoIdsByLegacyPropuesta[$propuestaId] ?? null) : null;
                if ($productoId === null && $c->no_partida !== null) {
                    $no = (int) $c->no_partida;
                    $partidaId = $partidaNoToId[$no] ?? null;
                    if ($partidaId && $defaultTipoId) {
                        $cacheKey = $no.'|'.md5(strtolower(trim((string) ($c->descripcion ?? '')))).'|'.(string) ($c->precio_unitario ?? '');
                        if (isset($orphanProductoCache[$cacheKey])) {
                            $productoId = $orphanProductoCache[$cacheKey];
                        } else {
                            $productoId = DB::connection($conn)->table('productos')->insertGetId([
                                'partida_id' => $partidaId,
                                'tipo_partida_especifica_id' => $defaultTipoId,
                                'lote' => null,
                                'descripcion' => $c->descripcion ?? 'Sin descripción',
                                'marca' => null,
                                'unidad_medida' => 'PZA',
                                'codigo' => null,
                                'medida' => null,
                                'origen' => 'concentrado',
                                'legacy_propuesta_id' => null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                            $productoMeta[$productoId] = [
                                'partida_id' => $partidaId,
                                'tipo_pe_id' => $defaultTipoId,
                            ];
                            DB::connection($conn)->table('producto_precios')->insert([
                                'producto_id' => $productoId,
                                'anio' => $anio,
                                'precio_unitario' => $c->precio_unitario ?? 0,
                                'subtotal' => null,
                                'proveedor' => null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                            $orphanProductoCache[$cacheKey] = $productoId;
                        }
                    }
                }
                if ($productoId === null) {
                    continue;
                }
                $partidaEspId = null;
                if ($c->clave2025 !== null && trim((string) $c->clave2025) !== '') {
                    $mkPe = $this->partidaEspecificaDistinctKey(trim((string) $c->clave2025), $c->descripcion).'|'.$anio;
                    $partidaEspId = $partidaEspecMap[$mkPe] ?? null;
                }

                $pm = $productoMeta[$productoId] ?? null;
                $batch[] = [
                    'empleado_id' => $empleadoId,
                    'producto_id' => $productoId,
                    'producto_original_id' => null,
                    'partida_especifica_id' => $partidaEspId,
                    'dependencia_id' => $empleadoDepMap[$empleadoId] ?? null,
                    'partida_id' => $pm['partida_id'] ?? null,
                    'tipo_partida_especifica_id' => $pm['tipo_pe_id'] ?? null,
                    'anio' => $anio,
                    'talla' => $c->talla,
                    'cantidad' => $c->cantidad ?? 1,
                    'precio_unitario' => $c->precio_unitario,
                    'importe' => $c->importe,
                    'iva' => $c->iva,
                    'importe_total' => $c->total,
                    'es_sustitucion' => false,
                    'estado' => 'aprobado',
                    'no_partida_snapshot' => $c->no_partida,
                    'clave_partida_snapshot' => $c->clave_partida,
                    'clave_para_ejercicio_snapshot' => $c->clave2025,
                    'legacy_concentrado_id' => $c->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($batch) >= 400) {
                    DB::connection($conn)->table('solicitudes_vestuario')->insert($batch);
                    $batch = [];
                }
            }
        });

        if ($batch !== []) {
            DB::connection($conn)->table('solicitudes_vestuario')->insert($batch);
        }
    }
}
