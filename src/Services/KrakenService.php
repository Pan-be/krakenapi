<?php

namespace Services;

class KrakenService
{
    private function intervalToSeconds(string $interval): int
    {
        return match ($interval) {
            '1h' => 3600,
            '4h' => 14400,
            default => throw new \InvalidArgumentException("Unsupported interval: $interval")
        };
    }

    private function alignTimestampToInterval(int $timestamp, int $intervalSeconds): int
    {
        return floor($timestamp / $intervalSeconds) * $intervalSeconds;
    }


    private string $apiUrl = 'https://futures.kraken.com/api/charts/v1/trade/';

    // public function fetchCandles(string $pair, string $interval, ?int $since = null): array
    // {
    //     $queryParams = [
    //         'count' => 7000
    //     ];

    //     if ($since !== null) {
    //         $queryParams['from'] = $since;
    //     }

    //     $queryString = http_build_query($queryParams);

    //     $url = "{$this->apiUrl}{$pair}/{$interval}?{$queryString}";

    public function fetchCandles(
        string $pair,
        string $interval,
        int $count,
        ?int $from = null
    ): array {
        $intervalSeconds = $this->intervalToSeconds($interval);

        // jeśli nie podano from → teraz
        $from = $from ?? time();

        // wyrównanie do interwału
        $alignedFrom = $this->alignTimestampToInterval($from, $intervalSeconds);

        // obliczamy start (pierwszą świecę)
        $startTimestamp = $alignedFrom - (($count - 1) * $intervalSeconds);

        $queryParams = [
            'count' => $count,
            'from'  => $startTimestamp
        ];

        $queryString = http_build_query($queryParams);
        $url = "{$this->apiUrl}{$pair}/{$interval}?{$queryString}";


        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
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
            return [
                'error' => 'Invalid JSON response from Kraken',
                'url' => $url,
                'raw_response' => substr($response, 0, 300) // wyświetl fragment odpowiedzi
            ];
        }

        return $data['candles'] ?? [];
    }
}
