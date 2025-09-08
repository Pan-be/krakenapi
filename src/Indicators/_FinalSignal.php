<?php

// namespace Indicators;

// class FinalSignal
// {
//     /**
//      * @param array $candles Tablica świec (każda świeca to asocjacyjna tablica z wartościami wskaźników i OHLC)
//      * @param array $const   Stałe z arkusza (AT5, AT6, AT42, AT43)
//      * @return array         Świece rozszerzone o pole 'final_signal'
//      */
//     public static function calculate(array $candles, array $const): array
//     {
//         $result = [];

//         for ($i = 0; $i < count($candles); $i++) {
//             $rowIndex = $i + 1; // indeks jak w arkuszu (1-based)
//             $r = $candles[$i];
//             $prev = $candles[$i - 1] ?? [];

//             $signal = self::evaluateSignal($rowIndex, $r, $prev, $const);

//             $result[] = $candles[$i] + ['final_signal' => $signal];
//         }

//         return $result;
//     }

//     private static function evaluateSignal(
//         int $rowIndex,
//         array $r,
//         array $prev,
//         array $const
//     ): string|int {
//         if ($rowIndex < 3) {
//             return "";
//         }

//         // helpers
//         $isNum = static fn($v): bool => is_numeric($v);
//         $allNum = static function (array $vals) use ($isNum): bool {
//             foreach ($vals as $v) {
//                 if (!$isNum($v)) return false;
//             }
//             return true;
//         };

//         // Stałe
//         $AT5  = $const['AT5']  ?? null;
//         $AT6  = $const['AT6']  ?? null;
//         $AT42 = $const['AT42'] ?? null;
//         $AT43 = $const['AT43'] ?? null;

//         // Bieżący wiersz
//         $BQ = $r['supertrend_direction'] ?? null;
//         $BR = $r['supertrend'] ?? null;
//         $E  = $r['close']  ?? null;
//         $W  = $r['ichimoku_senkou_a']  ?? null;
//         $X  = $r['ichimoku_senkou_b']  ?? null;
//         $U  = $r['ichimoku_tenkan']  ?? null;
//         $V  = $r['ichimoku_kijun']  ?? null;
//         $J  = $r['ema20']  ?? null;
//         $K  = $r['ema50']  ?? null;
//         $L  = $r['ema200']  ?? null;
//         $G  = $r['sma20']  ?? null;
//         $H  = $r['sma50']  ?? null;
//         $I  = $r['sma200']  ?? null;
//         $AM = $r['adx'] ?? null;
//         $AO = $r['atr_percent'] ?? null;

//         // Poprzedni wiersz
//         $Dprev = $prev['low'] ?? null;
//         $Vprev = $prev['ichimoku_kijun'] ?? null;
//         $Jprev = $prev['ema20'] ?? null;
//         $Cprev = $prev['high'] ?? null;
//         $Uprev = $prev['ichimoku_tenkan'] ?? null;

//         // Guard LONG
//         $guardLongPrev = $allNum([$Dprev, $Vprev, $Jprev])
//             ? (($Dprev <= $Vprev) || ($Dprev <= $Jprev))
//             : false;

//         // Guard SHORT
//         $guardShortPrev = $allNum([$Cprev, $Uprev, $Jprev])
//             ? (($Cprev >= $Uprev) || ($Cprev >= $Jprev))
//             : false;

//         // MAX(W:X), MIN(W:X)
//         $maxWX = $allNum([$W, $X]) ? max((float)$W, (float)$X) : null;
//         $minWX = $allNum([$W, $X]) ? min((float)$W, (float)$X) : null;

//         // MAX/MIN(J,K,L,G,H,I)
//         $jklghi = [$J, $K, $L, $G, $H, $I];
//         $haveJKLGHI = $allNum($jklghi);
//         $maxJKLGHI = $haveJKLGHI ? max(...array_map('floatval', $jklghi)) : null;
//         $minJKLGHI = $haveJKLGHI ? min(...array_map('floatval', $jklghi)) : null;

//         // --- LONG ---
//         $isLong =
//             $isNum($BQ) && (float)$BQ === 1.0
//             && $isNum($BR) && $isNum($E) && ((float)$E > (float)$BR * (1.0 + (float)$AT42))
//             && $maxWX !== null && ((float)$E > $maxWX)
//             && $isNum($U) && $isNum($V) && ((float)$U > (float)$V)
//             && $haveJKLGHI && ((float)$E > $maxJKLGHI)
//             && $isNum($AM) && ((float)$AM >= (float)$AT5)
//             && $isNum($AO) && ((float)$AO >= (float)$AT6)
//             && $guardLongPrev;

//         if ($isLong) {
//             return 1;
//         }

//         // --- SHORT ---
//         $isShort =
//             $isNum($BQ) && (float)$BQ === -1.0
//             && $isNum($BR) && $isNum($E) && ((float)$E < (float)$BR * (1.0 - ((float)$AT42 + (float)$AT43)))
//             && $minWX !== null && ((float)$E < $minWX)
//             && $isNum($U) && $isNum($V) && ((float)$U < (float)$V)
//             && $haveJKLGHI && ((float)$E < $minJKLGHI)
//             && $isNum($AM) && ((float)$AM >= ((float)$AT5 + 6.0))
//             && $isNum($AO) && ((float)$AO >= (float)$AT6)
//             && $guardShortPrev;

//         if ($isShort) {
//             return -1;
//         }

//         return 0;
//     }
// }

namespace Indicators;

class FinalSignal
{
    /**
     * @param array $candles - tablica świec, każda świeca to asocjacyjna tablica np.:
     *   [
     *     'rowIndex' => 20,
     *     'BQ' => 1, 'BR' => 100, 'E' => 123.4,
     *     'W' => 120, 'X' => 121,
     *     'U' => 50, 'V' => 49,
     *     'J' => 110,'K' => 111,'L' => 112,'G' => 109,'H' => 108,'I' => 107,
     *     'AM'=>30,'AO'=>2.0,
     *   ]
     * @param array $const - stałe: ['AT5'=>..., 'AT6'=>..., 'AT42'=>..., 'AT43'=>...]
     *
     * @return array - świeczki z dodanym kluczem 'final_result'
     */
    public static function calculate(array $candles, array $const): array
    {
        $n = count($candles);
        for ($i = 0; $i < $n; $i++) {
            $rowIndex = $candles[$i]['rowIndex'] ?? ($i + 1); // numer wiersza (domyślnie indeks+1)
            $prev     = $candles[$i - 1] ?? [];

            $candles[$i]['final_result'] = self::evaluateSignal($rowIndex, $candles[$i], $prev, $const);
        }
        return $candles;
    }

    private static function evaluateSignal(
        int $rowIndex,
        array $r,
        array $prev,
        array $const
    ): string|int {
        if ($rowIndex < 3) {
            return "";
        }

        // helpers
        $isNum = static fn($v): bool => is_numeric($v);
        $allNum = static function (array $vals) use ($isNum): bool {
            foreach ($vals as $v) {
                if (!$isNum($v)) return false;
            }
            return true;
        };

        // Stałe
        $AT5  = $const['AT5']  ?? null;
        $AT6  = $const['AT6']  ?? null;
        $AT42 = $const['AT42'] ?? null;
        $AT43 = $const['AT43'] ?? null;

        // Bieżący wiersz
        $BQ = $r['supertrend_direction'] ?? null;
        $BR = $r['supertrend'] ?? null;
        $E  = $r['close']  ?? null;
        $W  = $r['ichimoku_senkou_a']  ?? null;
        $X  = $r['ichimoku_senkou_b']  ?? null;
        $U  = $r['ichimoku_tenkan']  ?? null;
        $V  = $r['ichimoku_kijun']  ?? null;
        $J  = $r['ema20']  ?? null;
        $K  = $r['ema50']  ?? null;
        $L  = $r['ema200']  ?? null;
        $G  = $r['sma20']  ?? null;
        $H  = $r['sma50']  ?? null;
        $I  = $r['sma200']  ?? null;
        $AM = $r['adx'] ?? null;
        $AO = $r['atr_percent'] ?? null;

        // Poprzedni wiersz
        $Dprev = $prev['low'] ?? null;
        $Vprev = $prev['ichimoku_kijun'] ?? null;
        $Jprev = $prev['ema20'] ?? null;
        $Cprev = $prev['high'] ?? null;
        $Uprev = $prev['ichimoku_tenkan'] ?? null;

        // Guard LONG
        $guardLongPrev = $allNum([$Dprev, $Vprev, $Jprev])
            ? (($Dprev <= $Vprev) || ($Dprev <= $Jprev))
            : false;

        // Guard SHORT
        $guardShortPrev = $allNum([$Cprev, $Uprev, $Jprev])
            ? (($Cprev >= $Uprev) || ($Cprev >= $Jprev))
            : false;

        // MAX(W:X), MIN(W:X)
        $maxWX = $allNum([$W, $X]) ? max((float)$W, (float)$X) : null;
        $minWX = $allNum([$W, $X]) ? min((float)$W, (float)$X) : null;

        // MAX/MIN(J,K,L,G,H,I)
        $jklghi = [$J, $K, $L, $G, $H, $I];
        $haveJKLGHI = $allNum($jklghi);
        $maxJKLGHI = $haveJKLGHI ? max((float)$J, (float)$K, (float)$L, (float)$G, (float)$H, (float)$I) : null;
        $minJKLGHI = $haveJKLGHI ? min((float)$J, (float)$K, (float)$L, (float)$G, (float)$H, (float)$I) : null;

        // --- LONG ---
        $isLong =
            $BQ === "up"
            && $isNum($BR) && $isNum($E) && ((float)$E > (float)$BR * (1.0 + (float)$AT42))
            && $maxWX !== null && ((float)$E > $maxWX)
            && $isNum($U) && $isNum($V) && ((float)$U > (float)$V)
            && $haveJKLGHI && ((float)$E > $maxJKLGHI)
            && $isNum($AM) && ((float)$AM >= (float)$AT5)
            && $isNum($AO) && ((float)$AO >= (float)$AT6)
            && $guardLongPrev;

        if ($isLong) {
            return 1;
        }

        // --- SHORT ---
        $isShort =
            $BQ === "down"
            && $isNum($BR) && $isNum($E) && ((float)$E < (float)$BR * (1.0 - ((float)$AT42 + (float)$AT43)))
            && $minWX !== null && ((float)$E < $minWX)
            && $isNum($U) && $isNum($V) && ((float)$U < (float)$V)
            && $haveJKLGHI && ((float)$E < $minJKLGHI)
            && $isNum($AM) && ((float)$AM >= ((float)$AT5 + 6.0))
            && $isNum($AO) && ((float)$AO >= (float)$AT6)
            && $guardShortPrev;

        if ($isShort) {
            return -1;
        }

        return 0;
    }
}
