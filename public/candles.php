<?php
// ini_set('memory_limit', '1024M');
// ini_set('memory_limit', '2048M');
// ini_set('memory_limit', '3072M');
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// require_once __DIR__ . '/../autoload.php';

// use Core\ErrorHandler;
// use Core\Config;
// use Core\Request;
// use Controllers\CandleFetcherController;

// set_exception_handler([ErrorHandler::class, 'handleException']);

// header('Content-Type: application/json');

// $allowedIntervals = Config::load('allowedIntervals');

// $interval = Request::getIntervalFromQuery($allowedIntervals);

// $controller = new CandleFetcherController();
// $data = $controller->candleHandle($interval);

// echo json_encode($data, JSON_PRETTY_PRINT);

ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../autoload.php';

use Core\ErrorHandler;
use Core\Config;
use Controllers\CandleFetcherController;

set_exception_handler([ErrorHandler::class, 'handleException']);

$isDownload = isset($_GET['download']);

if ($isDownload) {
    header('Content-Type: text/csv');
} else {
    header('Content-Type: text/html; charset=utf-8');
}

// ✅ Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ✅ Pobranie danych
// $pairs     = $_POST['pairs'] ?? [];
// $interval  = $_POST['interval'] ?? null;
// $startDate = $_POST['start_date'] ?? null;
$pairs     = $_POST['pairs'] ?? [];
$interval  = $_POST['interval'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$count     = isset($_POST['count']) ? (int) $_POST['count'] : null;


// ✅ Walidacja podstawowa
// if (empty($pairs) || !$interval || !$startDate) {
if (empty($pairs) || !$interval || !$count) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// ✅ Walidacja interval (whitelist)
$allowedIntervals = Config::load('allowedIntervals');

if (!in_array($interval, $allowedIntervals)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid interval']);
    exit;
}

// ✅ Walidacja par (whitelist z config)
$allowedPairs = Config::load('pairs');

$validatedPairs = array_values(array_intersect($pairs, $allowedPairs));

if (empty($validatedPairs)) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid pairs selected']);
    exit;
}

// ✅ Konwersja daty
// $since = strtotime($startDate);

// if (!$since) {
//     http_response_code(400);
//     echo json_encode(['error' => 'Invalid date format']);
//     exit;
// }
$since = null;

if (!empty($startDate)) {
    $since = strtotime($startDate);

    if (!$since) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        exit;
    }
}

if ($count < 1 || $count > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid count value']);
    exit;
}


// ✅ Uruchomienie controllera
$controller = new CandleFetcherController();
// $data = $controller->candleHandle($validatedPairs, $interval, $since);
$data = $controller->candleHandle($validatedPairs, $interval, $count, $since);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imperium 1.1.0</title>
</head>

<style>
    /* vt323-regular - latin_latin-ext */
    @font-face {
        font-display: swap;
        /* Check https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display for other options. */
        font-family: 'VT323';
        font-style: normal;
        font-weight: 400;
        src: url('./fonts/vt323-v18-latin_latin-ext-regular.woff2') format('woff2');
        /* Chrome 36+, Opera 23+, Firefox 39+, Safari 12+, iOS 10+ */
    }

    body {
        background-color: #18181a;
        font-family: 'VT323';
        color: whitesmoke;
        display: grid;
        justify-items: center;
        text-align: center;
        padding: 40px;
    }

    .section {
        margin-bottom: 25px;
    }

    .pairs-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    button {
        padding: 10px 20px;
        cursor: pointer;
    }
</style>

<body>
    <h2>Export CSV</h2>

    <a href="exportCsv.php?interval=1h">
        <button>Download 1h CSV</button>
    </a>

    <a href="exportCsv.php?interval=4h">
        <button>Download 4h CSV</button>
    </a>
</body>

</html>