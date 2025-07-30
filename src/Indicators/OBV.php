<?php

namespace Indicators;

class OBV
{
    public static function calculate(array $candles): array
    {
        $obv = 0;
        $result = [];

        foreach ($candles as $i => $candle) {
            if ($i === 0) {
                $result[] = $candle + ['obv' => 0];
                continue;
            }

            $prevClose = $candles[$i - 1]['close'];

            if ($candle['close'] > $prevClose) {
                $obv += $candle['volume'];
            } elseif ($candle['close'] < $prevClose) {
                $obv -= $candle['volume'];
            }

            $result[] = $candle + ['obv' => (int)$obv];
        }

        return $result;
    }
}
