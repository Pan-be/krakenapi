<?php

namespace Services;

/**
 * Skeleton client for Kraken Futures' authenticated trading REST API.
 *
 * Separate from KrakenService (which only calls the public, unauthenticated
 * candle endpoint). This one signs requests and can place real orders once
 * given real keys, so treat it accordingly.
 *
 * Auth algorithm (Kraken Futures REST, current scheme post 2025-10-01):
 *   message   = postData . nonce . endpointPath
 *   sha256    = SHA256(message)                      [raw bytes]
 *   Authent   = base64( HMAC-SHA512( sha256, base64_decode(apiSecret) ) )
 * Headers: APIKey, Authent, Nonce.
 * Source: https://docs.kraken.com/api/docs/guides/futures-rest/
 *
 * endpointPath for signing is the path WITHOUT the "/derivatives" prefix
 * (e.g. "/api/v3/sendorder"), per Kraken's own examples. Verify this against
 * a real account before relying on it — get a 401/"authenticationError" and
 * this is the first thing to double check.
 */
class KrakenFuturesTradingService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl = 'https://futures.kraken.com';

    public function __construct(?string $apiKey = null, ?string $apiSecret = null)
    {
        $this->apiKey = $apiKey ?? (string) getenv('KRAKEN_FUTURES_API_KEY');
        $this->apiSecret = $apiSecret ?? (string) getenv('KRAKEN_FUTURES_API_SECRET');

        if ($this->apiKey === '' || $this->apiSecret === '') {
            throw new \RuntimeException(
                'Kraken Futures API credentials missing. Set KRAKEN_FUTURES_API_KEY ' .
                'and KRAKEN_FUTURES_API_SECRET (env vars), or pass them to the constructor.'
            );
        }
    }

    private function nonce(): string
    {
        return (string) round(microtime(true) * 1000);
    }

    private function sign(string $endpointPath, string $nonce, string $postData): string
    {
        $message = $postData . $nonce . $endpointPath;
        $sha256 = hash('sha256', $message, true);
        $secretDecoded = base64_decode($this->apiSecret, true);

        if ($secretDecoded === false) {
            throw new \RuntimeException('KRAKEN_FUTURES_API_SECRET is not valid base64.');
        }

        $hmac = hash_hmac('sha512', $sha256, $secretDecoded, true);

        return base64_encode($hmac);
    }

    /**
     * @param string $endpointPath e.g. "/api/v3/sendorder" — used both for the
     *   URL (prefixed with /derivatives) and for the signature (as-is).
     * @param array<string,scalar> $params form params, order not significant.
     * @return array decoded JSON response.
     */
    private function post(string $endpointPath, array $params): array
    {
        $postData = http_build_query($params);
        $nonce = $this->nonce();
        $authent = $this->sign($endpointPath, $nonce, $postData);

        $url = $this->baseUrl . '/derivatives' . $endpointPath;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'TradingAnalyzer/1.0',
            CURLOPT_HTTPHEADER => [
                'APIKey: ' . $this->apiKey,
                'Authent: ' . $authent,
                'Nonce: ' . $nonce,
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['error' => "cURL error: $curlError"];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Invalid JSON response from Kraken Futures',
                'http_code' => $httpCode,
                'raw_response' => substr($response, 0, 500),
            ];
        }

        return $data;
    }

    /**
     * Place an order. This is the skeleton — covers market and limit orders,
     * the two shapes the ported decision engine will actually need (entry at
     * market/1h-close, exit at market when SL/TP/time fires). Stop/take-profit
     * native order types (stp/take_profit) are NOT wired here; the documented
     * exit engine (see AI-Brain "Kraken Trading Bot" note) closes positions by
     * sending an opposite-side market order itself rather than relying on
     * resting stop orders, so that's deliberately out of scope for now.
     *
     * @param string $symbol e.g. "PF_XBTUSD"
     * @param string $side "buy" | "sell"
     * @param float $size contract size
     * @param string $orderType "mkt" | "lmt"
     * @param float|null $limitPrice required if $orderType === "lmt"
     * @param string|null $cliOrdId your own idempotency/tracking id
     */
    public function sendOrder(
        string $symbol,
        string $side,
        float $size,
        string $orderType = 'mkt',
        ?float $limitPrice = null,
        ?string $cliOrdId = null
    ): array {
        if (!in_array($side, ['buy', 'sell'], true)) {
            throw new \InvalidArgumentException('side must be "buy" or "sell"');
        }
        if (!in_array($orderType, ['mkt', 'lmt'], true)) {
            throw new \InvalidArgumentException('orderType must be "mkt" or "lmt"');
        }
        if ($orderType === 'lmt' && $limitPrice === null) {
            throw new \InvalidArgumentException('limitPrice is required for orderType "lmt"');
        }

        $params = [
            'orderType' => $orderType,
            'symbol' => $symbol,
            'side' => $side,
            'size' => $size,
        ];

        if ($limitPrice !== null) {
            $params['limitPrice'] = $limitPrice;
        }
        if ($cliOrdId !== null) {
            $params['cliOrdId'] = $cliOrdId;
        }

        return $this->post('/api/v3/sendorder', $params);
    }
}
