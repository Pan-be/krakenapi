<?php

namespace Controllers;

use Services\KrakenService;
use Services\CandleProcessor;
use Indicators\IndicatorCalculator;
use Core\Config;
use Repositories\CandleRepository;

class CandleFetcherController
{
    private CandleRepository $repository;

    public function __construct()
    {
        $this->repository = new CandleRepository();
    }

    public function candleHandle(int $interval): array
    {
        $kraken = new KrakenService();
        $processor = new CandleProcessor();
        $pairs = Config::load('test_pairs');

        $results = [];

        foreach ($pairs as $pair) {
            // 1. Pobierz ostatni znany timestamp z bazy
            $latestCandles = $this->repository->getByPairAndInterval($pair, 0);
            $lastTimestamp = 0;

            if (!empty($latestCandles)) {
                $lastTimestamp = end($latestCandles)['timestamp'];
            }

            // 2. Pobierz z API dane od ostatniego timestampu
            $rawData = $kraken->fetchCandles($pair, $interval);

            if (isset($rawData['error'])) {
                $results[$pair] = ['error' => $rawData['error']];
                continue;
            }

            $keys = array_keys($rawData);
            if (empty($keys) || !isset($rawData[$keys[0]]) || !is_array($rawData[$keys[0]])) {
                $results[$pair] = ['error' => 'invalid or empty data structure'];
                continue;
            }

            $candleData = $rawData[$keys[0]];
            $transformed = $processor->transform($candleData);

            // 3. Przefiltruj tylko nowe świece
            $newCandles = array_filter($transformed, fn($c) => $c['timestamp'] > $lastTimestamp);

            if (!empty($newCandles)) {
                $this->repository->insertMany($pair, $newCandles);
            }

            // 4. Pobierz kompletne dane (np. od ostatnich 200 świec)
            $completeCandles = $this->repository->getByPairAndInterval($pair, time() - 60 * $interval * 300); // ostatnie 300 świec

            // 5. Oblicz wskaźniki i zwróć
            $enhanced = IndicatorCalculator::applyAll($completeCandles);
            $results[$pair] = $enhanced;
        }

        return $results;
    }
}
