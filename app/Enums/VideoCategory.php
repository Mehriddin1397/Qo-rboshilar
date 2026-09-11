<?php

namespace App\Enums;

enum VideoCategory: string
{
    case Qorboshi = 'qorboshi';
    case Uzgolon = 'uzgolon';

    public function label(): string
    {
        return match ($this) {
            self::Qorboshi => 'Qo\'rboshilar',
            self::Uzgolon => 'Qo\'zg\'olonlar',
        };
    }
}
