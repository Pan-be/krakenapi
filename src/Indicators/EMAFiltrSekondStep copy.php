<?php

namespace Indicators;

class Mode
{

    public static function calculate(array $candles): int
    {
        if ($direction == 1 && EMA50 > EMA200) {
            return 1;
        } else {
            return 0;
        }

        if ($direction == -1 && EMA50 < EMA200) {
            return 1;
        } else {
            return 0;
        }


        return $direction;
    }
}
