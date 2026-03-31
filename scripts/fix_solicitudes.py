# -*- coding: utf-8 -*-
import re
path = r"c:\Users\Elias\Documents\sistema_sivso_2026\app\Console\Commands\MigrateCopiasivsoFromLegacyCommand.php"
with open(path, "r", encoding="utf-8") as f:
    s = f.read()
new = r"""    private function importSolicitudes(string $conn, $now, array $empleadoIds, array $productoIdsByLegacyPropuesta, int $anio): void
    {
        $db = DB::connection($conn);
        $propuestaByMatch = [];
        foreach ($db->table('propuesta')->get() as $p) {
            $mk = (int) $p->partida."\n".strtolower(trim((string) $p->descripcion))."\n".(string) $p->precio_unitario;
            if (! isset($propuestaByMatch[$mk])) {
                $propuestaByMatch[$mk] = $p->id;
            }
        }
        $tiposByCodigo = $db->table('tipos_partida_especifica')->pluck('id', 'codigo')->all();
        $partidaNoToId = $db->table('partidas')->pluck('id', 'no_partida')->all();
        $defaultTipoId = $tiposByCodigo[244] ?? reset($tiposByCodigo);

        $orphanProductoCache = [];
        $batch = [];

        $db->table('concentrado')->orderBy('id')->chunk(2000, function ($rows) use (
            &$batch,
            &$orphanProductoCache,
            $conn,
            $now,
            $empleadoIds,
            $productoIdsByLegacyPropuesta,
            $anio,
            $propuestaByMatch,
            $partidaNoToId,
            $defaultTipoId
        ) {
            foreach ($rows as $c) {
                $rowKey = $this->empleadoKey($c);
                if (! isset($empleadoIds[$rowKey])) {
                    continue;
                }
                $empleadoId = $empleadoIds[$rowKey];
                $propuestaId = null;
                if ($c->no_partida !== null && $c->descripcion !== null) {
                    $mk = (int) $c->no_partida."\n".strtolower(trim((string) $c->descripcion))."\n".(string) $c->precio_unitario;
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
                                'legacy_propuesta_id' => null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
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
                $batch[] = [
                    'empleado_id' => $empleadoId,
                    'producto_id' => $productoId,
                    'anio' => $anio,
                    'talla' => $c->talla,
                    'cantidad' => $c->cantidad ?? 1,
                    'precio_unitario' => $c->precio_unitario,
                    'importe' => $c->importe,
                    'iva' => $c->iva,
                    'importe_total' => $c->total,
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
}"""
pattern = r"    private function importSolicitudes\(string \$conn.*?\n\}"
s2, n = re.subn(pattern, new.rstrip("}") , s, count=1, flags=re.DOTALL)
if n != 1:
    raise SystemExit("replace failed n=%s" % n)
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(s2)
print("ok")
