<?php

namespace Controllers;

use Services\KrakenService;
use Services\CandleProcessor;
use Core\Config;

class CandleFetcherController
{
    public function candleHandle(int $interval): array
    {
        $kraken = new KrakenService();
        $processor = new CandleProcessor();
        $pairs = Config::load('pairs');

        $results = [];

        foreach ($pairs as $pair) {
            $rawData = $kraken->fetchCandles($pair, $interval);

            if (isset($rawData['error'])) {
                // Dodaj błąd do wyników dla konkretnej pary
                $results[$pair] = ['error' => $rawData['error']];
                continue;
            }

            $keys = array_keys($rawData);
            if (empty($keys) || !isset($rawData[$keys[0]]) || !is_array($rawData[$keys[0]])) {
                $results[$pair] = ['error' => 'invalid or empty data structure'];
                continue;
            }

            $candleData = $rawData[$keys[0]];
            $transformedCandles = $processor->transform($candleData);
            $results[$pair] = $transformedCandles;
        }

        return $results;
    }
}
