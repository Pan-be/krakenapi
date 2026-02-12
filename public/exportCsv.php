<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$interval = $_GET['interval'] ?? null;

if (!$interval) {
    die("Interval not specified.");
}

$inputDir = __DIR__ . "/json/candles/$interval";

if (!is_dir($inputDir)) {
    die("Directory not found: $inputDir");
}

$files = glob("$inputDir/*.json");

if (empty($files)) {
    die("No JSON files found for interval $interval");
}

// nagłówki downloadu
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="candles_' . $interval . '.csv"');

$output = fopen('php://output', 'w');

$headerWritten = false;

foreach ($files as $jsonFile) {

    $data = json_decode(file_get_contents($jsonFile), true);

    if (!$data || !is_array($data)) {
        continue;
    }

    if (!$headerWritten) {
        $headers = array_keys($data[0]);
        fputcsv($output, array_merge(['pair'], $headers));
        $headerWritten = true;
    }

    $pair = basename($jsonFile, '.json');

    foreach ($data as $row) {
        $ordered = [];
        foreach ($headers as $h) {
            $ordered[] = $row[$h] ?? '';
        }

        fputcsv($output, array_merge([$pair], $ordered));
    }
}

fclose($output);
exit;
