<?php

namespace App\Enums;

enum LiteratureStatus: string
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
