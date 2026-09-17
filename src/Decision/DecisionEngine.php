<?php

namespace Decision;

/**
 * Ties the whole decision pipeline together: mode -> direction -> filters ->
 * gate -> 1h confirmation (+ the T/T+1h reversal rule) -> SL/TP -> exit engine
 * -> early-exit overlay. Mirrors the Excel model end to end (see the
 * "Kraken Trading Bot" vault note for the full mechanics spec this was ported
 * from).
 *
 * FEE ASSUMPTION (unconfirmed): the Excel's final $ column subtracts a 0.001
 * or 0.0007 fee depending on exit type, but which type gets which rate was
 * never pinned down against the sheet. Assumed here: TP exits (likely a
 * resting/maker order) use 0.0007, everything else (SL/TIME/EARLY, likely
 * market/taker orders) uses 0.001. Confirm against the Excel before trusting
 * absolute $/％ P&L figures — it does not affect mode/direction/gate/entry
 * logic above.
 */
class DecisionEngine
{
    private const FEE_TP = 0.0007;
    private const FEE_OTHER = 0.001;

    /**
     * @param array $fourHourCandles 4h candles already run through
     *                               Indicators\IndicatorCalculator::applyAll()
     * @param array $hourlyCandles  1h candles already run through
     *                               Indicators\IndicatorCalculator::applyAll()
     */
    public static function evaluate(array $fourHourCandles, array $hourlyCandles): array
    {
        $fourHourCandles = ModeSelector::calculate($fourHourCandles);
        $fourHourCandles = DirectionSelector::calculate($fourHourCandles);
        $fourHourCandles = SignalFilters::calculate($fourHourCandles);
        $fourHourCandles = GateEvaluator::calculate($fourHourCandles);

        $hourlyCandles = OneHourConfirmation::annotate($hourlyCandles);

        foreach ($fourHourCandles as $i => $candle) {
            $fourHourCandles[$i]['trade'] = self::evaluateOne($candle, $hourlyCandles);
        }

        return $fourHourCandles;
    }

    private static function evaluateOne(array $candle, array $hourlyCandles): ?array
    {
        if (($candle['gate'] ?? 0) !== 1) {
            return null;
        }

        $timestamp = $candle['timestamp'] ?? null;
        $atr = $candle['atr'] ?? null;

        if ($timestamp === null || $atr === null) {
            return null;
        }

        $confirmation = OneHourConfirmation::findConfirmation($hourlyCandles, $timestamp, $candle['direction']);

        if ($confirmation === null) {
            return null; // no 1h confirmation in either candle -> no trade, even if the 4h side was green
        }

        $tradedDirection = $confirmation['sameCandle'] ? $candle['direction'] : -$candle['direction'];
        $entryIndex = $confirmation['index'];
        $entryPrice = $hourlyCandles[$entryIndex]['close'] ?? null;

        if ($entryPrice === null) {
            return null;
        }

        $mode = $candle['mode'];
        $slDistance = TradeLevels::slDistance($mode, $atr);
        $tpDistance = TradeLevels::tpDistance($mode, $atr);

        $overlay = EarlyExitOverlay::evaluate(
            $hourlyCandles,
            $entryIndex,
            $entryPrice,
            $tradedDirection,
            $slDistance,
            $tpDistance
        );

        $exit = $overlay['fired']
            ? ['exit_type' => $overlay['exit_type'], 'exit_price' => $overlay['exit_price']]
            : ExitEngine::run(
                $hourlyCandles,
                $entryIndex,
                $entryPrice,
                $tradedDirection,
                $slDistance,
                $tpDistance,
                $confirmation['sameCandle']
            );

        $pnlPercent = self::pnlPercent($tradedDirection, $entryPrice, $exit['exit_price'], $exit['exit_type']);

        return [
            'mode' => $mode,
            'signal_direction' => $candle['direction'],
            'traded_direction' => $tradedDirection,
            'same_candle' => $confirmation['sameCandle'],
            'entry_index' => $entryIndex,
            'entry_price' => round($entryPrice, 6),
            'sl_distance' => round($slDistance, 6),
            'tp_distance' => round($tpDistance, 6),
            'exit_type' => $exit['exit_type'],
            'exit_price' => round($exit['exit_price'], 6),
            'pnl_percent' => round($pnlPercent, 4),
        ];
    }

    private static function pnlPercent(int $direction, float $entryPrice, float $exitPrice, string $exitType): float
    {
        $move = $direction === 1
            ? ($exitPrice - $entryPrice) / $entryPrice
            : ($entryPrice - $exitPrice) / $entryPrice;

        $fee = $exitType === 'TP' ? self::FEE_TP : self::FEE_OTHER;

        return ($move - $fee) * 100;
    }
}
