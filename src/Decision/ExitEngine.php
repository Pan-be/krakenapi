<?php

namespace Decision;

/**
 * The TDTP exit engine (VBA Module2.bas), walking forward hour-by-hour on 1h
 * candles from the entry point. Two variants: NORMAL (checks up to 3h, used
 * when confirmation landed on the same 4h-aligned candle) and CONTRARIAN
 * (checks up to 4h, used when confirmation only arrived one hour later — the
 * concrete mechanism behind the "max 4h" rule). SL/TP take priority as SL if
 * both hit in the same candle (conservative — no intrabar order known). The
 * take-profit distance decays each hour held: 100% / 70% / 45% / 30% of the
 * original distance at hours 0/1/2/3.
 */
class ExitEngine
{
    private const TP_DECAY = [1.0, 0.7, 0.45, 0.30];
    private const NORMAL_MAX_HOURS = 3;
    private const CONTRARIAN_MAX_HOURS = 4;

    /**
     * @param bool $sameCandle true for the NORMAL variant (AB="T"), false for
     *                         the CONTRARIAN/INV4 variant (AB="T+1h")
     * @return array{exit_type:string, exit_price:float}
     */
    public static function run(
        array $hourlyCandles,
        int $entryIndex,
        float $entryPrice,
        int $direction,
        float $slDistance,
        float $tpDistance,
        bool $sameCandle
    ): array {
        $maxHours = $sameCandle ? self::NORMAL_MAX_HOURS : self::CONTRARIAN_MAX_HOURS;
        $slPrice = $direction === 1 ? $entryPrice - $slDistance : $entryPrice + $slDistance;

        $lastChecked = null;

        for ($hour = 1; $hour <= $maxHours; $hour++) {
            $candle = $hourlyCandles[$entryIndex + $hour] ?? null;

            if ($candle === null) {
                break; // ran out of candle history before the time limit
            }

            $lastChecked = $candle;
            $decay = self::TP_DECAY[$hour - 1];
            $tpPrice = $direction === 1
                ? $entryPrice + $tpDistance * $decay
                : $entryPrice - $tpDistance * $decay;

            $hitSl = $direction === 1 ? $candle['low'] <= $slPrice : $candle['high'] >= $slPrice;
            $hitTp = $direction === 1 ? $candle['high'] >= $tpPrice : $candle['low'] <= $tpPrice;

            if ($hitSl) {
                return ['exit_type' => 'SL', 'exit_price' => $slPrice];
            }

            if ($hitTp) {
                return ['exit_type' => 'TP', 'exit_price' => $tpPrice];
            }
        }

        // No SL/TP hit within the time limit -> forced TIME exit at the final checked candle's close.
        $finalCandle = $lastChecked ?? $hourlyCandles[$entryIndex];

        return ['exit_type' => 'TIME', 'exit_price' => $finalCandle['close']];
    }
}
