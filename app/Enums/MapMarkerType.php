<?php

namespace App\Enums;

enum MapMarkerType: string
{
    case Uprising = 'uprising';
    case Battle = 'battle';
    case RegionCenter = 'region_center';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Uprising => 'Qo\'zg\'olon',
            self::Battle => 'Jang',
            self::RegionCenter => 'Hudud markazi',
            self::Other => 'Boshqa',
        };
    }
}
