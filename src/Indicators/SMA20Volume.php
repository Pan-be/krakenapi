<?php

namespace Indicators;

class SMA20Volume
{
    public static function calculate(array $candles): array
    {
        $result = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < 19) {
                $result[] = $candles[$i] + ['sma20_volume' => null];
                continue;
            }

            $sum = 0;
            for ($j = $i - 19; $j <= $i; $j++) {
                $sum += $candles[$j]['volume'];
            }

            $sma = $sum / 20;
            $result[] = $candles[$i] + ['sma20_volume' => round($sma, 6)];
        }

        return $result;
    }
}
