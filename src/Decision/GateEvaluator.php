<?php

namespace Decision;

/**
 * Gate (Excel 4h sheet, col Y) = AND of mode!=OFF, direction!=0, both filters=1.
 * If the gate is 0, there is no trade — full stop, regardless of anything else.
 */
class GateEvaluator
{
    public static function calculate(array $candles): array
    {
        foreach ($candles as $i => $c) {
            $mode = $c['mode'] ?? 'OFF';
            $direction = $c['direction'] ?? 0;
            $emaFilter = $c['ema_filter'] ?? 0;
            $volumeFilter = $c['volume_filter'] ?? 0;

            $candles[$i]['gate'] = ($mode !== 'OFF' && $direction !== 0 && $emaFilter === 1 && $volumeFilter === 1)
                ? 1
                : 0;
        }

        return $candles;
    }
}
