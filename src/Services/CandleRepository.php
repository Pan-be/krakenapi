<?php

namespace Services;

use PDO;

/**
 * Persists raw OHLCV candles into storage/database.sqlite so history
 * accumulates across fetches (upsert on pair+interval+timestamp), instead of
 * each fetch overwriting the last one. Only raw candle fields are stored —
 * indicators are recomputed on read via Indicators\IndicatorCalculator, same
 * as they always have been from freshly-fetched API data.
 */
class CandleRepository
{
    private PDO $pdo;

    public function __construct(?string $dbPath = null)
    {
        $dbPath ??= __DIR__ . '/../../storage/database.sqlite';

        $this->pdo = new PDO('sqlite:' . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param array $candles Rows shaped like CandleProcessor::transform()'s
     *                       output: timestamp, open, high, low, close, vwap,
     *                       volume, count (datetime is derived, not stored).
     */
    public function upsertMany(string $pair, string $interval, array $candles): void
    {
        if (empty($candles)) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO candles (pair, "interval", timestamp, open, high, low, close, vwap, volume, count)
             VALUES (:pair, :interval, :timestamp, :open, :high, :low, :close, :vwap, :volume, :count)
             ON CONFLICT(pair, "interval", timestamp) DO UPDATE SET
                open = excluded.open,
                high = excluded.high,
                low = excluded.low,
                close = excluded.close,
                vwap = excluded.vwap,
                volume = excluded.volume,
                count = excluded.count'
        );

        $this->pdo->beginTransaction();

        try {
            foreach ($candles as $candle) {
                $stmt->execute([
                    'pair' => $pair,
                    'interval' => $interval,
                    'timestamp' => $candle['timestamp'],
                    'open' => $candle['open'],
                    'high' => $candle['high'],
                    'low' => $candle['low'],
                    'close' => $candle['close'],
                    'vwap' => $candle['vwap'],
                    'volume' => $candle['volume'],
                    'count' => $candle['count'],
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Returns up to $count most recent candles for pair+interval, oldest
     * first — ready to feed straight into Indicators\IndicatorCalculator::applyAll(),
     * same shape as CandleProcessor::transform()'s output.
     */
    public function fetchRecent(string $pair, string $interval, int $count): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM candles
             WHERE pair = :pair AND "interval" = :interval
             ORDER BY timestamp DESC
             LIMIT :count'
        );
        $stmt->bindValue('pair', $pair);
        $stmt->bindValue('interval', $interval);
        $stmt->bindValue('count', $count, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_reverse($rows); // oldest first

        return array_map(static function (array $row): array {
            $timestamp = (int) $row['timestamp'];

            return [
                'timestamp' => $timestamp,
                'datetime' => date('Y-m-d H:i:s', $timestamp),
                'open' => (float) $row['open'],
                'high' => (float) $row['high'],
                'low' => (float) $row['low'],
                'close' => (float) $row['close'],
                'vwap' => $row['vwap'] !== null ? (float) $row['vwap'] : null,
                'volume' => (float) $row['volume'],
                'count' => $row['count'] !== null ? (float) $row['count'] : null,
            ];
        }, $rows);
    }
}
