<?php

namespace Indicators;

class EMA20
{
    public static function calculate(array $candles): array
    {
        $emaCandles = [];
        $period = 20;
        $multiplier = 2 / ($period + 1);
        $emaPrev = null;

        for ($i = 0; $i < count($candles); $i++) {
            $close = $candles[$i]['close'];

            if ($i < $period - 1) {
                // Za mało danych do obliczenia EMA – dodaj null
                $emaCandles[] = $candles[$i] + ['ema20' => null];
                continue;
            }

            if ($i === $period - 1) {
                // Pierwsze EMA to SMA z pierwszych 20 świec
                $sum = 0;
                for ($j = 0; $j < $period; $j++) {
                    $sum += $candles[$j]['close'];
                }
                $emaPrev = $sum / $period;
            } else {
                // Obliczenie EMA na podstawie poprzedniej wartości
                $emaPrev = ($close - $emaPrev) * $multiplier + $emaPrev;
            }

            $emaCandles[] = $candles[$i] + ['ema20' => round($emaPrev, 6)];
        }

        return $emaCandles;
    }
}
