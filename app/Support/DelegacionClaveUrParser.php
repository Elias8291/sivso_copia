<?php

namespace App\Support;

/**
 * Extrae el número de UR típico al final de la clave de delegación (p. ej. 3A101 → 101, DR301 → 301, DR202A → 202).
 */
final class DelegacionClaveUrParser
{
    public static function urFromClave(string $clave): ?int
    {
        $clave = trim($clave);
        if ($clave === '') {
            return null;
        }
        if (preg_match('/(\d{2,4})([A-Za-z])?$/', $clave, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
