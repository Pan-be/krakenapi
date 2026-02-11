<?php

namespace Indicators;

class SMA20ATRPercent
{
    public static function calculate(array $candles): array
    {
        $result = [];

        for ($i = 0; $i < count($candles); $i++) {
            if ($i < 19) {
                $result[] = $candles[$i] + ['sma20_atr_percent' => null];
                continue;
            }

            $sum = 0;
            $valid = true;

            for ($j = $i - 19; $j <= $i; $j++) {
                if (!isset($candles[$j]['atr_percent'])) {
                    $valid = false;
                    break;
                }
                $sum += $candles[$j]['atr_percent'];
            }

            $result[] = $candles[$i] + [
                'sma20_atr_percent' => $valid ? round($sum / 20, 6) : null
            ];
        }

        return $result;
    }
}
