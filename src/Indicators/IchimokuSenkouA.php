<?php

namespace Indicators;

class IchimokuSenkouA
{
    public static function calculate(array $candles): array
    {
        $result = [];

        // Wartości Tenkan i Kijun muszą być już obliczone, więc sprawdzamy ich obecność
        for ($i = 0; $i < count($candles); $i++) {
            if ($i < 25) { // 26 - 1 przesunięcie do przodu
                $result[] = $candles[$i] + ['ichimoku_senkou_a' => null];
                continue;
            }

            // Senkou A to średnia z Tenkan i Kijun przesunięta o 26 świec do przodu
            if (!isset($candles[$i]['ichimoku_tenkan']) || !isset($candles[$i]['ichimoku_kijun'])) {
                $result[] = $candles[$i] + ['ichimoku_senkou_a' => null];
                continue;
            }

            $senkouAValue = ($candles[$i]['ichimoku_tenkan'] + $candles[$i]['ichimoku_kijun']) / 2;

            // Przesuwamy o 26 świec do przodu — w praktyce dodajemy tę wartość do świecy $i+26, jeśli istnieje
            $indexShifted = $i + 26;

            if ($indexShifted < count($candles)) {
                // Kopiujemy świecę z przesunięciem i ustawiamy wartość Senkou A
                $candles[$indexShifted]['ichimoku_senkou_a'] = round($senkouAValue, 6);
            }

            $result[] = $candles[$i]; // bieżąca świeca bez tej wartości, bo jest "w przyszłości"
        }

        return $candles;
    }
}
