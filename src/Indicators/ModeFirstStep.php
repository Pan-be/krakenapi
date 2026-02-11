<?php

namespace Indicators;

class Mode
{
    private static function superTrendCalc(array $candles): int
    {
        if ($SuperTrend == "up") {
            return 1;
        } elseif ($SuperTrend == "down") {
            return -1;
        } else {
            return //do nothing
        }
    }

    private static function ichimokuTrendCalc(array $candales): int {
        $heigherIchimoku;
        $lowerIchimoku;
        if (IchimokuSenkouA>IchimokuSenkouB) {
            $heigherIchimoku = IchimokuSenkouA;
            $lowerIchimoku = IchimokuSenkouB;
        } else {
            $heigherIchimoku = IchimokuSenkouB;
            $lowerIchimoku = IchimokuSenkouA;
        }

        if ($close > $heigherIchimoku && IchimokuTenkan>IchimokuKijun && IchimokuSenkouA>IchimokuSenkouB) {
            return 1;
        } elseif ($close < $lowerIchimoku && IchimokuTenkan<IchimokuKijun && IchimokuSenkouA<IchimokuSenkouB) {
            return -1;
        }
    }
    public static function calculate(array $candles): array
    {
        if ($ADX < 20) {
            return null;
        } elseif ($ADX > 20) {
            if ($ATRPercent > $SMA20Percent) {
                // coś na bazie SUperTrend
                superTrendCalc();
            } elseif ($ATRPercent < $SMA20Percent && $ADX >= 25) {
                // COŚ NA BAZI ICHIMOKU
                ichimokuTrendCalc();
            } else {
                return null;
            }
        }
        

        return $direction;
    }}