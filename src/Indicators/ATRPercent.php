<?php

namespace Indicators;

class ATRPercent
{
    /**
     * @param array $candles Tablica świec (każda świeca: open, high, low, close)
     * @param int $period Okres ATR (np. 14)
     * @return array Świece z dodanym polem atr_percent
     */
    public static function calculate(array $candles, int $period = 14): array
    {
        $atrValues = [];
        $result = [];

        for ($i = 0; $i < count($candles); $i++) {
            $high = $candles[$i]['high'];
            $low  = $candles[$i]['low'];
            $close = $candles[$i]['close'];
            $prevClose = $candles[$i - 1]['close'] ?? $close;

            // True Range
            $tr = max(
                $high - $low,
                abs($high - $prevClose),
                abs($low - $prevClose)
            );

            $atrValues[] = $tr;

            if ($i < $period) {
                // jeszcze za mało danych
                $atr = null;
            } elseif ($i == $period) {
                // pierwsze ATR to zwykła średnia TR z pierwszych N świec
                $atr = array_sum(array_slice($atrValues, 0, $period)) / $period;
            } else {
                // dalsze ATR obliczamy jak EMA: poprzedni ATR + (TR - poprzedni ATR) / period
                $prevAtr = $result[$i - 1]['atr'] ?? null;
                if ($prevAtr === null) {
                    $atr = array_sum(array_slice($atrValues, -$period)) / $period;
                } else {
                    $atr = (($prevAtr * ($period - 1)) + $tr) / $period;
                }
            }

            // ATR%
            $atrPercent = $atr !== null ? round(($atr / $close) * 100, 4) : null;

            $result[] = $candles[$i] + [
                'atr' => $atr !== null ? round($atr, 6) : null,
                'atr_percent' => $atrPercent,
            ];
        }

        return $result;
    }
}
