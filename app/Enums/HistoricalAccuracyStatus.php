<?php

namespace App\Enums;

/**
 * Faza 13 §7: MUHIM — bu `status` (draft/published, publish holati) bilan
 * ARALASHTIRILMAYDI. `status` — kontent publicga chiqarilganmi degan savol;
 * `accuracy_status` — chiqarilgan bo'lsa ham, geometriya qanchalik ISHONCHLI
 * ekanligi. Ikkalasi mustaqil o'lchov: bir yozuv `published` + `uncertain`
 * bo'lishi mumkin (masalan taxminiy chegara sifatida, aniq ko'rsatib, publish
 * qilingan).
 *
 * Default `Uncertain` — §41 "no false precision" prinsipiga mos: yangi kiritilgan
 * geometriya hech qachon standart bo'yicha "verified" deb taxmin qilinmaydi,
 * admin buni ongli ravishda tanlashi kerak.
 */
enum HistoricalAccuracyStatus: string
{
    case Verified = 'verified';
    case Approximate = 'approximate';
    case Uncertain = 'uncertain';

    public function label(): string
    {
        return match ($this) {
            self::Verified => 'Tasdiqlangan',
            self::Approximate => 'Taxminiy',
            self::Uncertain => "Noaniq",
        };
    }
}
