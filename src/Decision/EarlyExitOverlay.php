<?php

namespace Decision;

/**
 * Early-exit overlay (Excel cols AW-AZ), checked before the normal exit engine.
 * Looks only at the single 1h candle right after entry: if price already moved
 * favourably by >=2% of the TP distance, defer to the normal exit engine for the
 * real exit. Otherwise the trade is cut immediately at that candle's close.
 */
class EarlyExitOverlay
{
    private const FAVOURABLE_THRESHOLD_PCT = 0.02;

    /**
     * @return array{fired:bool, exit_type?:string, exit_price?:float}
     */
    public static function evaluate(
        array $hourlyCandles,
        int $entryIndex,
        float $entryPrice,
        int $direction,
        float $slDistance,
        float $tpDistance
    ): array {
        $candle = $hourlyCandles[$entryIndex + 1] ?? null;

        if ($candle === null) {
            // No candle to check yet — let the exit engine handle the missing history.
            return ['fired' => false];
        }

        $favourableMove = $direction === 1
            ? $candle['close'] - $entryPrice
            : $entryPrice - $candle['close'];

        $threshold = self::FAVOURABLE_THRESHOLD_PCT * $tpDistance;

        if ($favourableMove >= $threshold) {
            return ['fired' => false];
        }

        $slPrice = $direction === 1 ? $entryPrice - $slDistance : $entryPrice + $slDistance;
        $breachedStop = $direction === 1 ? $candle['low'] <= $slPrice : $candle['high'] >= $slPrice;

        return [
            'fired' => true,
            'exit_type' => $breachedStop ? 'SL' : 'EARLY',
            'exit_price' => $candle['close'],
        ];
    }
}
