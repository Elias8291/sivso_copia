<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mapeo NUE+UR / persona+UR desde la tabla legacy «delegacion» hacia claves de «delegaciones».
 * Usado en migración legacy y en reconciliación tras import CSV.
 */
final class LegacyEmpleadosDelegacionMapper
{
    /**
     * Clave de persona en catálogo: nombre + apellidos + UR + dependencia (sin NUE).
     */
    public static function empleadoKey(object $row): string
    {
        return implode('|', [
            strtolower(trim((string) ($row->nombre_trab ?? ''))),
            strtolower(trim((string) ($row->apellp_trab ?? ''))),
            strtolower(trim((string) ($row->apellm_trab ?? ''))),
            (string) ($row->ur ?? ''),
            strtolower(trim((string) ($row->dependencia ?? ''))),
        ]);
    }

    /**
     * @return array{
     *   by_persona_ur: array<string, string>,
     *   by_nue_ur: array<string, string>,
     *   by_nue: array<string, string>
     * }
     */
    public static function buildDelegacionClaveLookup(string $connection, array $delIds): array
    {
        $byPersonaUr = [];
        $byNueUr = [];
        $byNue = [];

        if (! Schema::connection($connection)->hasTable('delegacion')) {
            return ['by_persona_ur' => $byPersonaUr, 'by_nue_ur' => $byNueUr, 'by_nue' => $byNue];
        }

        $cols = Schema::connection($connection)->getColumnListing('delegacion');
        $colSet = array_flip($cols);
        $hasNombreTrab = isset($colSet['nombre_trab']);
        $hasApellp = isset($colSet['apellp_trab']);
        $hasApellm = isset($colSet['apellm_trab']);

        foreach (DB::connection($connection)->table('delegacion')->get() as $d) {
            $clave = trim((string) $d->delegacion);
            if ($clave === '' || ! isset($delIds[$clave])) {
                continue;
            }

            $urD = isset($d->ur) && $d->ur !== null && $d->ur !== '' ? (int) $d->ur : null;
            $nueT = isset($d->nue) ? trim((string) $d->nue) : '';

            if ($hasNombreTrab || $hasApellp || $hasApellm) {
                $fake = (object) [
                    'nombre_trab' => $hasNombreTrab ? ($d->nombre_trab ?? '') : '',
                    'apellp_trab' => $hasApellp ? ($d->apellp_trab ?? '') : '',
                    'apellm_trab' => $hasApellm ? ($d->apellm_trab ?? '') : '',
                    'ur' => $urD,
                    'dependencia' => $d->dependencia ?? '',
                ];
                $pk = self::empleadoKey($fake);
                if ($pk !== '||||') {
                    $byPersonaUr[$pk] = $clave;
                }
            }

            if ($nueT !== '') {
                if ($urD !== null) {
                    $byNueUr[$nueT.'|'.$urD] = $clave;
                } else {
                    $byNue[$nueT] = $clave;
                }
            }
        }

        return [
            'by_persona_ur' => $byPersonaUr,
            'by_nue_ur' => $byNueUr,
            'by_nue' => $byNue,
        ];
    }

    /**
     * @param  array{by_persona_ur: array<string, string>, by_nue_ur: array<string, string>, by_nue: array<string, string>}  $lookup
     */
    public static function resolveDelegacionIdForEmpleadoRow(object $row, array $lookup, array $delIds): ?int
    {
        $personaKey = self::empleadoKey($row);
        if ($personaKey !== '||||' && isset($lookup['by_persona_ur'][$personaKey])) {
            $cl = $lookup['by_persona_ur'][$personaKey];

            return $delIds[$cl] ?? null;
        }

        $nueTrim = trim((string) ($row->nue ?? ''));
        if ($nueTrim === '') {
            return null;
        }

        if ($row->ur !== null && $row->ur !== '') {
            $k = $nueTrim.'|'.(int) $row->ur;
            if (isset($lookup['by_nue_ur'][$k])) {
                $cl = $lookup['by_nue_ur'][$k];

                return $delIds[$cl] ?? null;
            }
        }

        if (isset($lookup['by_nue'][$nueTrim])) {
            $cl = $lookup['by_nue'][$nueTrim];

            return $delIds[$cl] ?? null;
        }

        return null;
    }

    /**
     * Fila normalizada empleados + nombre dependencia → delegacion_id.
     *
     * @param  array{by_persona_ur: array<string, string>, by_nue_ur: array<string, string>, by_nue: array<string, string>}  $lookup
     */
    public static function resolveDelegacionIdForNormalizedEmpleado(object $e, ?string $dependenciaNombre, array $lookup, array $delIds): ?int
    {
        $row = (object) [
            'nue' => $e->nue ?? null,
            'nombre_trab' => $e->nombre ?? '',
            'apellp_trab' => $e->apellido_paterno ?? '',
            'apellm_trab' => $e->apellido_materno ?? '',
            'ur' => $e->ur ?? null,
            'dependencia' => $dependenciaNombre ?? '',
        ];

        return self::resolveDelegacionIdForEmpleadoRow($row, $lookup, $delIds);
    }
}
