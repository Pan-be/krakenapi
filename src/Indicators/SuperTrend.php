<?php

namespace Indicators;

class SuperTrend
{
    public static function calculate(array $candles, int $period = 10, float $multiplier = 3.0): array
    {
        $supertrendCandles = [];
        $atr = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i === 0) {
                $atr[] = null;
                $supertrendCandles[] = $candles[$i] + ['supertrend' => null, 'supertrend_direction' => null];
                continue;
            }

            $high = $candles[$i]['high'];
            $low = $candles[$i]['low'];
            $closePrev = $candles[$i - 1]['close'];

            // True Range
            $tr = max($high - $low, abs($high - $closePrev), abs($low - $closePrev));

            // ATR (Wilder’s smoothing)
            if ($i < $period) {
                $atr[] = null;
                $supertrendCandles[] = $candles[$i] + ['supertrend' => null, 'supertrend_direction' => null];
                continue;
            } elseif ($i === $period) {
                $atr[] = array_sum(array_column(array_slice($candles, 1, $period), 'high')) / $period; // placeholder
            } else {
                $atr[] = (($atr[$i - 1] * ($period - 1)) + $tr) / $period;
            }

            $atrVal = $atr[$i];

            // Basic bands
            $mid = ($high + $low) / 2;
            $upperBand = $mid + $multiplier * $atrVal;
            $lowerBand = $mid - $multiplier * $atrVal;

            // Trend logic
            if ($i === $period) {
                $supertrend = $upperBand;
                $direction = 'down';
            } else {
                $prevST = $supertrendCandles[$i - 1]['supertrend'];
                $prevDir = $supertrendCandles[$i - 1]['supertrend_direction'];

                if ($candles[$i]['close'] > $upperBand) {
                    $supertrend = $lowerBand;
                    $direction = 'up';
                } elseif ($candles[$i]['close'] < $lowerBand) {
                    $supertrend = $upperBand;
                    $direction = 'down';
                } else {
                    $supertrend = ($prevDir === 'up') ? min($lowerBand, $prevST) : max($upperBand, $prevST);
                    $direction = $prevDir;
                }
            }

            $supertrendCandles[] = $candles[$i] + [
                'supertrend' => round($supertrend, 6),
                'supertrend_direction' => $direction,
            ];
        }

        return $supertrendCandles;
    }
}
