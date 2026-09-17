<?php

namespace Decision;

/**
 * Two filters that must both pass (Excel 4h sheet, cols W and X).
 */
class SignalFilters
{
    private const VOLUME_FILTER_THRESHOLD = 1.4;
    private const MOMENTUM_BOOST = 1.1;

    public static function calculate(array $candles): array
    {
        $n = count($candles);

        for ($i = 0; $i < $n; $i++) {
            $prev = $candles[$i - 1] ?? null;

            $candles[$i]['ema_filter'] = self::emaFilter($candles[$i]);
            $candles[$i]['volume_filter'] = self::volumeFilter($candles[$i], $prev);
        }

        return $candles;
    }

    private static function emaFilter(array $c): int
    {
        $direction = $c['direction'] ?? 0;
        $ema50 = $c['ema50'] ?? null;
        $ema200 = $c['ema200'] ?? null;

        if ($direction === 0 || $ema50 === null || $ema200 === null) {
            return 0;
        }

        return match ($direction) {
            1 => $ema50 > $ema200 ? 1 : 0,
            -1 => $ema50 < $ema200 ? 1 : 0,
            default => 0,
        };
    }

    private static function volumeFilter(array $c, ?array $prev): int
    {
        $score = self::volumeScore($c, $prev);

        return ($score !== null && $score >= self::VOLUME_FILTER_THRESHOLD) ? 1 : 0;
    }

    /**
     * (ADX/35) * (Volume/SMA20Volume) * a 1.1 momentum boost if ADX and the
     * volume ratio are both rising vs. the prior candle.
     */
    public static function volumeScore(array $c, ?array $prev): ?float
    {
        $adx = $c['adx'] ?? null;
        $volume = $c['volume'] ?? null;
        $sma20Volume = $c['sma20_volume'] ?? null;

        if ($adx === null || $volume === null || !$sma20Volume) {
            return null;
        }

        $volumeRatio = $volume / $sma20Volume;
        $score = ($adx / 35) * $volumeRatio;

        if ($prev !== null && self::isRisingVsPrev($adx, $volumeRatio, $prev)) {
            $score *= self::MOMENTUM_BOOST;
        }

        return $score;
    }

    private static function isRisingVsPrev(float $adx, float $volumeRatio, array $prev): bool
    {
        $prevAdx = $prev['adx'] ?? null;
        $prevVolume = $prev['volume'] ?? null;
        $prevSma20Volume = $prev['sma20_volume'] ?? null;

        if ($prevAdx === null || $prevVolume === null || !$prevSma20Volume) {
            return false;
        }

        $prevVolumeRatio = $prevVolume / $prevSma20Volume;

        return $adx > $prevAdx && $volumeRatio > $prevVolumeRatio;
    }
}
