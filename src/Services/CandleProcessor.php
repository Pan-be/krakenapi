<?php

namespace Services;

class CandleProcessor
{
    public function transform(array $candles): array
    {
        $result = [];

        foreach ($candles as $candle) {
            // format z kluczami
            if (isset($candle['time'], $candle['open'], $candle['high'], $candle['low'], $candle['close'], $candle['volume'])) {
                $time   = (int) ($candle['time'] / 1000);
                $open   = (float) $candle['open'];
                $high   = (float) $candle['high'];
                $low    = (float) $candle['low'];
                $close  = (float) $candle['close'];
                $volume = (float) $candle['volume'];

                // fallback: format z indeksami
            } elseif (is_array($candle) && count($candle) >= 6) {
                $time   = (int) ($candle[0] / 1000);
                $open   = (float) $candle[1];
                $high   = (float) $candle[2];
                $low    = (float) $candle[3];
                $close  = (float) $candle[4];
                $volume = (float) $candle[5];
            } else {
                continue;
            }

            $result[] = [
                'timestamp' => $time,
                'datetime'  => date('Y-m-d H:i:s', $time),
                'open'      => $open,
                'high'      => $high,
                'low'       => $low,
                'close'     => $close,
                'vwap'      => null,   // brak w Kraken Futures
                'volume'    => $volume,
                'count'     => null,   // brak w Kraken Futures
            ];
        }

        return $result;
    }
}
