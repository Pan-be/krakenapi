<?php

namespace Indicators;

class RSI
{
    public static function calculate(array $candles, int $period = 14): array
    {
        $rsiCandles = [];
        $gains = $losses = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i === 0) {
                $rsiCandles[] = $candles[$i] + ['rsi' => null];
                continue;
            }

            $change = $candles[$i]['close'] - $candles[$i - 1]['close'];
            $gains[] = max($change, 0);
            $losses[] = max(-$change, 0);

            if ($i < $period) {
                $rsiCandles[] = $candles[$i] + ['rsi' => null];
                continue;
            }

            $avgGain = array_sum(array_slice($gains, -$period)) / $period;
            $avgLoss = array_sum(array_slice($losses, -$period)) / $period;

            if ($avgLoss == 0) {
                $rsi = 100;
            } else {
                $rs = $avgGain / $avgLoss;
                $rsi = 100 - (100 / (1 + $rs));
            }

            $rsiCandles[] = $candles[$i] + ['rsi' => round($rsi, 2)];
        }

        return $rsiCandles;
    }
}
