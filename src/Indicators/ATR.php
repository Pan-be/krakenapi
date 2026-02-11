<?php

namespace Indicators;

class ATR
{
    public static function calculate(array $candles, int $period = 14): array
    {
        $result = [];
        $trueRanges = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i === 0) {
                // Brak poprzedniej świecy
                $trueRanges[] = null;
                $result[] = $candles[$i] + ['atr' => null];
                continue;
            }

            $high = $candles[$i]['high'];
            $low = $candles[$i]['low'];
            $prevClose = $candles[$i - 1]['close'];

            $tr = max(
                $high - $low,
                abs($high - $prevClose),
                abs($low - $prevClose)
            );

            $trueRanges[] = $tr;

            // Za mało danych na ATR
            if ($i < $period) {
                $result[] = $candles[$i] + ['atr' => null];
                continue;
            }

            // Pierwszy ATR = SMA(TR)
            if ($i === $period) {
                $sum = 0;
                for ($j = 1; $j <= $period; $j++) {
                    $sum += $trueRanges[$j];
                }

                $atr = $sum / $period;
                $result[] = $candles[$i] + ['atr' => round($atr, 6)];
                continue;
            }

            // Kolejne ATR (Wilder smoothing)
            $prevAtr = $result[$i - 1]['atr'];
            $atr = (($prevAtr * ($period - 1)) + $tr) / $period;

            $result[] = $candles[$i] + ['atr' => round($atr, 6)];
        }

        return $result;
    }
}
