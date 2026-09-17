<?php

namespace Decision;

/**
 * SL/TP distance, by mode (Excel: SuperTrend regime vs. Ichimoku regime).
 * ATR is the 4h ATR at the signal candle.
 */
class TradeLevels
{
    public static function slDistance(string $mode, float $atr): float
    {
        return ($mode === 'ST' ? 1.2 : 1.5) * $atr;
    }

    public static function tpDistance(string $mode, float $atr): float
    {
        return ($mode === 'ST' ? 1.8 : 3.0) * $atr;
    }
}
