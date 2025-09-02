<?php

namespace Indicators;

class DateTime
{
    public static function calculate(array $candles): array
    {

        $result = [];

        foreach ($candles as $candle) {
            if (!isset($candle['timestamp'])) {
                // Pomijamy świecę bez timestampu
                $result[] = $candle + ['datetime' => null];
                continue;
            }

            // Zamiana timestampu na format "Y-m-d H:i:s"
            $time = (int)$candle['timestamp'];
            $datetime = date('Y-m-d H:i:s', $time);

            $result[] = $candle + ['datetime' => $datetime];
        }

        return $result;
    }
}
