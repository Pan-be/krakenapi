<?php

namespace Indicators;

class 1hChecker
{

    public static function calculate(array $candles): int
    {
        if (macd_histogram>0 && macd_histogram_z_teraz>macd_histogram_z_-1h && macd>macd_signal && ADX>20) {
            return 1; // long
        } else (macd_histogram<0 && macd_histogram_z_teraz<macd_hisgram_z_-1h & macd<macd_signal adx>20) {
            return -1; // short
        } else {
            return 0;
        }


        return $direction;
    }
}