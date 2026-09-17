<?php

namespace Decision;

/**
 * Direction (Excel 4h sheet, col V) — signed direction from whichever engine is active.
 */
class DirectionSelector
{
    public static function calculate(array $candles): array
    {
        foreach ($candles as $i => $candle) {
            $candles[$i]['direction'] = self::directionFor($candle);
        }

        return $candles;
    }

    private static function directionFor(array $c): int
    {
        return match ($c['mode'] ?? 'OFF') {
            'ST' => self::superTrendDirection($c),
            'ICHI' => self::ichimokuDirection($c),
            default => 0,
        };
    }

    private static function superTrendDirection(array $c): int
    {
        return match ($c['supertrend_direction'] ?? null) {
            'up' => 1,
            'down' => -1,
            default => 0,
        };
    }

    private static function ichimokuDirection(array $c): int
    {
        $close = $c['close'] ?? null;
        $senkouA = $c['ichimoku_senkou_a'] ?? null;
        $senkouB = $c['ichimoku_senkou_b'] ?? null;
        $tenkan = $c['ichimoku_tenkan'] ?? null;
        $kijun = $c['ichimoku_kijun'] ?? null;

        if ($close === null || $senkouA === null || $senkouB === null || $tenkan === null || $kijun === null) {
            return 0;
        }

        if ($close > max($senkouA, $senkouB) && $tenkan > $kijun && $senkouA > $senkouB) {
            return 1;
        }

        if ($close < min($senkouA, $senkouB) && $tenkan < $kijun && $senkouA < $senkouB) {
            return -1;
        }

        return 0;
    }
}
