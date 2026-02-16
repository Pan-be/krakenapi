<?php

require_once __DIR__ . '/../../autoload.php';

use Controllers\CandleFetcherController;

date_default_timezone_set('UTC');

/**
 * Czekamy kilka sekund,
 * żeby Kraken zdążył zamknąć świecę
 */
sleep(5);

$pairs = [
    'Pf_ETHUSD',
    'Pf_XBTUSD',
    'PF_ATOMUSD',
    'Pf_LTCUSD'
];

$interval = '4h';
$count = 300;

$controller = new CandleFetcherController();

$controller->candleHandle($pairs, $interval, $count, null);

echo "4h candles updated at " . date('Y-m-d H:i:s') . PHP_EOL;
