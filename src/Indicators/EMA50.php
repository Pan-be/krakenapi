<?php

namespace Indicators;

class EMA50
{
    public static function calculate(array $candles): array
    {
        $emaCandles = [];
        $period = 50;
        $multiplier = 2 / ($period + 1);
        $emaPrev = null;

        for ($i = 0; $i < count($candles); $i++) {
            $close = $candles[$i]['close'];

            if ($i < $period - 1) {
                $emaCandles[] = $candles[$i] + ['ema50' => null];
                continue;
            }

            if ($i === $period - 1) {
                $sum = 0;
                for ($j = 0; $j < $period; $j++) {
                    $sum += $candles[$j]['close'];
                }
                $emaPrev = $sum / $period;
            } else {
                $emaPrev = ($close - $emaPrev) * $multiplier + $emaPrev;
            }

            $emaCandles[] = $candles[$i] + ['ema50' => round($emaPrev, 6)];
        }

        return $emaCandles;
    }
}
