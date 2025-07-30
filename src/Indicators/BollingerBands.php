<?php

namespace Indicators;

class BollingerBands
{
    public static function calculate(array $candles, int $period = 20, float $multiplier = 2.0): array
    {
        $result = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < $period - 1) {
                $result[] = $candles[$i] + [
                    'bb_middle' => null,
                    'bb_upper' => null,
                    'bb_lower' => null,
                ];
                continue;
            }

            $slice = array_slice($candles, $i - $period + 1, $period);
            $closes = array_column($slice, 'close');

            $avg = array_sum($closes) / $period;

            // obliczanie odchylenia standardowego
            $variance = 0;
            foreach ($closes as $close) {
                $variance += pow($close - $avg, 2);
            }
            $stdDev = sqrt($variance / $period);

            $upper = $avg + $multiplier * $stdDev;
            $lower = $avg - $multiplier * $stdDev;

            $result[] = $candles[$i] + [
                'bb_middle' => round($avg, 6),
                'bb_upper' => round($upper, 6),
                'bb_lower' => round($lower, 6),
            ];
        }

        return $result;
    }
}
