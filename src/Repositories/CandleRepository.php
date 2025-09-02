<?php

namespace Repositories;

use Database\SQLiteConnector;
use PDO;

class CandleRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = SQLiteConnector::connect();
    }

    public function insertMany(string $pair, array $candles): void
    {
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO candles (pair, timestamp, open, high, low, close, vwap, volume, count)
            VALUES (:pair, :timestamp, :open, :high, :low, :close, :vwap, :volume, :count)
        ");

        foreach ($candles as $c) {
            $stmt->execute([
                ':pair' => $pair,
                ':timestamp' => $c['timestamp'],
                ':open' => $c['open'],
                ':high' => $c['high'],
                ':low' => $c['low'],
                ':close' => $c['close'],
                ':vwap' => $c['vwap'],
                ':volume' => $c['volume'],
                ':count' => $c['count'],
            ]);
        }
    }

    public function getByPairAndInterval(string $pair, int $fromTimestamp): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM candles
            WHERE pair = :pair AND timestamp >= :from
            ORDER BY timestamp ASC
        ");

        $stmt->execute([
            ':pair' => $pair,
            ':from' => $fromTimestamp
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
