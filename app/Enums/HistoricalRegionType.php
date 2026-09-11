<?php

namespace App\Enums;

/**
 * Faza 13 §6: bitta HistoricalRegion nima ma'noni anglatishini aniq belgilaydi —
 * siyosiy chegara, ma'muriy birlik, sof geografik hudud yoki boshqa. Bu metadata
 * xaritada "bu chiziq nimani bildiradi" degan savolga javob beradi, aks holda
 * turli ma'nodagi chegaralar bir xil vizual belgi ostida chalkashib ketardi.
 */
enum HistoricalRegionType: string
{
    case Political = 'political';
    case Administrative = 'administrative';
    case Geographical = 'geographical';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Political => 'Siyosiy chegara',
            self::Administrative => "Ma'muriy birlik",
            self::Geographical => 'Geografik hudud',
            self::Other => 'Boshqa',
        };
    }
}
