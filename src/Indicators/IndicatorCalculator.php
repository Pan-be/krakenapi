<?php

namespace Indicators;

class IndicatorCalculator
{
    public static function applyAll(array $candles): array
    {
        $candles = SMA20::calculate($candles);
        $candles = SMA50::calculate($candles);
        $candles = SMA200::calculate($candles);
        $candles = EMA20::calculate($candles);
        $candles = EMA50::calculate($candles);
        $candles = EMA200::calculate($candles);
        // $candles = RSI::calculate($candles);
        // $candles = MACD::calculate($candles);
        // $candles = OBV::calculate($candles);
        // $candles = BollingerBands::calculate($candles);

        $candles = IchimokuTenkan::calculate($candles);
        $candles = IchimokuKijun::calculate($candles);
        $candles = IchimokuSenkouA::calculate($candles);
        $candles = IchimokuSenkouB::calculate($candles);
        // $candles = IchimokuChikou::calculate($candles);

        $candles = ADX::calculate($candles);
        $candles = SuperTrend::calculate($candles);
        $candles = ATRPercent::calculate($candles, 14);
        // $candles = FinalSignal::calculate($candles, [
        //     'AT5' => 44,
        //     'AT6' => 0,
        //     'AT42' => 0.025,
        //     'AT43' => 0
        // ]);


        return $candles;
    }
}