<?php

namespace Indicators;

class MACD
{
    public static function calculate(array $candles): array
    {
        $macdCandles = [];
        $ema12 = self::calculateEMA($candles, 12, 'ema12');
        $ema26 = self::calculateEMA($ema12, 26, 'ema26');

        $macdLine = [];
        foreach ($ema26 as $i => $candle) {
            $ema12Val = $candle['ema12'] ?? null;
            $ema26Val = $candle['ema26'] ?? null;

            if ($ema12Val !== null && $ema26Val !== null) {
                $macd = $ema12Val - $ema26Val;
                $macdLine[] = $macd;
                $macdCandles[] = $candle + ['macd' => round($macd, 6)];
            } else {
                $macdLine[] = null;
                $macdCandles[] = $candle + ['macd' => null];
            }
        }

        // MACD Signal line (9-period EMA of MACD)
        $macdSignal = [];
        $signal = null;
        $multiplier = 2 / (9 + 1);
        for ($i = 0; $i < count($macdLine); $i++) {
            $macdValue = $macdLine[$i];
            if ($macdValue === null) {
                $macdSignal[] = null;
                $macdCandles[$i]['macd_signal'] = null;
                $macdCandles[$i]['macd_histogram'] = null;
                continue;
            }

            if ($signal === null && $i >= 33) { // 26 EMA + 9 EMA period = ~34 candles
                $sum = array_sum(array_slice($macdLine, $i - 8, 9));
                $signal = $sum / 9;
            } elseif ($signal !== null) {
                $signal = ($macdValue - $signal) * $multiplier + $signal;
            }

            $macdCandles[$i]['macd_signal'] = $signal !== null ? round($signal, 6) : null;
            $macdCandles[$i]['macd_histogram'] = ($signal !== null && $macdValue !== null)
                ? round($macdValue - $signal, 6)
                : null;
        }

        return $macdCandles;
    }

    private static function calculateEMA(array $candles, int $period, string $key): array
    {
        $ema = null;
        $multiplier = 2 / ($period + 1);

        foreach ($candles as $i => &$candle) {
            $close = $candle['close'];

            if ($i < $period - 1) {
                $candle[$key] = null;
                continue;
            }

            if ($i === $period - 1) {
                $sum = 0;
                for ($j = $i - $period + 1; $j <= $i; $j++) {
                    $sum += $candles[$j]['close'];
                }
                $ema = $sum / $period;
            } else {
                $ema = ($close - $ema) * $multiplier + $ema;
            }

            $candle[$key] = round($ema, 6);
        }

        return $candles;
    }
}
