<?php

namespace Services;

class KrakenService
{
    private string $apiUrl = 'https://api.kraken.com/0/public/OHLC';

    public function fetchCandles(string $pair, int $interval = 1): array
    {
        $url = "{$this->apiUrl}?pair={$pair}&interval={$interval}";
        $json = file_get_contents(($url));

        if (!$json) {
            return ['error' => 'could not connect to Kraken API'];
        }

        $data = json_decode($json, true);
        return $data['result'] ?? [];
    }
}
