<?php

require_once __DIR__ . '/autoload.php';

use Core\ErrorHandler;
use Core\Config;
use Core\Request;
use Controllers\CandleFetcherController;

set_exception_handler([ErrorHandler::class, 'handleException']);

header('Content-Type: application/json');

$allowedIntervals = Config::load('allowedIntervals');

$interval = Request::getIntervalFromQuery($allowedIntervals);

$controller = new CandleFetcherController();
$data = $controller->candleHandle($interval);

echo json_encode($data, JSON_PRETTY_PRINT);
