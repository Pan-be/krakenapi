<?php

namespace Decision;

/**
 * 1h confirmation (Excel 1h sheet, cols K/L) plus the entry-timing rule
 * (cols Z/AA/AB/AR) that matches a 4h signal to its confirming 1h candle,
 * checking the matched candle first and the next one after that.
 */
class OneHourConfirmation
{
    private const ADX_THRESHOLD = 20;

    /** Adds 'long_conf' / 'short_conf' flags to every 1h candle. */
    public static function annotate(array $hourlyCandles): array
    {
        $n = count($hourlyCandles);

        for ($i = 0; $i < $n; $i++) {
            $prev = $hourlyCandles[$i - 1] ?? null;

            $hourlyCandles[$i]['long_conf'] = self::longConfirmed($hourlyCandles[$i], $prev);
            $hourlyCandles[$i]['short_conf'] = self::shortConfirmed($hourlyCandles[$i], $prev);
        }

        return $hourlyCandles;
    }

    private static function longConfirmed(array $c, ?array $prev): bool
    {
        [$hist, $macd, $signal, $adx, $prevHist] = self::macdInputs($c, $prev);

        if ($hist === null || $prevHist === null) {
            return false;
        }

        return $hist > 0 && $hist > $prevHist && $macd > $signal && $adx > self::ADX_THRESHOLD;
    }

    private static function shortConfirmed(array $c, ?array $prev): bool
    {
        [$hist, $macd, $signal, $adx, $prevHist] = self::macdInputs($c, $prev);

        if ($hist === null || $prevHist === null) {
            return false;
        }

        return $hist < 0 && $hist < $prevHist && $macd < $signal && $adx > self::ADX_THRESHOLD;
    }

    private static function macdInputs(array $c, ?array $prev): array
    {
        return [
            $c['macd_histogram'] ?? null,
            $c['macd'] ?? null,
            $c['macd_signal'] ?? null,
            $c['adx'] ?? null,
            $prev['macd_histogram'] ?? null,
        ];
    }

    /**
     * Given a 4h candle's timestamp and its (4h-side) signal direction, find the
     * confirming 1h candle: the matched candle itself (col Z), else the one right
     * after it. Returns null if neither confirms — no trade, even if the 4h side
     * was fully green.
     *
     * @return array{index:int, sameCandle:bool}|null
     */
    public static function findConfirmation(array $hourlyCandles, int $fourHourTimestamp, int $direction): ?array
    {
        if ($direction === 0) {
            return null;
        }

        $matchedIndex = self::matchIndex($hourlyCandles, $fourHourTimestamp);

        if ($matchedIndex === null) {
            return null;
        }

        foreach ([0, 1] as $offset) {
            $index = $matchedIndex + $offset;
            $candle = $hourlyCandles[$index] ?? null;

            if ($candle === null) {
                continue;
            }

            $confirmed = $direction === 1 ? $candle['long_conf'] : $candle['short_conf'];

            if ($confirmed) {
                return ['index' => $index, 'sameCandle' => $offset === 0];
            }
        }

        return null;
    }

    /** Excel's Z: MATCH(4h_timestamp + 3h, 1h!timestamps) — the 4h candle's last hour. */
    private static function matchIndex(array $hourlyCandles, int $fourHourTimestamp): ?int
    {
        $targetTimestamp = $fourHourTimestamp + 3 * 3600;

        foreach ($hourlyCandles as $i => $c) {
            if (($c['timestamp'] ?? null) === $targetTimestamp) {
                return $i;
            }
        }

        return null;
    }
}
