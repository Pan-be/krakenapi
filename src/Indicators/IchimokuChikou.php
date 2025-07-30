<?php

namespace Indicators;

class IchimokuChikou
{
    public static function calculate(array $candles, int $shift = 26): array
    {
        for ($i = 0; $i < count($candles); $i++) {
            $indexShifted = $i - $shift;

            if ($indexShifted < 0) {
                $candles[$i]['ichimoku_chikou'] = null;
            } else {
                // Zamykająca cena przesunięta o 26 świec wstecz
                $candles[$i]['ichimoku_chikou'] = $candles[$indexShifted]['close'];
            }
        }

        return $candles;
    }
}
