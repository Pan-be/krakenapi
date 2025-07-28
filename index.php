<?php

require_once __DIR__ . '/autoload.php';

use Core\ErrorHandler;
use Core\Config;
use Core\Request;
use Services\CandleProcessor;
use Services\KrakenService;

set_exception_handler([ErrorHandler::class, 'handleException']);

header('Content-Type: application/json');

$pairs = Config::load('pairs');
$allowedIntervals = Config::load('allowedIntervals');

$interval = Request::getIntervalFromQuery($allowedIntervals);

$kraken = new KrakenService();
$processor = new CandleProcessor();

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

echo json_encode($results, JSON_PRETTY_PRINT);
