<?php

namespace Controllers;

use Services\KrakenService;
use Services\CandleProcessor;
use Indicators\IndicatorCalculator;

class CandleFetcherController
{
    public function candleHandle(array $pairs, string $interval, int $since): array
    {
        $kraken = new KrakenService();
        $processor = new CandleProcessor();

        $results = [];

        foreach ($pairs as $pair) {
            $rawData = $kraken->fetchCandles($pair, $interval, $since);

            if (isset($rawData['error'])) {
                $results[$pair] = ['error' => $rawData['error']];
                continue;
            }

            if (empty($rawData) || !is_array($rawData)) {
                $results[$pair] = ['error' => 'invalid or empty data structure', 'raw' => $rawData];
                continue;
            }

            $candleData = $rawData; // bo fetchCandles już zwraca $data['candles']

            if (empty($candleData) || !is_array($candleData)) {
                $results[$pair] = ['error' => 'invalid or empty data structure', 'raw' => $rawData];
                continue;
            }

            // 👇 debug
            // file_put_contents('/tmp/debug_candles.json', json_encode($candleData, JSON_PRETTY_PRINT));

            $transformedCandles = $processor->transform($candleData);
            $enhancedCandles = IndicatorCalculator::applyAll($transformedCandles);
            $results[$pair] = $enhancedCandles;

            // 👇 zapis do pliku per para
            $dir = __DIR__ . '/../../public/json/candles/' . $interval;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $filePath = $dir . '/' . $pair . '.json';
            file_put_contents($filePath, json_encode($enhancedCandles, JSON_PRETTY_PRINT));
        }

        return $results;
    }
}
