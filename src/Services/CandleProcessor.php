<?php

namespace Services;

class CandleProcessor
{
    public function transform(array $rawCandles): array
    {
        $result = [];

        foreach ($rawCandles as $candle) {
            [$time, $open, $high, $low, $close, $vwap, $volume, $count] = $candle;

            $dataPoint = [
                'timestamp' => (int)$time,
                'open' => (float)$open,
                'high' => (float)$high,
                'low' => (float)$low,
                'close' => (float)$close,
                'vwap' => (float)$vwap,
                'volume' => (float)$volume,
                'count' => (float)$count,
            ];

            $result[] = $dataPoint;
        }

        return $result;
    }
}
