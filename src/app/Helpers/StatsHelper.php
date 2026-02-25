<?php

declare(strict_types=1);

namespace App\Helpers;

class StatsHelper
{
    /**
     * Calcula la media (promedio).
     */
    public static function mean(array $values): float
    {
        return count($values) > 0 ? array_sum($values) / count($values) : 0;
    }

    /**
     * Calcula la moda (valor más común).
     * Si hay empate devuelve el primero en orden descendente de frecuencia.
     */
    public static function mode(array $values): float|int|null
    {
        if (empty($values)) {
            return null;
        }

        // Redondear a enteros para agrupar valores decimales cercanos
        $rounded = array_map(fn($v) => (int) round((float) $v), $values);
        $frequencies = array_count_values($rounded);
        arsort($frequencies);

        return array_key_first($frequencies);
    }

    /**
     * Calcula la desviación estándar poblacional.
     */
    public static function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = self::mean($values);
        $variance = array_sum(array_map(
            fn($x) => pow($x - $mean, 2),
            $values
        )) / $count;

        return sqrt($variance);
    }

    /**
     * Calcula la varianza poblacional.
     */
    public static function variance(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = self::mean($values);

        return array_sum(array_map(
            fn($x) => pow($x - $mean, 2),
            $values
        )) / $count;
    }

    /**
     * Retorna el rango típico como string legible: "68.5-82.3"
     * (media ± 1 desviación estándar).
     */
    public static function typicalRange(array $values): string
    {
        $mean = self::mean($values);
        $std  = self::standardDeviation($values);

        $lower = round($mean - $std, 1);
        $upper = round($mean + $std, 1);

        return "{$lower}-{$upper}";
    }

    /**
     * Retorna solo el valor de la desviación en formato "±X.X".
     */
    public static function typicalRangeDelta(array $values): string
    {
        return '±' . round(self::standardDeviation($values), 1);
    }

    /**
     * Determina el nivel de consistencia basado en el coeficiente de variación.
     *
     * CV < 10 % → "alta"
     * CV < 20 % → "media"
     * CV ≥ 20 % → "baja"
     */
    public static function consistencyLevel(array $values): string
    {
        $std  = self::standardDeviation($values);
        $mean = self::mean($values);

        if ($mean == 0) {
            return 'baja';
        }

        $cv = ($std / $mean) * 100;

        if ($cv < 10) {
            return 'alta';
        }
        if ($cv < 20) {
            return 'media';
        }

        return 'baja';
    }

    /**
     * Traduce el nivel de consistencia a una interpretación legible.
     */
    public static function consistencyInterpretation(string $level, string $subject = 'tus resultados'): string
    {
        return match ($level) {
            'alta'  => "Tu nivel de consistencia es alto: {$subject} son muy estables.",
            'media' => "Tu nivel de consistencia es moderado: hay cierta variabilidad en {$subject}.",
            default => "Hay alta variabilidad en {$subject}. Con más práctica tus puntajes se estabilizarán.",
        };
    }

    /**
     * Calcula el rango percentil de un puntaje dentro de un conjunto de valores.
     * Retorna un entero 0-100 que representa qué porcentaje de valores están por debajo.
     *
     * percentileRank([60,70,80,90], 75) → 50 (TOP 50%)
     */
    public static function percentileRank(array $values, float $score): int
    {
        $count = count($values);
        if ($count === 0) {
            return 0;
        }

        $below = count(array_filter($values, fn($v) => $v < $score));

        return (int) round(($below / $count) * 100);
    }

    /**
     * Describe la posición percentil en términos legibles como "TOP 40%".
     */
    public static function topPercent(array $values, float $score): string
    {
        $percentile = self::percentileRank($values, $score);
        $top        = 100 - $percentile;

        return 'TOP ' . max(1, $top) . '%';
    }

    /**
     * Detecta la tendencia de una serie de valores usando regresión lineal simple.
     *
     * slope > +1  → "positiva"
     * slope < -1  → "negativa"
     * else        → "estable"
     */
    public static function detectTrend(array $values): string
    {
        if (count($values) < 3) {
            return 'estable';
        }

        $n    = count($values);
        $x    = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($values);

        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $values[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $denominator = ($n * $sumX2 - $sumX * $sumX);
        if ($denominator == 0) {
            return 'estable';
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;

        if ($slope > 1) {
            return 'positiva';
        }
        if ($slope < -1) {
            return 'negativa';
        }

        return 'estable';
    }

    /**
     * Calcula la pendiente de la regresión lineal (puntos por intento).
     */
    public static function trendSlope(array $values): float
    {
        $n = count($values);
        if ($n < 2) {
            return 0.0;
        }

        $x    = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($values);

        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $values[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $denominator = ($n * $sumX2 - $sumX * $sumX);
        if ($denominator == 0) {
            return 0.0;
        }

        return round(($n * $sumXY - $sumX * $sumY) / $denominator, 2);
    }

    /**
     * Determina la dificultad de un paso según su tasa de éxito promedio.
     *
     * avg >= 80 → "baja"
     * avg >= 60 → "media"
     * avg <  60 → "alta"
     */
    public static function difficultyLevel(float $averageScore): string
    {
        if ($averageScore >= 80) {
            return 'baja';
        }
        if ($averageScore >= 60) {
            return 'media';
        }

        return 'alta';
    }

    /**
     * Determina la cantidad de estrellas (1-5) según el puntaje promedio.
     */
    public static function stars(float $averageScore): int
    {
        return match (true) {
            $averageScore >= 90 => 5,
            $averageScore >= 80 => 4,
            $averageScore >= 70 => 3,
            $averageScore >= 55 => 2,
            default             => 1,
        };
    }

    /**
     * Construye el badge de un aprendiz según su promedio.
     *
     * >= 85 → "oro"
     * >= 75 → "plata"
     * >= 60 → "bronce"
     * else  → "ninguno"
     */
    public static function badge(float $average): string
    {
        return match (true) {
            $average >= 85 => 'oro',
            $average >= 75 => 'plata',
            $average >= 60 => 'bronce',
            default        => 'ninguno',
        };
    }
}
