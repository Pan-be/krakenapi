#!/usr/bin/env php
<?php

require_once __DIR__ . '/../autoload.php';

use Controllers\CandleFetcherController;
use Repositories\CandleRepository;
use Core\Config;

// Pobieranie interwału z linii poleceń
$interval = $argv[1] ?? null;

if (!$interval || !is_numeric($interval)) {
    echo "Użycie: php sync-candles.php <interwał>\n";
    exit(1);
}

$controller = new CandleFetcherController();
$repository = new CandleRepository();

$results = $controller->candleHandle((int)$interval);

foreach ($results as $pair => $candlesOrError) {
    if (isset($candlesOrError['error'])) {
        echo "[ERROR] {$pair}: {$candlesOrError['error']}\n";
        continue;
    }

    try {
        $repository->insertMany($pair, $candlesOrError);
        echo "[OK] {$pair}: zapisano " . count($candlesOrError) . " świec.\n";
        var_dump($results);
    } catch (Exception $e) {
        echo "[FAIL] {$pair}: błąd zapisu do bazy: {$e->getMessage()}\n";
    }
}
