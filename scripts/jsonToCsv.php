#!/usr/bin/env php
<?php
/**
 * CLI tool: Convert JSON candle files to CSV
 *
 * Usage:
 *   php scripts/jsonToCsv.php <interval> [--pair=PAIR] [--output=csv]
 *
 * Examples:
 *   php scripts/jsonToCsv.php 1m
 *   php scripts/jsonToCsv.php 5m --pair=ETHUSD
 */

$argvCopy = $argv;
array_shift($argvCopy); // remove script name

if (empty($argvCopy)) {
    echo "Usage: php scripts/jsonToCsv.php <interval> [--pair=PAIR] [--output=csv]\n";
    exit(1);
}

$interval = $argvCopy[0] ?? '1h';

// parse options
$options = [];
foreach ($argvCopy as $arg) {
    if (str_starts_with($arg, '--')) {
        [$key, $val] = array_pad(explode('=', substr($arg, 2), 2), 2, true);
        $options[$key] = $val;
    }
}

$pairFilter = $options['pair'] ?? null;
$outputFormat = $options['output'] ?? 'csv';

$inputDir = __DIR__ . "/../storage/candles/$interval";
$outputDir = __DIR__ . "/../storage/$outputFormat/$interval";

if (!is_dir($inputDir)) {
    fwrite(STDERR, "Input directory not found: $inputDir\n");
    exit(1);
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$files = glob("$inputDir/*.json");
if ($pairFilter) {
    $files = array_filter($files, fn($f) => basename($f, '.json') === $pairFilter);
}

if (empty($files)) {
    echo "No JSON files found for interval '$interval'" . ($pairFilter ? " and pair '$pairFilter'" : "") . "\n";
    exit(0);
}

foreach ($files as $jsonFile) {
    $pair = basename($jsonFile, '.json');
    $outFile = "$outputDir/$pair.$outputFormat";

    $data = json_decode(file_get_contents($jsonFile), true);
    if (!$data || !is_array($data)) {
        echo "Skipping invalid file: $jsonFile\n";
        continue;
    }

    if ($outputFormat === 'csv') {
        $fp = fopen($outFile, 'w');
        $headers = array_keys($data[0]);
        fputcsv($fp, $headers);

        foreach ($data as $row) {
            $ordered = [];
            foreach ($headers as $h) {
                $ordered[] = $row[$h] ?? ''; // jeśli brak pola → pusta wartość
            }
            fputcsv($fp, $ordered);
        }

        fclose($fp);
    } else {
        file_put_contents($outFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    echo "Converted $jsonFile -> $outFile\n";
}
