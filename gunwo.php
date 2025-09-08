<?php

function evaluateSignal(
    int $rowIndex,
    array $r,
    array $prev,
    array $const
): string|int {

    if ($rowIndex < 3) {
        return ""; // =JEŻELI(WIERSZ()<3;"";...)
    }

    // helpers
    $isNum = static fn($v): bool => is_numeric($v);
    $allNum = static function (array $vals) use ($isNum): bool {
        foreach ($vals as $v) {
            if (!$isNum($v)) return false;
        }
        return true;
    };

    // Ułatwienia nazw
    $AT5  = $const['AT5']  ?? null;
    $AT6  = $const['AT6']  ?? null;
    $AT42 = $const['AT42'] ?? null;
    $AT43 = $const['AT43'] ?? null;

    // Bieżący wiersz (…20)
    $BQ = $r['BQ'] ?? null;
    $BR = $r['BR'] ?? null;
    $E  = $r['E']  ?? null;
    $W  = $r['W']  ?? null;
    $X  = $r['X']  ?? null;
    $U  = $r['U']  ?? null;
    $V  = $r['V']  ?? null;
    $J  = $r['J']  ?? null;
    $K  = $r['K']  ?? null;
    $L  = $r['L']  ?? null;
    $G  = $r['G']  ?? null;
    $H  = $r['H']  ?? null;
    $I  = $r['I']  ?? null;
    $AM = $r['AM'] ?? null;
    $AO = $r['AO'] ?? null;

    // Poprzedni wiersz (…19)
    $Dprev = $prev['D'] ?? null;
    $Vprev = $prev['V'] ?? null;
    $Jprev = $prev['J'] ?? null;
    $Cprev = $prev['C'] ?? null;
    $Uprev = $prev['U'] ?? null;

    // Warunek z końcówki LONG:
    // JEŻELI( ORAZ(num D19,V19,J19); LUB(D19<=V19; D19<=J19); FAŁSZ )
    $guardLongPrev = $allNum([$Dprev, $Vprev, $Jprev])
        ? (($Dprev <= $Vprev) || ($Dprev <= $Jprev))
        : false;

    // Warunek z końcówki SHORT:
    // JEŻELI( ORAZ(num C19,U19,J19); LUB(C19>=U19; C19>=J19); FAŁSZ )
    $guardShortPrev = $allNum([$Cprev, $Uprev, $Jprev])
        ? (($Cprev >= $Uprev) || ($Cprev >= $Jprev))
        : false;

    // MAX(W20:X20) i MIN(W20:X20)
    $maxWX = $allNum([$W, $X]) ? max((float)$W, (float)$X) : null;
    $minWX = $allNum([$W, $X]) ? min((float)$W, (float)$X) : null;

    // MAX(J,K,L,G,H,I) oraz MIN(J,K,L,G,H,I)
    $jklghi = [$J, $K, $L, $G, $H, $I];
    $haveJKLGHI = $allNum($jklghi);
    $maxJKLGHI = $haveJKLGHI ? max((float)$J, (float)$K, (float)$L, (float)$G, (float)$H, (float)$I) : null;
    $minJKLGHI = $haveJKLGHI ? min((float)$J, (float)$K, (float)$L, (float)$G, (float)$H, (float)$I) : null;

    // --- LONG (zwraca 1) ---
    $isLong =
        $isNum($BQ) && (float)$BQ === 1.0
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

    // --- SHORT (zwraca -1) ---
    $isShort =
        $isNum($BQ) && (float)$BQ === -1.0
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

    // --- Brak sygnału ---
    return 0;
}

// ======= PRZYKŁAD UŻYCIA =======
$row = 20;
$r = [
    'BQ' => -1,
    'BR' => 3529.835583,
    'E' => 3383.5,
    'W' => 3655.575,
    'X' => 3721.05,
    'U' => 3444.3,
    'V' => 3485.55,
    'J' => 3492.26,
    'K' => 3572.57913,
    'L' => 3680.689198,
    'G' => 3491.92,
    'H' => 35894.206,
    'I' => 3740.8675,
    'AM' => 55.77,
    'AO' => 1.2358,
];
$prev = ['D' => 3414, 'V' => 3523.6, 'J' => 3503.7111, 'C' => 3480.4, 'U' => 3465.95];
$const = ['AT5' => 44, 'AT6' => 0, 'AT42' => 0, 'AT43' => 0];

$result = evaluateSignal($row, $r, $prev, $const); // 1 / -1 / 0 / ""
echo $result;
