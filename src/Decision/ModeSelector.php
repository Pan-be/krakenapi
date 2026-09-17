<?php

namespace Decision;

/**
 * Mode selection (Excel 4h sheet, col U) — which trend engine is active.
 */
class ModeSelector
{
    private const ADX_OFF_THRESHOLD = 35;
    private const ADX_ICHI_THRESHOLD = 25;

    public static function calculate(array $candles): array
    {
        foreach ($candles as $i => $candle) {
            $candles[$i]['mode'] = self::modeFor($candle);
        }

        return $candles;
    }

    private static function modeFor(array $c): string
    {
        $adx = $c['adx'] ?? null;
        $atrPercent = $c['atr_percent'] ?? null;
        $sma20AtrPercent = $c['sma20_atr_percent'] ?? null;

        if ($adx === null || $atrPercent === null || $sma20AtrPercent === null) {
            return 'OFF';
        }

        if ($adx <= self::ADX_OFF_THRESHOLD) {
            return 'OFF';
        }

        if ($atrPercent > $sma20AtrPercent) {
            return 'ST';
        }

        if ($adx >= self::ADX_ICHI_THRESHOLD) {
            return 'ICHI';
        }

        return 'OFF';
    }
}
