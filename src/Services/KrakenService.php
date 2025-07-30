<?php

namespace Services;

class KrakenService
{
    private string $apiUrl = 'https://api.kraken.com/0/public/OHLC';

    public function fetchCandles(string $pair, int $interval = 1): array
    {
        $url = "{$this->apiUrl}?pair={$pair}&interval={$interval}";

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,       // max 5 sek. na połączenie
            CURLOPT_TIMEOUT => 10,             // max 10 sek. na całość
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'TradingAnalyzer/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            return ['error' => "cURL error: $curlError"];
        }

        if ($httpCode !== 200) {
            return ['error' => "HTTP error: $httpCode"];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON response from Kraken'];
        }

        return $data['result'] ?? [];
    }
}
