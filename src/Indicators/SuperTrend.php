<?php

namespace Indicators;

class SuperTrend
{
    public static function calculate(array $candles, int $period = 10, float $multiplier = 3.0): array
    {
        $n = count($candles);
        if ($n < $period) {
            return $candles; // za mało świec
        }

        // True Range i ATR
        $trs = [];
        $atr = array_fill(0, $n, null);

        for ($i = 1; $i < $n; $i++) {
            $h = $candles[$i]['high'];
            $l = $candles[$i]['low'];
            $cp = $candles[$i - 1]['close'];

            $trs[$i] = max(
                $h - $l,
                abs($h - $cp),
                abs($l - $cp)
            );
        }

        // Pierwszy ATR = średnia z TR
        $atr[$period] = array_sum(array_slice($trs, 1, $period)) / $period;

        // ATR Wildera
        for ($i = $period + 1; $i < $n; $i++) {
            $atr[$i] = (($atr[$i - 1] * ($period - 1)) + $trs[$i]) / $period;
        }

        // Basic bands
        $basicUB = array_fill(0, $n, null);
        $basicLB = array_fill(0, $n, null);
        for ($i = 0; $i < $n; $i++) {
            if ($atr[$i] === null) continue;
            $mid = ($candles[$i]['high'] + $candles[$i]['low']) / 2;
            $basicUB[$i] = $mid + $multiplier * $atr[$i];
            $basicLB[$i] = $mid - $multiplier * $atr[$i];
        }

        // Final bands
        $finalUB = array_fill(0, $n, null);
        $finalLB = array_fill(0, $n, null);

        for ($i = $period; $i < $n; $i++) {
            if ($i == $period) {
                $finalUB[$i] = $basicUB[$i];
                $finalLB[$i] = $basicLB[$i];
                continue;
            }
            $closePrev = $candles[$i - 1]['close'];

            $finalUB[$i] = ($basicUB[$i] < $finalUB[$i - 1] || $closePrev > $finalUB[$i - 1])
                ? $basicUB[$i]
                : $finalUB[$i - 1];

            $finalLB[$i] = ($basicLB[$i] > $finalLB[$i - 1] || $closePrev < $finalLB[$i - 1])
                ? $basicLB[$i]
                : $finalLB[$i - 1];
        }

        // SuperTrend
        $supertrend = array_fill(0, $n, null);
        for ($i = $period; $i < $n; $i++) {
            if ($i == $period) {
                $supertrend[$i] = $finalUB[$i];
                continue;
            }

            $close = $candles[$i]['close'];

            if ($supertrend[$i - 1] == $finalUB[$i - 1] && $close <= $finalUB[$i]) {
                $supertrend[$i] = $finalUB[$i];
            } elseif ($supertrend[$i - 1] == $finalUB[$i - 1] && $close > $finalUB[$i]) {
                $supertrend[$i] = $finalLB[$i];
            } elseif ($supertrend[$i - 1] == $finalLB[$i - 1] && $close >= $finalLB[$i]) {
                $supertrend[$i] = $finalLB[$i];
            } elseif ($supertrend[$i - 1] == $finalLB[$i - 1] && $close < $finalLB[$i]) {
                $supertrend[$i] = $finalUB[$i];
            }
        }

        // Kierunek
        $direction = array_fill(0, $n, null);
        for ($i = $period; $i < $n; $i++) {
            if ($supertrend[$i] !== null) {
                $direction[$i] = ($candles[$i]['close'] < $supertrend[$i]) ? 'down' : 'up';
            }
        }

        // Łączenie wyników z oryginalnymi świecami
        $result = [];
        for ($i = 0; $i < $n; $i++) {
            $result[] = array_merge($candles[$i], [
                'atr' => $atr[$i] !== null ? round($atr[$i], 6) : null,
                'supertrend' => $supertrend[$i] !== null ? round($supertrend[$i], 6) : null,
                'supertrend_direction' => $direction[$i] ?? null,
            ]);
        }

        return $result;
    }
}
