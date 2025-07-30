<?php

namespace Indicators;

class SMA200
{
    public static function calculate(array $candles): array
    {
        $smaCandles = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < 199) {
                $smaCandles[] = $candles[$i] + ['sma200' => null];
                continue;
            }

            $sum = 0;
            for ($j = $i - 199; $j <= $i; $j++) {
                $sum += $candles[$j]['close'];
            }

            $sma = $sum / 200;
            $smaCandles[] = $candles[$i] + ['sma200' => round($sma, 6)];
        }

        return $smaCandles;
    }
}
