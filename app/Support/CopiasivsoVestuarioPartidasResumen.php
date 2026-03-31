<?php

namespace App\Support;

use Illuminate\Database\Connection;

/**
 * Agregados de solicitudes_vestuario por año: dependencia -> partida -> tipo partida específica.
 * Usa FKs denormalizadas en solicitudes_vestuario para GROUP BY sin JOINs extra.
 */
final class CopiasivsoVestuarioPartidasResumen
{
    /**
     * @return array{
     *     total_lineas: int,
     *     total_importe: float,
     *     dependencias_partidas: list<array{
     *         dependencia_id: int|null,
     *         nombre: string,
     *         total_lineas: int,
     *         total_importe: float,
     *         partidas: list<array{
     *             partida_id: int,
     *             no_partida: int,
     *             descripcion: string|null,
     *             total_lineas: int,
     *             total_importe: float,
     *             tipos: list<array{
     *                 tipo_partida_especifica_id: int|null,
     *                 codigo: int|string|null,
     *                 nombre: string|null,
     *                 lineas: int,
     *                 importe: float
     *             }>
     *         }>
     *     }>
     * }
     */
    public static function forAnio(Connection $cx, int $anio): array
    {
        $tot = $cx->table('solicitudes_vestuario as s')
            ->where('s.anio', $anio)
            ->selectRaw('COUNT(*) as lineas, COALESCE(SUM(s.importe_total), 0) as importe')
            ->first();

        $rows = $cx->table('solicitudes_vestuario as s')
            ->leftJoin('dependencias as d', 'd.id', '=', 's.dependencia_id')
            ->leftJoin('partidas as pa', 'pa.id', '=', 's.partida_id')
            ->leftJoin('tipos_partida_especifica as tpe', 'tpe.id', '=', 's.tipo_partida_especifica_id')
            ->where('s.anio', $anio)
            ->groupBy('s.dependencia_id', 's.partida_id', 's.tipo_partida_especifica_id')
            ->orderByRaw('COALESCE(MAX(d.nombre), \'\')')
            ->orderByRaw('MAX(pa.no_partida)')
            ->orderByRaw('MAX(tpe.codigo) IS NULL, MAX(tpe.codigo)')
            ->selectRaw(
                's.dependencia_id as dependencia_id, '.
                'COALESCE(MAX(d.nombre), \'Sin dependencia\') as dependencia_nombre, '.
                's.partida_id as partida_id, MAX(pa.no_partida) as no_partida, MAX(pa.descripcion) as partida_descripcion, '.
                's.tipo_partida_especifica_id as tipo_partida_especifica_id, MAX(tpe.codigo) as tipo_codigo, MAX(tpe.nombre) as tipo_nombre, '.
                'COUNT(*) as lineas, COALESCE(SUM(s.importe_total), 0) as importe'
            )
            ->get();

        $depBuckets = [];

        foreach ($rows as $row) {
            $dKey = $row->dependencia_id === null ? '_null' : (string) (int) $row->dependencia_id;
            $pKey = (int) $row->partida_id;

            if (! isset($depBuckets[$dKey])) {
                $depBuckets[$dKey] = [
                    'dependencia_id' => $row->dependencia_id !== null ? (int) $row->dependencia_id : null,
                    'nombre' => (string) $row->dependencia_nombre,
                    'partidas_map' => [],
                    'total_lineas' => 0,
                    'total_importe' => 0.0,
                ];
            }

            $ln = (int) $row->lineas;
            $im = (float) $row->importe;

            if (! isset($depBuckets[$dKey]['partidas_map'][$pKey])) {
                $depBuckets[$dKey]['partidas_map'][$pKey] = [
                    'partida_id' => $pKey,
                    'no_partida' => (int) $row->no_partida,
                    'descripcion' => $row->partida_descripcion !== null ? (string) $row->partida_descripcion : null,
                    'tipos' => [],
                    'total_lineas' => 0,
                    'total_importe' => 0.0,
                ];
            }

            $tipoCodigo = $row->tipo_codigo;
            if ($tipoCodigo !== null) {
                $tipoCodigo = is_numeric($tipoCodigo) ? (int) $tipoCodigo : (string) $tipoCodigo;
            }

            $depBuckets[$dKey]['partidas_map'][$pKey]['tipos'][] = [
                'tipo_partida_especifica_id' => $row->tipo_partida_especifica_id !== null ? (int) $row->tipo_partida_especifica_id : null,
                'codigo' => $tipoCodigo,
                'nombre' => $row->tipo_nombre !== null ? (string) $row->tipo_nombre : null,
                'lineas' => $ln,
                'importe' => $im,
            ];
            $depBuckets[$dKey]['partidas_map'][$pKey]['total_lineas'] += $ln;
            $depBuckets[$dKey]['partidas_map'][$pKey]['total_importe'] += $im;

            $depBuckets[$dKey]['total_lineas'] += $ln;
            $depBuckets[$dKey]['total_importe'] += $im;
        }

        $dependenciasPartidas = [];

        foreach ($depBuckets as $dep) {
            $partidas = collect($dep['partidas_map'])
                ->sortBy('no_partida')
                ->values()
                ->map(function (array $p) {
                    usort($p['tipos'], function (array $a, array $b) {
                        if ($a['codigo'] === null && $b['codigo'] === null) {
                            return 0;
                        }
                        if ($a['codigo'] === null) {
                            return 1;
                        }
                        if ($b['codigo'] === null) {
                            return -1;
                        }

                        return $a['codigo'] <=> $b['codigo'];
                    });

                    return [
                        'partida_id' => $p['partida_id'],
                        'no_partida' => $p['no_partida'],
                        'descripcion' => $p['descripcion'],
                        'total_lineas' => $p['total_lineas'],
                        'total_importe' => $p['total_importe'],
                        'tipos' => $p['tipos'],
                    ];
                })
                ->all();

            unset($dep['partidas_map']);
            $dep['partidas'] = $partidas;
            $dependenciasPartidas[] = $dep;
        }

        usort($dependenciasPartidas, function (array $a, array $b) {
            $an = $a['nombre'] === 'Sin dependencia' ? 'zzz' : $a['nombre'];
            $bn = $b['nombre'] === 'Sin dependencia' ? 'zzz' : $b['nombre'];

            return strcmp($an, $bn);
        });

        return [
            'total_lineas' => (int) ($tot->lineas ?? 0),
            'total_importe' => (float) ($tot->importe ?? 0),
            'dependencias_partidas' => $dependenciasPartidas,
        ];
    }
}
