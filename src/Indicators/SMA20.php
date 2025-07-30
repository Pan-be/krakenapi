<?php

namespace Indicators;

class SMA20
{
    public static function calculate(array $candles): array
    {
        $smaCandles = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < 19) {
                $smaCandles[] = $candles[$i] + ['sma20' => null];
                continue;
            }

            $sum = 0;
            for ($j = $i - 19; $j <= $i; $j++) {
                $sum += $candles[$j]['close'];
            }

            $sma = $sum / 20;
            $smaCandles[] = $candles[$i] + ['sma20' => round($sma, 6)];
        }

        return $smaCandles;
    }
}
