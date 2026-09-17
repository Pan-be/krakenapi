#!/usr/bin/env php
<?php
/**
 * One-off migration for storage/database.sqlite's "candles" table:
 * - adds the "interval" column (missing today, so pair+timestamp can't tell
 *   a 1h candle apart from a 4h one)
 * - adds a UNIQUE index on (pair, interval, timestamp) so upserts have
 *   something to conflict on, and lookback queries (ORDER BY timestamp) are
 *   indexed instead of full-scanning
 *
 * Deliberately does NOT delete the ~130k existing rows (leftover 15m-interval
 * spot data from 2025-07-28 to 2025-08-11 — a different era of this project,
 * unrelated to the Futures data fetched today). They get "interval" = NULL,
 * which is harmless: SQLite treats NULL as distinct in a UNIQUE index, so
 * they never conflict with new rows (which always set a real interval), and
 * their pair names (e.g. "XBTUSD") don't collide with today's Futures pair
 * IDs (e.g. "Pf_XBTUSD") anyway. They just sit there as inert legacy data,
 * same as they already do today.
 *
 * Idempotent: safe to run more than once.
 *
 * Usage: php scripts/migrate_candles_schema.php [path/to/database.sqlite]
 */

$dbPath = $argv[1] ?? __DIR__ . '/../storage/database.sqlite';

if (!file_exists($dbPath)) {
    fwrite(STDERR, "Database not found: $dbPath\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$columns = $pdo->query('PRAGMA table_info(candles)')->fetchAll(PDO::FETCH_ASSOC);
$columnNames = array_column($columns, 'name');

if (!in_array('interval', $columnNames, true)) {
    echo "Adding \"interval\" column...\n";
    $pdo->exec('ALTER TABLE candles ADD COLUMN "interval" TEXT');
} else {
    echo "\"interval\" column already present — skipping.\n";
}

$indexes = $pdo->query("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='candles'")
    ->fetchAll(PDO::FETCH_COLUMN);

// Pre-existing UNIQUE(pair, timestamp) index from before intervals were mixed in — wrong now,
// since a 4h candle and a 1h candle can share the exact same timestamp at aligned boundaries.
if (in_array('idx_pair_timestamp', $indexes, true)) {
    echo "Dropping stale UNIQUE(pair, timestamp) index (doesn't account for \"interval\")...\n";
    $pdo->exec('DROP INDEX idx_pair_timestamp');
}

if (!in_array('idx_candles_pair_interval_timestamp', $indexes, true)) {
    echo "Creating unique index idx_candles_pair_interval_timestamp...\n";
    $pdo->exec(
        'CREATE UNIQUE INDEX idx_candles_pair_interval_timestamp ON candles(pair, "interval", timestamp)'
    );
} else {
    echo "Index already present — skipping.\n";
}

$count = $pdo->query('SELECT COUNT(*) FROM candles')->fetchColumn();
echo "Done. candles table now has $count row(s).\n";
