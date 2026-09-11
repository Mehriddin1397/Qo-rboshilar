<?php

namespace App\Enums;

/**
 * Faza 14 §5: TimelineEvent — Qorboshi/Uzgolon/Literature/Video/HistoricalRegion/
 * HistoricalMapLayer bilan bir xil ikki holatli (draft/published) naqsh. Blog'dagi
 * 4 holatli (draft/pending/approved/rejected) moderatsiya workflow'i qo'llanilmadi,
 * chunki TimelineEvent — Blog'dan farqli — faqat admin tomonidan yaratiladi, oddiy
 * foydalanuvchi submit qilish oqimi yo'q (spec "loyiha standartiga mos mavjud
 * enumdan foydalan" — admin-only kontent uchun standart shu ikki holatli naqsh).
 */
enum TimelineEventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Qoralama',
            self::Published => 'Nashr etilgan',
        };
    }
}
