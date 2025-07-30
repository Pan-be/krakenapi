<?php

namespace Indicators;

class SMA50
{
    public static function calculate(array $candles): array
    {
        $smaCandles = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < 49) {
                $smaCandles[] = $candles[$i] + ['sma50' => null];
                continue;
            }

            $sum = 0;
            for ($j = $i - 49; $j <= $i; $j++) {
                $sum += $candles[$j]['close'];
            }

            $sma = $sum / 50;
            $smaCandles[] = $candles[$i] + ['sma50' => round($sma, 6)];
        }

        return $smaCandles;
    }
}
