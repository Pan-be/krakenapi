<?php

namespace Indicators;

class IchimokuKijun
{
    public static function calculate(array $candles, int $period = 26): array
    {
        $result = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < $period - 1) {
                $result[] = $candles[$i] + ['ichimoku_kijun' => null];
                continue;
            }

            $slice = array_slice($candles, $i - $period + 1, $period);
            $highs = array_column($slice, 'high');
            $lows = array_column($slice, 'low');

            $highMax = max($highs);
            $lowMin = min($lows);

            $kijun = ($highMax + $lowMin) / 2;

            $result[] = $candles[$i] + ['ichimoku_kijun' => round($kijun, 6)];
        }

        return $result;
    }
}
