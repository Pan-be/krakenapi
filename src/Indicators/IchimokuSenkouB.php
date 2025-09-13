<?php

namespace Indicators;

class IchimokuSenkouB
{
    public static function calculate(array $candles, int $period = 52): array
    {
        $result = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < $period - 1) {
                $result[] = $candles[$i] + ['ichimoku_senkou_b' => null];
                continue;
            }

            $slice = array_slice($candles, $i - $period + 1, $period);
            $highs = array_column($slice, 'high');
            $lows = array_column($slice, 'low');

            $highMax = max($highs);
            $lowMin = min($lows);

            $senkouBValue = ($highMax + $lowMin) / 2;

            $indexShifted = $i + 26;

            if ($indexShifted < count($candles)) {
                $candles[$indexShifted]['ichimoku_senkou_b'] = round($senkouBValue, 6);
            }

            $result[] = $candles[$i];
        }

        return $result;
    }
}