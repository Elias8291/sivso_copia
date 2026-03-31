<?php

namespace App\Support;

/**
 * Corrige texto donde un charset incorrecto dejó "?" en lugar de tildes o ñ.
 */
final class SpanishQuestionMarkArtifacts
{
    /**
     * @return array<string, string> patrón regex => reemplazo
     */
    private static function pairs(): array
    {
        return [
            '/PANTAL\?NES/ui' => 'PANTALONES',
            '/PANTAL\?N/ui' => 'PANTALÓN',
            '/COMPOSICI\?N/ui' => 'COMPOSICIÓN',
            '/ALGOD\?N/ui' => 'ALGODÓN',
            '/POLI\?STERES/ui' => 'POLIÉSTERES',
            '/POLI\?STER/ui' => 'POLIÉSTER',
            '/SINT\?TICAS/ui' => 'SINTÉTICAS',
            '/SINT\?TICOS/ui' => 'SINTÉTICOS',
            '/SINT\?TICO/ui' => 'SINTÉTICO',
            '/SINT\?TICA/ui' => 'SINTÉTICA',
            '/CINTUR\?NES/ui' => 'CINTURONES',
            '/CINTUR\?N/ui' => 'CINTURÓN',
            '/QU\?MICAS/ui' => 'QUÍMICAS',
            '/QU\?MICA/ui' => 'QUÍMICA',
            '/QU\?MICOS/ui' => 'QUÍMICOS',
            '/QU\?MICO/ui' => 'QUÍMICO',
            '/EL\?STICAS/ui' => 'ELÁSTICAS',
            '/EL\?STICA/ui' => 'ELÁSTICA',
            '/EL\?STICOS/ui' => 'ELÁSTICOS',
            '/EL\?STICO/ui' => 'ELÁSTICO',
            '/EXPLORACI\?N/ui' => 'EXPLORACIÓN',
            '/C\?MODO/ui' => 'CÓMODO',
            '/ABRASI\?N/ui' => 'ABRASIÓN',
            '/PU\?OS/ui' => 'PUÑOS',
            '/PU\?O/ui' => 'PUÑO',
            '/L\?MPARA/ui' => 'LÁMPARA',
            '/CORD\?N/ui' => 'CORDÓN',
            '/LIM\?N/ui' => 'LIMÓN',
            '/ATR\?S/ui' => 'ATRÁS',
            '/BOT\?NES/ui' => 'BOTONES',
            '/BOT\?N/ui' => 'BOTÓN',
            '/SEG\?N/ui' => 'SEGÚN',
            '/TAMBI\?N/ui' => 'TAMBIÉN',
            '/ADEM\?S/ui' => 'ADEMÁS',
            '/M\?QUINA/ui' => 'MÁQUINA',
            '/M\?QUINAS/ui' => 'MÁQUINAS',
            '/M\?XIMO/ui' => 'MÁXIMO',
            '/M\?XIMA/ui' => 'MÁXIMA',
            '/M\?NIMO/ui' => 'MÍNIMO',
            '/M\?NIMA/ui' => 'MÍNIMA',
            '/N\?MERO/ui' => 'NÚMERO',
            '/N\?MEROS/ui' => 'NÚMEROS',
            '/PATR\?N/ui' => 'PATRÓN',
            '/PATR\?NES/ui' => 'PATRONES',
            '/CAPITUL\?O/ui' => 'CAPÍTULO',
            '/TITUL\?O/ui' => 'TÍTULO',
            '/JAB\?N/ui' => 'JABÓN',
            '/JAB\?NES/ui' => 'JABONES',
            '/CRIST\?L/ui' => 'CRISTAL',
            '/CRIST\?LES/ui' => 'CRISTALES',
            '/ESPA\?OL/ui' => 'ESPAÑOL',
            '/ESPA\?OLA/ui' => 'ESPAÑOLA',
            '/ESPA\?OLES/ui' => 'ESPAÑOLES',
            '/ESPA\?OLAS/ui' => 'ESPAÑOLAS',
            '/ESPA\?A/ui' => 'ESPAÑA',
            '/COMPA\?IAS/ui' => 'COMPAÑÍAS',
            '/COMPA\?IA/ui' => 'COMPAÑÍA',
            '/NI\?EZ/ui' => 'NIÑEZ',
            '/A\?OS/ui' => 'AÑOS',
            '/A\?O/ui' => 'AÑO',
            '/DA\?OS/ui' => 'DAÑOS',
            '/DA\?O/ui' => 'DAÑO',
            '/BA\?OS/ui' => 'BAÑOS',
            '/BA\?O/ui' => 'BAÑO',
            '/PE\?AS/ui' => 'PEÑAS',
            '/PE\?A/ui' => 'PEÑA',
            '/SU\?OS/ui' => 'SUEÑOS',
            '/SU\?O/ui' => 'SUEÑO',
            '/MA\?ANA/ui' => 'MAÑANA',
            '/MA\?ANAS/ui' => 'MAÑANAS',
            '/ENSE\?ANZA/ui' => 'ENSEÑANZA',
            '/ENSE\?ANZAS/ui' => 'ENSEÑANZAS',
            '/MU\?ECAS/ui' => 'MUÑECAS',
            '/MU\?ECA/ui' => 'MUÑECA',
            '/RAZ\?N/ui' => 'RAZÓN',
            '/RAZ\?NES/ui' => 'RAZONES',
            '/MEZCLILL\?/ui' => 'MEZCLILLA',
            '/CAMIS\?N/ui' => 'CAMISÓN',
            '/CAMIS\?/ui' => 'CAMISA',
            '/CHAQUET\?N/ui' => 'CHAQUETÓN',
            '/FUNCI\?N/ui' => 'FUNCIÓN',
            '/DISE\?O/ui' => 'DISEÑO',
            '/DISE\?OS/ui' => 'DISEÑOS',
            '/NI\?OS/ui' => 'NIÑOS',
            '/NI\?AS/ui' => 'NIÑAS',
            '/NI\?O/ui' => 'NIÑO',
            '/NI\?A/ui' => 'NIÑA',
            '/MO\?O/ui' => 'MOÑO',
            '/PE\?UELA/ui' => 'PEÑUELA',
            '/INGL\?S/ui' => 'INGLÉS',
            '/MET\?LICO/ui' => 'METÁLICO',
            '/MET\?LICA/ui' => 'METÁLICA',
            '/MET\?LICOS/ui' => 'METÁLICOS',
            '/MET\?LICAS/ui' => 'METÁLICAS',
            '/MEC\?NICO/ui' => 'MECÁNICO',
            '/MEC\?NICA/ui' => 'MECÁNICA',
            '/T\?CNICO/ui' => 'TÉCNICO',
            '/T\?CNICA/ui' => 'TÉCNICA',
            '/EL\?CTRICO/ui' => 'ELÉCTRICO',
            '/EL\?CTRICA/ui' => 'ELÉCTRICA',
            '/ESPECIFICACI\?N/ui' => 'ESPECIFICACIÓN',
            '/DESCRIPCI\?N/ui' => 'DESCRIPCIÓN',
            '/INSTALACI\?N/ui' => 'INSTALACIÓN',
            '/APLICACI\?N/ui' => 'APLICACIÓN',
            '/CALIFICACI\?N/ui' => 'CALIFICACIÓN',
            '/CERTIFICACI\?N/ui' => 'CERTIFICACIÓN',
            '/MODIFICACI\?N/ui' => 'MODIFICACIÓN',
            '/IDENTIFICACI\?N/ui' => 'IDENTIFICACIÓN',
            '/VERIFICACI\?N/ui' => 'VERIFICACIÓN',
            '/CLASIFICACI\?N/ui' => 'CLASIFICACIÓN',
            '/PLANIFICACI\?N/ui' => 'PLANIFICACIÓN',
            '/CONSTRUCCI\?N/ui' => 'CONSTRUCCIÓN',
            '/PRODUCCI\?N/ui' => 'PRODUCCIÓN',
            '/REPARACI\?N/ui' => 'REPARACIÓN',
            '/OPERACI\?N/ui' => 'OPERACIÓN',
            '/ADMINISTRACI\?N/ui' => 'ADMINISTRACIÓN',
            '/ORGANIZACI\?N/ui' => 'ORGANIZACIÓN',
            '/COMUNICACI\?N/ui' => 'COMUNICACIÓN',
            '/EVALUACI\?N/ui' => 'EVALUACIÓN',
            '/CAPACITACI\?N/ui' => 'CAPACITACIÓN',
            '/NOTIFICACI\?N/ui' => 'NOTIFICACIÓN',
            '/DISTRIBUCI\?N/ui' => 'DISTRIBUCIÓN',
            '/REQUISICI\?N/ui' => 'REQUISICIÓN',
            '/SUBDIVISI\?N/ui' => 'SUBDIVISIÓN',
            '/ATENCI\?N/ui' => 'ATENCIÓN',
            '/PREPARACI\?N/ui' => 'PREPARACIÓN',
            '/AUTORIZACI\?N/ui' => 'AUTORIZACIÓN',
            '/DECLARACI\?N/ui' => 'DECLARACIÓN',
            '/OBLIGACI\?N/ui' => 'OBLIGACIÓN',
            '/GESTI\?N/ui' => 'GESTIÓN',
            '/DIVISI\?N/ui' => 'DIVISIÓN',
            '/VERSI\?N/ui' => 'VERSIÓN',
            '/DIMENSI\?N/ui' => 'DIMENSIÓN',
            '/EXTENSI\?N/ui' => 'EXTENSIÓN',
            '/RETENCI\?N/ui' => 'RETENCIÓN',
            '/CONVERSI\?N/ui' => 'CONVERSIÓN',
            '/MEDICI\?N/ui' => 'MEDICIÓN',
            '/PRECISI\?N/ui' => 'PRECISIÓN',
            '/DECISI\?N/ui' => 'DECISIÓN',
            '/SUJECI\?N/ui' => 'SUJECIÓN',
            '/TENSI\?N/ui' => 'TENSIÓN',
            '/ELEVACI\?N/ui' => 'ELEVACIÓN',
            '/PRESENTACI\?N/ui' => 'PRESENTACIÓN',
            '/ORIENTACI\?N/ui' => 'ORIENTACIÓN',
            '/INFORMACI\?N/ui' => 'INFORMACIÓN',
            '/FORMACI\?N/ui' => 'FORMACIÓN',
            '/TRANSFORMACI\?N/ui' => 'TRANSFORMACIÓN',
            '/CONFORMACI\?N/ui' => 'CONFORMACIÓN',
            '/SUSPENSI\?N/ui' => 'SUSPENSIÓN',
            '/COMPRENSI\?N/ui' => 'COMPRENSIÓN',
            '/ADQUISICI\?N/ui' => 'ADQUISICIÓN',
            '/DISPOSICI\?N/ui' => 'DISPOSICIÓN',
            '/SUPERVISI\?N/ui' => 'SUPERVISIÓN',
            '/PROTECCI\?N/ui' => 'PROTECCIÓN',
            '/SELECCI\?N/ui' => 'SELECCIÓN',
            '/INSPECCI\?N/ui' => 'INSPECCIÓN',
            '/CORRECCI\?N/ui' => 'CORRECCIÓN',
            '/DIRECCI\?N/ui' => 'DIRECCIÓN',
            '/REDUCCI\?N/ui' => 'REDUCCIÓN',
            '/INTRODUCCI\?N/ui' => 'INTRODUCCIÓN',
            '/CONEXI\?N/ui' => 'CONEXIÓN',
            '/RELACI\?N/ui' => 'RELACIÓN',
            '/SOLUCI\?N/ui' => 'SOLUCIÓN',
            '/EVOLUCI\?N/ui' => 'EVOLUCIÓN',
            '/REVOLUCI\?N/ui' => 'REVOLUCIÓN',
            '/CONCLUSI\?N/ui' => 'CONCLUSIÓN',
            '/EXCLUSI\?N/ui' => 'EXCLUSIÓN',
            '/INCLUSI\?N/ui' => 'INCLUSIÓN',
            '/ILUSI\?N/ui' => 'ILUSIÓN',
            '/DIFUSI\?N/ui' => 'DIFUSIÓN',
            '/CONFUSI\?N/ui' => 'CONFUSIÓN',
            '/PRESI\?N/ui' => 'PRESIÓN',
            '/IMPRESI\?N/ui' => 'IMPRESIÓN',
            '/EXPRESI\?N/ui' => 'EXPRESIÓN',
            '/COMPRESI\?N/ui' => 'COMPRESIÓN',
            '/DEPRESI\?N/ui' => 'DEPRESIÓN',
            '/PROFESI\?N/ui' => 'PROFESIÓN',
            '/PROHIBICI\?N/ui' => 'PROHIBICIÓN',
            '/SUSTITUCI\?N/ui' => 'SUSTITUCIÓN',
            '/INSTITUCI\?N/ui' => 'INSTITUCIÓN',
            '/CONSTITUCI\?N/ui' => 'CONSTITUCIÓN',
            '/DESTRUCCI\?N/ui' => 'DESTRUCCIÓN',
            '/INSTRUCCI\?N/ui' => 'INSTRUCCIÓN',
            '/MANTENCI\?N/ui' => 'MANTENCIÓN',
            '/CONTENCI\?N/ui' => 'CONTENCIÓN',
            '/DETENCI\?N/ui' => 'DETENCIÓN',
            '/SITUACI\?N/ui' => 'SITUACIÓN',
            '/ACTUACI\?N/ui' => 'ACTUACIÓN',
            '/CONTINUACI\?N/ui' => 'CONTINUACIÓN',
            '/DISCONTINUACI\?N/ui' => 'DISCONTINUACIÓN',
            '/SUBSTITUCI\?N/ui' => 'SUBSTITUCIÓN',
            '/RESTITUCI\?N/ui' => 'RESTITUCIÓN',
        ];
    }

    /**
     * Sustituciones exactas desde config/sivso_encoding_overrides.php (prioridad a claves más largas).
     */
    private static function applyExactOverrides(string $text): string
    {
        /** @var array<string, string> $map */
        $map = config('sivso_encoding_overrides', []);
        if ($map === [] || ! is_array($map)) {
            return $text;
        }
        uksort($map, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
        $out = $text;
        foreach ($map as $bad => $good) {
            if ($bad === '' || ! str_contains($out, $bad)) {
                continue;
            }
            $out = str_replace($bad, (string) $good, $out);
        }

        return $out;
    }

    private static function applyPairs(string $text): string
    {
        $out = $text;
        foreach (self::pairs() as $pattern => $replacement) {
            $replaced = preg_replace($pattern, $replacement, $out);
            if ($replaced !== null) {
                $out = $replaced;
            }
        }

        return $out;
    }

    /**
     * Aplica patrones sobre fragmentos que son “palabras” con ? (por si el texto largo impidió un match global).
     */
    private static function fixWordChunksWithQuestionMark(string $text): string
    {
        if (! str_contains($text, '?')) {
            return $text;
        }

        $result = preg_replace_callback(
            '/(?<![\p{L}])([\p{L}]{1,60}(?:\?[\p{L}]{1,60})+)(?![\p{L}])/u',
            static function (array $m): string {
                $chunk = $m[1];
                for ($j = 0; $j < 12 && str_contains($chunk, '?'); $j++) {
                    $before = $chunk;
                    $chunk = self::applyPairs($chunk);
                    if ($chunk === $before) {
                        break;
                    }
                }

                return $chunk;
            },
            $text
        );

        return $result === null ? $text : $result;
    }

    /**
     * Corrige en varias pasadas: overrides de config, regex conocidos y reintento por palabra.
     */
    public static function fix(string $text): string
    {
        if (! str_contains($text, '?')) {
            return $text;
        }
        $out = $text;
        for ($i = 0; $i < 12 && str_contains($out, '?'); $i++) {
            $before = $out;
            $out = self::applyExactOverrides($out);
            $out = self::applyPairs($out);
            $out = self::fixWordChunksWithQuestionMark($out);
            if ($out === $before) {
                break;
            }
        }

        return $out;
    }

    public static function fixNullable(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        return self::fix($text);
    }
}
