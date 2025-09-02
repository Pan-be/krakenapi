<?php

require_once __DIR__ . '/autoload.php';

use Database\SQLiteConnector;

$pdo = SQLiteConnector::connect();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS candles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pair TEXT NOT NULL,
        timestamp INTEGER NOT NULL,
        open REAL,
        high REAL,
        low REAL,
        close REAL,
        vwap REAL,
        volume REAL,
        count REAL
    );
");

echo "Migration done.\n";
