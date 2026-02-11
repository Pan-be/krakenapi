<?php

namespace Indicators;

class Mode
{

    public static function calculate(array $candles): int
    {
        if (volume >= SMA20Volume) {
            return 1;
        } else {
            return 0;
        }


        return $direction;
    }
}
