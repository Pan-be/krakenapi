<?php

namespace Indicators;

class ADX
{
    /**
     * Zwraca w świecach: 'adx', '+di', '-di'
     */
    public static function calculate(array $candles, int $period = 14): array
    {
        $n = count($candles);
        if ($n === 0) return $candles;

        $tr = array_fill(0, $n, null);
        $plusDM = array_fill(0, $n, null);
        $minusDM = array_fill(0, $n, null);

        $atr = array_fill(0, $n, null);        // ATR (Wilder)
        $smPlusDM = array_fill(0, $n, null);   // wygładzony +DM (Wilder)
        $smMinusDM = array_fill(0, $n, null);  // wygładzony -DM (Wilder)

        $dx = array_fill(0, $n, null);
        $adxArr = array_fill(0, $n, null);

        for ($i = 0; $i < $n; $i++) {
            // Pierwsza świeca – brak poprzedniej
            if ($i === 0) {
                $candles[$i]['adx'] = null;
                $candles[$i]['+di'] = null;
                $candles[$i]['-di'] = null;
                continue;
            }

            $high      = (float)$candles[$i]['high'];
            $low       = (float)$candles[$i]['low'];
            $closePrev = (float)$candles[$i - 1]['close'];
            $highPrev  = (float)$candles[$i - 1]['high'];
            $lowPrev   = (float)$candles[$i - 1]['low'];

            // True Range
            $tr[$i] = max($high - $low, abs($high - $closePrev), abs($low - $closePrev));

            // Directional Movement (surowy)
            $upMove   = $high - $highPrev;
            $downMove = $lowPrev - $low;

            $plusDM[$i]  = ($upMove > $downMove && $upMove > 0)   ? $upMove   : 0.0;
            $minusDM[$i] = ($downMove > $upMove && $downMove > 0) ? $downMove : 0.0;

            // Inicjalizacja wygładzania Wildera w punkcie i == $period
            if ($i === $period) {
                $atr[$i]      = array_sum(array_slice($tr, 1, $period)) / $period;
                $smPlusDM[$i] = array_sum(array_slice($plusDM, 1, $period));
                $smMinusDM[$i] = array_sum(array_slice($minusDM, 1, $period));
            }
            // Kolejne kroki wygładzania
            if ($i > $period) {
                // ATR
                $atr[$i] = (($atr[$i - 1] * ($period - 1)) + $tr[$i]) / $period;
                // +DM i -DM (Wilder)
                $smPlusDM[$i]  = $smPlusDM[$i - 1] - ($smPlusDM[$i - 1] / $period) + $plusDM[$i];
                $smMinusDM[$i] = $smMinusDM[$i - 1] - ($smMinusDM[$i - 1] / $period) + $minusDM[$i];
            }

            // Obliczenia DI/DX dopiero gdy mamy ATR
            $plusDI = null;
            $minusDI = null;
            $dxVal = null;
            if ($i >= $period && $atr[$i] !== null && $atr[$i] > 0) {
                $plusDI  = 100.0 * ($smPlusDM[$i]  / $atr[$i]);
                $minusDI = 100.0 * ($smMinusDM[$i] / $atr[$i]);

                $sumDI = $plusDI + $minusDI;
                $dxVal = ($sumDI > 0)
                    ? 100.0 * abs($plusDI - $minusDI) / $sumDI
                    : 0.0; // brak kierunkowości => DX = 0
                $dx[$i] = $dxVal;
            }

            // ADX: najpierw średnia z DX z jednego okresu (i == 2*period-1)
            if ($i === (2 * $period - 1)) {
                $window = array_slice($dx, $period, $period);
                $window = array_values(array_filter($window, static fn($v) => $v !== null));
                $adxArr[$i] = count($window) ? array_sum($window) / count($window) : null;
            } elseif ($i > (2 * $period - 1)) {
                // wygładzanie Wildera DX
                $prev = $adxArr[$i - 1];
                $adxArr[$i] = ($prev !== null && $dxVal !== null)
                    ? (($prev * ($period - 1)) + $dxVal) / $period
                    : $prev;
            }

            $candles[$i]['adx'] = $adxArr[$i]  !== null ? round($adxArr[$i], 2)  : null;
            $candles[$i]['+di'] = $plusDI      !== null ? round($plusDI, 2)      : null;
            $candles[$i]['-di'] = $minusDI     !== null ? round($minusDI, 2)     : null;
        }

        return $candles;
    }
}
